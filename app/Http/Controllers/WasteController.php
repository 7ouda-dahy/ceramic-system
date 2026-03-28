<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\WasteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteRecord::with(['product', 'branch', 'creator'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $records = $query->get();

        $summaryQuantity = (float) $records->sum('quantity_meter');
        $summaryValue = (float) $records->sum('total_cost');
        $branches = Branch::orderBy('name')->get();

        return view('wastes.index', compact('records', 'summaryQuantity', 'summaryValue', 'branches'));
    }

    public function create()
    {
        $products = Product::orderBy('full_name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('wastes.create', compact('products', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'nullable|exists:branches,id',
            'quantity_meter' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);
            $qty = (float) $request->quantity_meter;

            if ($qty > (float) $product->quantity_meter) {
                DB::rollBack();
                return back()->withInput()->with('error', 'الكمية الهالكة أكبر من الكمية المتاحة بالمخزون.');
            }

            $unitCost = (float) ($product->average_cost > 0 ? $product->average_cost : $product->purchase_price);
            $totalCost = $qty * $unitCost;

            $record = WasteRecord::create([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id ?: null,
                'created_by' => Auth::id(),
                'product_name' => $product->full_name,
                'quantity_meter' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'reason' => trim($request->reason),
                'notes' => trim($request->notes ?? ''),
            ]);

            $product->quantity_meter = (float) $product->quantity_meter - $qty;
            $product->save();

            DB::commit();

            return redirect()->route('wastes.show', $record->id)
                ->with('success', 'تم تسجيل الهالك وخصم الكمية من المخزون بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تسجيل الهالك: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $record = WasteRecord::with(['product', 'branch', 'creator'])->find($id);

        if (!$record) {
            return redirect()->route('wastes.index')->with('error', 'سجل الهالك غير موجود.');
        }

        return view('wastes.show', compact('record'));
    }
}