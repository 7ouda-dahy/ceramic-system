<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->get()->map(function ($supplier) {
            $supplier->total_due = (float) PurchaseInvoice::where('supplier_id', $supplier->id)->sum('remaining_amount');
            return $supplier;
        });

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Supplier::create([
            'name' => trim($request->name),
            'phone' => trim($request->phone ?? ''),
            'address' => trim($request->address ?? ''),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'تمت إضافة المورد بنجاح.');
    }

    public function show($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return redirect()->route('suppliers.index')->with('error', 'المورد غير موجود.');
        }

        $invoices = PurchaseInvoice::where('supplier_id', $supplier->id)
            ->latest()
            ->get();

        $totalDue = (float) $invoices->sum('remaining_amount');

        return view('suppliers.show', compact('supplier', 'invoices', 'totalDue'));
    }
}