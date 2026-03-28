<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
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
                    ->get();
            }
        }

        return view('supplier_payments.create', compact('suppliers', 'selectedSupplier', 'openInvoices', 'centralCashbox'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_id' => 'required|array|min:1',
            'payment_amount' => 'required|array|min:1',
        ]);

        $centralCashbox = Cashbox::where('is_central', true)->first();

        if (!$centralCashbox) {
            return back()->withInput()->with('error', 'الخزنة المركزية غير موجودة.');
        }

        DB::beginTransaction();

        try {
            $totalPaid = 0;
            $supplierName = Supplier::find($request->supplier_id)?->name ?? 'مورد';

            foreach ($request->invoice_id as $index => $invoiceId) {
                $amount = (float) ($request->payment_amount[$index] ?? 0);

                if ($amount <= 0) {
                    continue;
                }

                $invoice = PurchaseInvoice::where('supplier_id', $request->supplier_id)->findOrFail($invoiceId);

                if ($amount > (float) $invoice->remaining_amount) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'مبلغ السداد أكبر من المتبقي في فاتورة الشراء #' . $invoice->id);
                }

                $totalPaid += $amount;

                $invoice->paid_amount += $amount;
                $invoice->remaining_amount -= $amount;

                if ($invoice->remaining_amount <= 0) {
                    $invoice->remaining_amount = 0;
                    $invoice->payment_status = 'paid';
                } else {
                    $invoice->payment_status = 'partial';
                }

                $invoice->save();

                CashboxTransaction::create([
                    'cashbox_id' => $centralCashbox->id,
                    'created_by' => Auth::id(),
                    'invoice_id' => null,
                    'type' => 'OUT',
                    'amount' => $amount,
                    'reason' => 'سداد للمورد: ' . $supplierName . ' | فاتورة شراء #' . $invoice->id,
                    'reference_code' => 'SPAY-' . $invoice->id,
                ]);
            }

            if ($totalPaid <= 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'أدخل مبلغ سداد صحيح لفاتورة واحدة على الأقل.');
            }

            if ($totalPaid > (float) $centralCashbox->balance) {
                DB::rollBack();
                return back()->withInput()->with('error', 'رصيد الخزنة المركزية لا يسمح بإتمام السداد.');
            }

            DB::commit();

            return redirect()->route('suppliers.show', $request->supplier_id)->with('success', 'تم تسجيل سداد المورد بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تسجيل السداد: ' . $e->getMessage());
        }
    }
}