<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentItem;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();

        $customers = $customers->filter(function ($customer) {
            return Invoice::where('customer_id', $customer->id)
                ->where('remaining_amount', '>', 0)
                ->exists();
        })->values();

        $selectedCustomer = null;
        $openInvoices = collect();

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);

            if ($selectedCustomer) {
                $openInvoices = Invoice::where('customer_id', $selectedCustomer->id)
                    ->where('remaining_amount', '>', 0)
                    ->latest()
                    ->get()
                    ->map(function ($invoice) {
                        $cashbox = null;

                        if (!is_null($invoice->branch_id)) {
                            $cashbox = Cashbox::where('branch_id', $invoice->branch_id)->first();
                        }

                        $invoice->cashbox_name = $cashbox?->name ?? 'لا توجد خزنة مرتبطة';
                        $invoice->payment_status_ar = match ($invoice->payment_status) {
                            'paid' => 'مدفوعة',
                            'partial' => 'مدفوعة جزئيًا',
                            'due' => 'آجلة',
                            default => $invoice->payment_status,
                        };

                        return $invoice;
                    });
            }
        }

        return view('customer_payments.create', compact('customers', 'selectedCustomer', 'openInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'required|array|min:1',
            'payment_amount' => 'required|array|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        DB::beginTransaction();

        try {
            $items = [];
            $totalAmount = 0;

            $customerDueBefore = (float) Invoice::where('customer_id', $customer->id)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            foreach ($request->invoice_id as $index => $invoiceId) {
                $amount = (float) ($request->payment_amount[$index] ?? 0);

                if ($amount <= 0) {
                    continue;
                }

                $invoice = Invoice::where('customer_id', $customer->id)->findOrFail($invoiceId);

                if ($amount > (float) $invoice->remaining_amount) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'مبلغ السداد أكبر من المتبقي في الفاتورة #' . $invoice->id);
                }

                if (is_null($invoice->branch_id)) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'الفاتورة #' . $invoice->id . ' غير مرتبطة بفرع، ولا يمكن تحديد الخزنة الخاصة بها.');
                }

                $cashbox = Cashbox::where('branch_id', $invoice->branch_id)->first();

                if (!$cashbox) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'لا توجد خزنة مرتبطة بفرع الفاتورة #' . $invoice->id);
                }

                $remainingBefore = (float) $invoice->remaining_amount;
                $remainingAfter = max(0, $remainingBefore - $amount);

                $items[] = [
                    'invoice' => $invoice,
                    'cashbox' => $cashbox,
                    'amount' => $amount,
                    'remaining_before' => $remainingBefore,
                    'remaining_after' => $remainingAfter,
                ];

                $totalAmount += $amount;
            }

            if ($totalAmount <= 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'أدخل مبلغ سداد صحيح لفاتورة واحدة على الأقل.');
            }

            $paymentHeader = CustomerPayment::create([
                'customer_id' => $customer->id,
                'created_by' => Auth::id(),
                'total_amount' => $totalAmount,
                'notes' => trim($request->notes ?? ''),
                'reference_code' => 'CPAY-' . now()->format('YmdHis'),
            ]);

            foreach ($items as $row) {
                $invoice = $row['invoice'];
                $cashbox = $row['cashbox'];
                $amount = $row['amount'];

                CustomerPaymentItem::create([
                    'customer_payment_id' => $paymentHeader->id,
                    'invoice_id' => $invoice->id,
                    'cashbox_id' => $cashbox->id,
                    'amount' => $amount,
                    'remaining_before' => $row['remaining_before'],
                    'remaining_after' => $row['remaining_after'],
                ]);

                $invoice->paid_amount += $amount;
                $invoice->remaining_amount = $row['remaining_after'];

                if ($invoice->remaining_amount <= 0) {
                    $invoice->remaining_amount = 0;
                    $invoice->payment_status = 'paid';
                } else {
                    $invoice->payment_status = 'partial';
                }

                $invoice->save();

                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                ]);

                CashboxTransaction::create([
                    'cashbox_id' => $cashbox->id,
                    'created_by' => Auth::id(),
                    'invoice_id' => $invoice->id,
                    'type' => 'IN',
                    'amount' => $amount,
                    'reason' => 'سداد من العميل: ' . $customer->name . ' | فاتورة #' . $invoice->id . ' | فرع الفاتورة #' . $invoice->branch_id,
                    'reference_code' => $paymentHeader->reference_code,
                ]);
            }

            $customerDueAfter = max(0, $customerDueBefore - $totalAmount);

            $systemNote = 'إجمالي مديونية العميل قبل السداد: ' . number_format($customerDueBefore, 2) .
                ' ج.م | بعد السداد: ' . number_format($customerDueAfter, 2) . ' ج.م';

            $paymentHeader->notes = trim($request->notes ?? '');
            if ($paymentHeader->notes !== '') {
                $paymentHeader->notes .= ' | ';
            }
            $paymentHeader->notes .= $systemNote;
            $paymentHeader->save();

            DB::commit();

            return redirect()->route('customer-payments.show', $paymentHeader->id)
                ->with('success', 'تم تسجيل سداد العميل بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تسجيل السداد: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $payment = CustomerPayment::with(['customer', 'items.invoice', 'items.cashbox'])->find($id);

        if (!$payment) {
            return redirect()->route('customers.index')->with('error', 'سند السداد غير موجود.');
        }

        $customerRemainingAfter = 0;
        $customerDueBefore = 0;

        if ($payment->customer) {
            $customerRemainingAfter = (float) Invoice::where('customer_id', $payment->customer->id)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            $customerDueBefore = $customerRemainingAfter + (float) $payment->total_amount;
        }

        return view('customer_payments.show', compact('payment', 'customerRemainingAfter', 'customerDueBefore'));
    }

    public function print($id)
    {
        $payment = CustomerPayment::with(['customer', 'items.invoice', 'items.cashbox'])->find($id);

        if (!$payment) {
            return redirect()->route('customers.index')->with('error', 'سند السداد غير موجود.');
        }

        $customerRemainingAfter = 0;
        $customerDueBefore = 0;

        if ($payment->customer) {
            $customerRemainingAfter = (float) Invoice::where('customer_id', $payment->customer->id)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            $customerDueBefore = $customerRemainingAfter + (float) $payment->total_amount;
        }

        return view('customer_payments.print', compact('payment', 'customerRemainingAfter', 'customerDueBefore'));
    }
}