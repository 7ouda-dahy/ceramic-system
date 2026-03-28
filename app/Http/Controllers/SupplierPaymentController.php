<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function create(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $selectedSupplier = null;
        $openInvoices = collect();
        $centralCashbox = Cashbox::where('is_central', true)->first();

        if ($request->filled('supplier_id')) {
            $selectedSupplier = Supplier::find($request->supplier_id);

            if ($selectedSupplier) {
                $openInvoices = PurchaseInvoice::where('supplier_id', $selectedSupplier->id)
                    ->where('remaining_amount', '>', 0)
                    ->latest()
                    ->get()
                    ->map(function ($invoice) {
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

        return view('supplier_payments.create', compact(
            'suppliers',
            'selectedSupplier',
            'openInvoices',
            'centralCashbox'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_id' => 'required|array|min:1',
            'payment_amount' => 'required|array|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $centralCashbox = Cashbox::where('is_central', true)->first();

        if (!$centralCashbox) {
            return back()->withInput()->with('error', 'الخزنة المركزية غير موجودة.');
        }

        DB::beginTransaction();

        try {
            $supplier = Supplier::findOrFail($request->supplier_id);

            $supplierDueBefore = (float) PurchaseInvoice::where('supplier_id', $supplier->id)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            $totalPaid = 0;
            $items = [];

            foreach ($request->invoice_id as $index => $invoiceId) {
                $amount = (float) ($request->payment_amount[$index] ?? 0);

                if ($amount <= 0) {
                    continue;
                }

                $invoice = PurchaseInvoice::where('supplier_id', $supplier->id)->findOrFail($invoiceId);

                if ($amount > (float) $invoice->remaining_amount) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'مبلغ السداد أكبر من المتبقي في فاتورة الشراء #' . $invoice->id);
                }

                $remainingBefore = (float) $invoice->remaining_amount;
                $remainingAfter = max(0, $remainingBefore - $amount);

                $items[] = [
                    'invoice' => $invoice,
                    'amount' => $amount,
                    'remaining_before' => $remainingBefore,
                    'remaining_after' => $remainingAfter,
                ];

                $totalPaid += $amount;
            }

            if ($totalPaid <= 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'أدخل مبلغ سداد صحيح لفاتورة واحدة على الأقل.');
            }

            if ($totalPaid > (float) $centralCashbox->balance) {
                DB::rollBack();
                return back()->withInput()->with('error', 'رصيد الخزنة المركزية لا يسمح بإتمام السداد.');
            }

            $payment = SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'cashbox_id' => $centralCashbox->id,
                'created_by' => Auth::id(),
                'total_amount' => $totalPaid,
                'notes' => trim($request->notes ?? ''),
                'reference_code' => 'SPAY-' . now()->format('YmdHis'),
            ]);

            foreach ($items as $row) {
                $invoice = $row['invoice'];
                $amount = $row['amount'];

                SupplierPaymentItem::create([
                    'supplier_payment_id' => $payment->id,
                    'purchase_invoice_id' => $invoice->id,
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
            }

            CashboxTransaction::create([
                'cashbox_id' => $centralCashbox->id,
                'created_by' => Auth::id(),
                'invoice_id' => null,
                'type' => 'OUT',
                'amount' => $totalPaid,
                'reason' => 'سداد للمورد: ' . $supplier->name,
                'reference_code' => $payment->reference_code,
            ]);

            $supplierDueAfter = max(0, $supplierDueBefore - $totalPaid);

            $systemNote = 'إجمالي مديونية المورد قبل السداد: ' . number_format($supplierDueBefore, 2) .
                ' ج.م | بعد السداد: ' . number_format($supplierDueAfter, 2) . ' ج.م';

            $payment->notes = trim($request->notes ?? '');
            if ($payment->notes !== '') {
                $payment->notes .= ' | ';
            }
            $payment->notes .= $systemNote;
            $payment->save();

            DB::commit();

            return redirect()->route('supplier-payments.show', $payment->id)
                ->with('success', 'تم تسجيل سداد المورد بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تسجيل السداد: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $payment = SupplierPayment::with(['supplier', 'cashbox', 'items.purchaseInvoice'])->find($id);

        if (!$payment) {
            return redirect()->route('suppliers.index')->with('error', 'سند السداد غير موجود.');
        }

        $supplierRemainingAfter = 0;
        $supplierDueBefore = 0;

        if ($payment->supplier) {
            $supplierRemainingAfter = (float) PurchaseInvoice::where('supplier_id', $payment->supplier->id)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            $supplierDueBefore = $supplierRemainingAfter + (float) $payment->total_amount;
        }

        return view('supplier_payments.show', compact('payment', 'supplierRemainingAfter', 'supplierDueBefore'));
    }

    public function print($id)
    {
        $payment = SupplierPayment::with(['supplier', 'cashbox', 'items.purchaseInvoice'])->find($id);

        if (!$payment) {
            return redirect()->route('suppliers.index')->with('error', 'سند السداد غير موجود.');
        }

        $supplierRemainingAfter = 0;
        $supplierDueBefore = 0;

        if ($payment->supplier) {
            $supplierRemainingAfter = (float) PurchaseInvoice::where('supplier_id', $payment->supplier->id)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            $supplierDueBefore = $supplierRemainingAfter + (float) $payment->total_amount;
        }

        return view('supplier_payments.print', compact('payment', 'supplierRemainingAfter', 'supplierDueBefore'));
    }
}