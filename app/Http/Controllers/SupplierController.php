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
            $openInvoices = PurchaseInvoice::where('supplier_id', $supplier->id)
                ->where('remaining_amount', '>', 0)
                ->get();

            $supplier->total_due = (float) $openInvoices->sum('remaining_amount');
            $supplier->oldest_due_date = $openInvoices->min('created_at');
            $supplier->latest_due_date = $openInvoices->max('created_at');

            return $supplier;
        });

        // مهم: إخفاء الموردين الذين لا توجد عليهم ذمم
        $suppliers = $suppliers->filter(function ($supplier) {
            return (float) $supplier->total_due > 0;
        })->values();

        $sort = $request->get('sort', '');

        if ($sort === 'oldest') {
            $suppliers = $suppliers->sortBy(function ($supplier) {
                return $supplier->oldest_due_date ?? now()->addYears(100);
            })->values();
        } elseif ($sort === 'latest') {
            $suppliers = $suppliers->sortByDesc(function ($supplier) {
                return $supplier->latest_due_date ?? now()->subYears(100);
            })->values();
        } elseif ($sort === 'highest_due') {
            $suppliers = $suppliers->sortByDesc('total_due')->values();
        } elseif ($sort === 'lowest_due') {
            $suppliers = $suppliers->sortBy('total_due')->values();
        }

        return view('suppliers.index', compact('suppliers', 'sort'));
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

        // مهم: صفحة الذمم تعرض فقط الفواتير المفتوحة
        $invoices = PurchaseInvoice::where('supplier_id', $supplier->id)
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

        $totalDue = (float) PurchaseInvoice::where('supplier_id', $supplier->id)
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');

        return view('suppliers.show', compact('supplier', 'invoices', 'totalDue'));
    }
}