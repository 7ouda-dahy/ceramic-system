<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseInvoice::query()->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('supplier_phone', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->get()->map(function ($invoice) {
            $invoice->payment_status_ar = $this->paymentStatusAr($invoice->payment_status);
            return $invoice;
        });

        $statsQuery = PurchaseInvoice::query();

        if ($request->filled('date_from')) {
            $statsQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $statsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $statsInvoices = $statsQuery->get();

        $stats = [
            'count' => $statsInvoices->count(),
            'total' => (float) $statsInvoices->sum('total_amount'),
            'paid' => (float) $statsInvoices->sum('paid_amount'),
            'due' => (float) $statsInvoices->sum('remaining_amount'),
        ];

        return view('purchase_invoices.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $products = Product::orderBy('full_name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $centralCashbox = Cashbox::where('is_central', true)->first();

        return view('purchase_invoices.create', compact('products', 'suppliers', 'centralCashbox'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:255',
            'supplier_invoice_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'payment_mode' => 'required|in:immediate,credit',
            'paid_amount' => 'nullable|numeric|min:0',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric|min:0.01',
            'price.*' => 'required|numeric|min:0.01',
        ]);

        $centralCashbox = Cashbox::where('is_central', true)->first();

        if (!$centralCashbox) {
            return back()->withInput()->with('error', 'الخزنة المركزية غير موجودة.');
        }

        $supplier = Supplier::firstOrCreate(
            [
                'name' => trim($request->supplier_name),
                'phone' => trim($request->supplier_phone ?? ''),
            ],
            [
                'address' => null,
            ]
        );

        $items = [];
        $total = 0;

        foreach ($request->product_id as $index => $productId) {
            $product = Product::findOrFail($productId);

            $qty = (float) $request->quantity[$index];
            $price = (float) $request->price[$index];
            $lineTotal = $qty * $price;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $lineTotal,
            ];

            $total += $lineTotal;
        }

        $paidAmount = (float) ($request->paid_amount ?? 0);

        if ($request->payment_mode === 'immediate' && round($paidAmount, 2) !== round($total, 2)) {
            return back()->withInput()->with('error', 'في السداد الفوري يجب أن يكون المدفوع مساويًا لإجمالي الفاتورة.');
        }

        if ($paidAmount > $total) {
            return back()->withInput()->with('error', 'المبلغ المدفوع أكبر من إجمالي الفاتورة.');
        }

        if ($paidAmount > (float) $centralCashbox->balance) {
            return back()->withInput()->with('error', 'الرصيد الحالي في الخزنة المركزية لا يسمح بإتمام عملية الشراء.');
        }

        $remainingAmount = $total - $paidAmount;
        $status = $this->resolvePaymentStatus($paidAmount, $remainingAmount);

        DB::beginTransaction();

        try {
            $invoice = PurchaseInvoice::create([
                'supplier_id' => $supplier->id,
                'created_by' => Auth::id(),
                'supplier_name' => trim($request->supplier_name),
                'supplier_phone' => trim($request->supplier_phone ?? ''),
                'supplier_invoice_reference' => trim($request->supplier_invoice_reference ?? ''),
                'notes' => trim($request->notes ?? ''),
                'total_amount' => $total,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $status,
            ]);

            foreach ($items as $item) {
                $product = $item['product'];

                $oldQty = (float) $product->quantity_meter;
                $oldAvg = (float) $product->average_cost;
                $lastPurchasePrice = (float) $product->purchase_price;

                $newQty = $item['qty'];
                $newPrice = $item['price'];
                $finalQty = $oldQty + $newQty;

                $newAverageCost = $finalQty > 0
                    ? (($oldQty * $oldAvg) + ($newQty * $newPrice)) / $finalQty
                    : $newPrice;

                $priceAlert = '';
                if ($lastPurchasePrice > 0 && abs($newPrice - $lastPurchasePrice) / $lastPurchasePrice >= 0.25) {
                    $priceAlert = 'تنبيه: تغير سعر الشراء من ' . number_format($lastPurchasePrice, 2) . ' إلى ' . number_format($newPrice, 2);
                }

                $product->quantity_meter = $finalQty;
                $product->purchase_price = $newPrice;
                $product->average_cost = round($newAverageCost, 2);
                $product->save();

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->full_name,
                    'quantity_meter' => $newQty,
                    'unit_price' => $newPrice,
                    'line_total' => $item['line_total'],
                    'notes' => $priceAlert,
                ]);
            }

            if ($paidAmount > 0) {
                CashboxTransaction::create([
                    'cashbox_id' => $centralCashbox->id,
                    'created_by' => Auth::id(),
                    'invoice_id' => null,
                    'type' => 'OUT',
                    'amount' => $paidAmount,
                    'reason' => 'دفع للمورد: ' . $invoice->supplier_name . ' | فاتورة شراء #' . $invoice->id,
                    'reference_code' => 'PUR-' . $invoice->id,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-invoices.show', $invoice->id)
                ->with('success', 'تم حفظ فاتورة الشراء بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ فاتورة الشراء: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $invoice = PurchaseInvoice::find($id);

        if (!$invoice) {
            return redirect()->route('purchase-invoices.index')->with('error', 'فاتورة الشراء غير موجودة.');
        }

        $invoice->payment_status_ar = $this->paymentStatusAr($invoice->payment_status);
        $items = PurchaseInvoiceItem::where('purchase_invoice_id', $invoice->id)->get();

        return view('purchase_invoices.show', compact('invoice', 'items'));
    }

    public function print($id)
    {
        $invoice = PurchaseInvoice::find($id);

        if (!$invoice) {
            return redirect()->route('purchase-invoices.index')->with('error', 'فاتورة الشراء غير موجودة.');
        }

        $invoice->payment_status_ar = $this->paymentStatusAr($invoice->payment_status);
        $items = PurchaseInvoiceItem::where('purchase_invoice_id', $invoice->id)->get();
        $centralCashbox = Cashbox::where('is_central', true)->first();

        return view('purchase_invoices.print', compact('invoice', 'items', 'centralCashbox'));
    }

    private function resolvePaymentStatus(float $paidAmount, float $remainingAmount): string
    {
        if ($remainingAmount > 0 && $paidAmount > 0) {
            return 'partial';
        }

        if ($paidAmount == 0) {
            return 'due';
        }

        return 'paid';
    }

    private function paymentStatusAr(?string $status): string
    {
        return match ($status) {
            'paid' => 'مدفوعة',
            'partial' => 'مدفوعة جزئيًا',
            'due' => 'آجلة',
            default => (string) $status,
        };
    }
}