<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create()
    {
        $invoices = Invoice::with('branch')
            ->where('remaining_amount', '>', 0)
            ->latest()
            ->get();

        return view('payments.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $invoice = Invoice::with('branch')->findOrFail($request->invoice_id);
        $amount = (float) $request->amount;

        if ($amount > (float) $invoice->remaining_amount) {
            return back()->withInput()->with('error', 'المبلغ أكبر من المتبقي على الفاتورة.');
        }

        $cashbox = Cashbox::where('branch_id', $invoice->branch_id)->first();

        if (!$cashbox) {
            return back()->withInput()->with('error', 'لا توجد خزنة مرتبطة بفرع هذه الفاتورة.');
        }

        DB::beginTransaction();

        try {
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
            ]);

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
                'cashbox_id' => $cashbox->id,
                'created_by' => Auth::id(),
                'invoice_id' => $invoice->id,
                'type' => 'IN',
                'amount' => $amount,
                'reason' => 'سداد من العميل: ' . $invoice->customer_name . ' | فاتورة بيع #' . $invoice->id,
                'reference_code' => 'PAY-' . $invoice->id,
            ]);

            DB::commit();

            return redirect()->route('payments.create')->with('success', 'تم تسجيل السداد بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تسجيل السداد: ' . $e->getMessage());
        }
    }
}