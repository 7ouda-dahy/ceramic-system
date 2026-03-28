<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with('branch');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('payment_status', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $invoices = $query->latest()->get();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $products = Product::where('quantity_meter', '>', 0)->orderBy('full_name')->get();
        $customers = Customer::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('invoices.create', compact('products', 'customers', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'payment_mode' => 'required|in:immediate,credit',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric|min:0.01',
            'sale_price.*' => 'required|numeric|min:0.01',
        ]);

        $branch = Branch::findOrFail($request->branch_id);
        $cashbox = Cashbox::where('branch_id', $branch->id)->first();

        if (!$cashbox) {
            return back()->withInput()->with('error', 'لا توجد خزنة مرتبطة بالفرع المختار.');
        }

        $customer = Customer::firstOrCreate(
            [
                'name' => trim($request->customer_name),
                'phone' => trim($request->customer_phone ?? ''),
            ],
            [
                'address' => null,
            ]
        );

        $discountValue = (float) ($request->discount_value ?? 0);
        $discountReason = trim($request->discount_reason ?? '');
        $paidAmount = (float) ($request->paid_amount ?? 0);

        $items = [];
        $totalBeforeDiscount = 0;

        foreach ($request->product_id as $index => $productId) {
            $product = Product::findOrFail($productId);

            $qty = (float) $request->quantity[$index];
            $salePrice = (float) $request->sale_price[$index];

            if ($product->quantity_meter < $qty) {
                return back()->withInput()->with('error', 'الكمية غير متاحة للصنف: ' . $product->full_name);
            }

            $lineTotal = $qty * $salePrice;
            $costPrice = (float) $product->average_cost;
            $profitAmount = ($salePrice - $costPrice) * $qty;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'sale_price' => $salePrice,
                'line_total' => $lineTotal,
                'cost_price' => $costPrice,
                'profit_amount' => $profitAmount,
            ];

            $totalBeforeDiscount += $lineTotal;
        }

        if ($discountValue > $totalBeforeDiscount) {
            return back()->withInput()->with('error', 'قيمة الخصم أكبر من إجمالي الفاتورة.');
        }

        $totalAfterDiscount = $totalBeforeDiscount - $discountValue;

        if ($request->payment_mode === 'immediate' && round($paidAmount, 2) !== round($totalAfterDiscount, 2)) {
            return back()->withInput()->with('error', 'في السداد الفوري يجب أن يكون المدفوع مساويًا لإجمالي الفاتورة بعد الخصم.');
        }

        if ($paidAmount > $totalAfterDiscount) {
            return back()->withInput()->with('error', 'المبلغ المدفوع أكبر من إجمالي الفاتورة.');
        }

        $remainingAmount = $totalAfterDiscount - $paidAmount;

        $status = 'paid';
        if ($remainingAmount > 0 && $paidAmount > 0) {
            $status = 'partial';
        } elseif ($paidAmount == 0) {
            $status = 'due';
        }

        DB::beginTransaction();

        try {
            $invoice = Invoice::create([
                'branch_id' => $branch->id,
                'created_by' => Auth::id(),
                'customer_id' => $customer->id,
                'customer_name' => trim($request->customer_name),
                'customer_phone' => trim($request->customer_phone ?? ''),
                'total_amount' => $totalAfterDiscount,
                'discount_value' => $discountValue,
                'discount_reason' => $discountReason,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $status,
            ]);

            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->full_name,
                    'quantity_meter' => $item['qty'],
                    'unit_price' => $item['sale_price'],
                    'cost_price' => $item['cost_price'],
                    'line_total' => $item['line_total'],
                    'profit_amount' => $item['profit_amount'],
                ]);

                $item['product']->quantity_meter -= $item['qty'];
                $item['product']->save();
            }

            if ($paidAmount > 0) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                ]);

                CashboxTransaction::create([
                    'cashbox_id' => $cashbox->id,
                    'created_by' => Auth::id(),
                    'invoice_id' => $invoice->id,
                    'type' => 'IN',
                    'amount' => $paidAmount,
                    'reason' => 'تحصيل من العميل: ' . $invoice->customer_name . ' | فاتورة بيع #' . $invoice->id,
                    'reference_code' => 'SALE-' . $invoice->id,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'تم حفظ فاتورة البيع بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with('branch')->find($id);

        if (!$invoice) {
            return redirect()->route('invoices.index')->with('error', 'الفاتورة غير موجودة.');
        }

        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();

        return view('invoices.show', compact('invoice', 'items'));
    }
}