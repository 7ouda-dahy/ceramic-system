<?php

namespace App\Http\Controllers;

use App\Models\CashTransfer;
use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashTransferController extends Controller
{
    public function index()
    {
        $transfers = CashTransfer::with(['fromCashbox', 'toCashbox', 'creator'])->latest()->get();
        return view('cash_transfers.index', compact('transfers'));
    }

    public function create()
    {
        $cashboxes = Cashbox::orderBy('is_central', 'desc')->orderBy('name')->get();
        return view('cash_transfers.create', compact('cashboxes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_cashbox_id' => 'required|exists:cashboxes,id',
            'to_cashbox_id' => 'required|exists:cashboxes,id|different:from_cashbox_id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $from = Cashbox::findOrFail($request->from_cashbox_id);
        $to = Cashbox::findOrFail($request->to_cashbox_id);

        if (!$from->is_central && !$to->is_central) {
            return back()->withInput()->with('error', 'غير مسموح بتحويل مباشر بين خزنتي فرعين. يجب أن يمر التحويل عبر الخزنة المركزية.');
        }

        if ($from->balance < (float) $request->amount) {
            return back()->withInput()->with('error', 'رصيد الخزنة المصدر لا يسمح بإتمام التحويل.');
        }

        $reference = 'TRN-' . strtoupper(Str::random(8));

        DB::beginTransaction();

        try {
            CashTransfer::create([
                'from_cashbox_id' => $from->id,
                'to_cashbox_id' => $to->id,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'reference_code' => $reference,
                'created_by' => Auth::id(),
            ]);

            CashboxTransaction::create([
                'cashbox_id' => $from->id,
                'created_by' => Auth::id(),
                'type' => 'OUT',
                'amount' => $request->amount,
                'reason' => 'تحويل من ' . $from->name . ' إلى ' . $to->name,
                'reference_code' => $reference,
            ]);

            CashboxTransaction::create([
                'cashbox_id' => $to->id,
                'created_by' => Auth::id(),
                'type' => 'IN',
                'amount' => $request->amount,
                'reason' => 'تحويل من ' . $from->name . ' إلى ' . $to->name,
                'reference_code' => $reference,
            ]);

            DB::commit();

            return redirect()->route('cash-transfers.index')->with('success', 'تم تنفيذ التحويل بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تنفيذ التحويل: ' . $e->getMessage());
        }
    }
}