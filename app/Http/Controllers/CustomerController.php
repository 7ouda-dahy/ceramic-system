<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->get()->map(function ($customer) {
            $openInvoices = Invoice::where('customer_id', $customer->id)
                ->where('remaining_amount', '>', 0)
                ->get();

            $customer->total_due = (float) $openInvoices->sum('remaining_amount');
            $customer->oldest_due_date = $openInvoices->min('created_at');
            $customer->latest_due_date = $openInvoices->max('created_at');

            return $customer;
        });

        $customers = $customers->filter(function ($customer) {
            return (float) $customer->total_due > 0;
        })->values();

        $sort = $request->get('sort', '');

        if ($sort === 'oldest') {
            $customers = $customers->sortBy(function ($customer) {
                return $customer->oldest_due_date ?? now()->addYears(100);
            })->values();
        } elseif ($sort === 'latest') {
            $customers = $customers->sortByDesc(function ($customer) {
                return $customer->latest_due_date ?? now()->subYears(100);
            })->values();
        } elseif ($sort === 'highest_due') {
            $customers = $customers->sortByDesc('total_due')->values();
        } elseif ($sort === 'lowest_due') {
            $customers = $customers->sortBy('total_due')->values();
        }

        $totalDue = (float) $customers->sum('total_due');
        $customersCount = $customers->count();
        $averageDue = $customersCount > 0 ? $totalDue / $customersCount : 0;

        return view('customers.index', compact(
            'customers',
            'sort',
            'totalDue',
            'customersCount',
            'averageDue'
        ));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Customer::create([
            'name' => trim($request->name),
            'phone' => trim($request->phone ?? ''),
            'address' => trim($request->address ?? ''),
        ]);

        return redirect()->route('customers.index')->with('success', 'تمت إضافة العميل بنجاح.');
    }

    public function show($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return redirect()->route('customers.index')->with('error', 'العميل غير موجود.');
        }

        $invoices = Invoice::where('customer_id', $customer->id)
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

        $totalDue = (float) Invoice::where('customer_id', $customer->id)
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');

        return view('customers.show', compact('customer', 'invoices', 'totalDue'));
    }
}