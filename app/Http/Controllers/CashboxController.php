<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashboxController extends Controller
{
    public function index(Request $request, $slug)
    {
        $cashbox = $this->resolveCashboxBySlug($slug);

        if (!$cashbox) {
            abort(404, 'الخزنة غير موجودة');
        }

        $query = CashboxTransaction::where('cashbox_id', $cashbox->id);

        if ($request->filled('filter')) {
            if ($request->filter === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($request->filter === 'month') {
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            } elseif ($request->filter === 'custom') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('reference_code', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $transactionsAsc = $query->orderBy('created_at')->orderBy('id')->get();

        $openingBalance = $this->calculateOpeningBalance($cashbox, $transactionsAsc);
        $runningBalance = $openingBalance;

        foreach ($transactionsAsc as $transaction) {
            if ($transaction->type === 'IN') {
                $runningBalance += (float) $transaction->amount;
            } else {
                $runningBalance -= (float) $transaction->amount;
            }

            $transaction->balance_after = $runningBalance;
        }

        $transactions = $transactionsAsc->sortByDesc(function ($item) {
            return $item->created_at->timestamp . str_pad($item->id, 10, '0', STR_PAD_LEFT);
        })->values();

        $totalIn = (float) $transactions->where('type', 'IN')->sum('amount');
        $totalOut = (float) $transactions->where('type', 'OUT')->sum('amount');

        return view('cashbox.show', compact(
            'cashbox',
            'transactions',
            'totalIn',
            'totalOut'
        ));
    }

    public function storeExpense(Request $request, $slug)
    {
        $cashbox = $this->resolveCashboxBySlug($slug);

        if (!$cashbox) {
            abort(404, 'الخزنة غير موجودة');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:1000',
        ]);

        if ((float) $request->amount > (float) $cashbox->balance) {
            return back()->with('error', 'الرصيد الحالي لا يسمح بتسجيل المصروف.');
        }

        CashboxTransaction::create([
            'cashbox_id' => $cashbox->id,
            'created_by' => Auth::id(),
            'invoice_id' => null,
            'type' => 'OUT',
            'amount' => (float) $request->amount,
            'reason' => trim($request->reason),
            'reference_code' => 'EXP-' . now()->format('YmdHis'),
        ]);

        $cashbox->balance -= (float) $request->amount;
        $cashbox->save();

        return back()->with('success', 'تم تسجيل المصروف بنجاح.');
    }

    public function storeTransfer(Request $request, $slug)
    {
        $fromCashbox = $this->resolveCashboxBySlug($slug);

        if (!$fromCashbox) {
            abort(404, 'الخزنة غير موجودة');
        }

        $request->validate([
            'to_cashbox_id' => 'required|exists:cashboxes,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:1000',
        ]);

        $toCashbox = Cashbox::find($request->to_cashbox_id);

        if (!$toCashbox || $toCashbox->id == $fromCashbox->id) {
            return back()->with('error', 'اختر خزنة صحيحة للتحويل.');
        }

        if ((float) $request->amount > (float) $fromCashbox->balance) {
            return back()->with('error', 'رصيد الخزنة الحالية لا يسمح بالتحويل.');
        }

        $reference = 'TRF-' . now()->format('YmdHis');

        CashboxTransaction::create([
            'cashbox_id' => $fromCashbox->id,
            'created_by' => Auth::id(),
            'invoice_id' => null,
            'type' => 'OUT',
            'amount' => (float) $request->amount,
            'reason' => 'تحويل إلى ' . $toCashbox->name . ' | ' . trim($request->reason),
            'reference_code' => $reference,
        ]);

        CashboxTransaction::create([
            'cashbox_id' => $toCashbox->id,
            'created_by' => Auth::id(),
            'invoice_id' => null,
            'type' => 'IN',
            'amount' => (float) $request->amount,
            'reason' => 'تحويل من ' . $fromCashbox->name . ' | ' . trim($request->reason),
            'reference_code' => $reference,
        ]);

        $fromCashbox->balance -= (float) $request->amount;
        $fromCashbox->save();

        $toCashbox->balance += (float) $request->amount;
        $toCashbox->save();

        return back()->with('success', 'تم التحويل بين الخزن بنجاح.');
    }

    public function print(Request $request, $slug)
    {
        $cashbox = $this->resolveCashboxBySlug($slug);

        if (!$cashbox) {
            abort(404, 'الخزنة غير موجودة');
        }

        $query = CashboxTransaction::where('cashbox_id', $cashbox->id)
            ->orderBy('created_at')
            ->orderBy('id');

        $transactionsAsc = $query->get();

        $openingBalance = $this->calculateOpeningBalance($cashbox, $transactionsAsc);
        $runningBalance = $openingBalance;

        foreach ($transactionsAsc as $transaction) {
            if ($transaction->type === 'IN') {
                $runningBalance += (float) $transaction->amount;
            } else {
                $runningBalance -= (float) $transaction->amount;
            }

            $transaction->balance_after = $runningBalance;
        }

        $transactions = $transactionsAsc->sortByDesc(function ($item) {
            return $item->created_at->timestamp . str_pad($item->id, 10, '0', STR_PAD_LEFT);
        })->values();

        return view('cashbox.print', compact('cashbox', 'transactions'));
    }

    private function calculateOpeningBalance(Cashbox $cashbox, $transactionsAsc): float
    {
        $netMovement = 0;

        foreach ($transactionsAsc as $transaction) {
            if ($transaction->type === 'IN') {
                $netMovement += (float) $transaction->amount;
            } else {
                $netMovement -= (float) $transaction->amount;
            }
        }

        return (float) $cashbox->balance - $netMovement;
    }

    private function resolveCashboxBySlug(string $slug): ?Cashbox
    {
        return Cashbox::where('slug', $slug)->first();
    }
}

