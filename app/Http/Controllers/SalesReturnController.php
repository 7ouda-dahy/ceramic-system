<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesReturn::with('invoice')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('invoice_id', $search)
                    ->orWhereHas('invoice', function ($sub) use ($search) {
                        $sub->where('customer_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('refund_filter')) {
            if ($request->refund_filter === 'cash') {
                $query->where('refund_amount', '>', 0);
            } elseif ($request->refund_filter === 'settlement') {
                $query->where('refund_amount', '=', 0);
            }
        }

        $returns = $query->get();

        return view('sales_returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $invoices = Invoice::latest()->get();
        $selectedInvoice = null;
        $items = collect();
        $warning = null;
        $daysPassed = null;
        $lastReturnDate = null;

        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::find($request->invoice_id);

            if ($selectedInvoice) {
                $daysPassed = now()->startOfDay()->diffInDays($selectedInvoice->created_at->startOfDay());

                if ($daysPassed > 14) {
                    $lastReturnDate = $selectedInvoice->created_at->copy()->addDays(14)->format('Y-m-d');
                    $warning = 'تنبيه: هذه الفاتورة مر عليها أكثر من 14 يوم. آخر يوم ضمن مدة الاسترجاع كان ' . $lastReturnDate . '، وسيتم السماح بالمرتجع مع التنبيه فقط.';
                }

                $items = InvoiceItem::where('invoice_id', $selectedInvoice->id)
                    ->get()
                    ->map(function ($item) {
                        $alreadyReturned = (float) SalesReturnItem::where('invoice_item_id', $item->id)->sum('returned_quantity');
                        $available = max(0, (float) $item->quantity_meter - $alreadyReturned);

                        return (object) [
                            'invoice_item_id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product_name,
                            'sold_quantity' => (float) $item->quantity_meter,
                            'already_returned' => $alreadyReturned,
                            'available' => $available,
                            'price' => (float) $item->unit_price,
                        ];
                    });
            }
        }

        return view('sales_returns.create', compact(
            'invoices',
            'selectedInvoice',
            'items',
            'warning',
            'daysPassed',
            'lastReturnDate'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'reason' => 'required|string|max:255',
            'invoice_item_id' => 'required|array|min:1',
            'return_quantity' => 'required|array|min:1',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // مهم جدًا: المرتجع يخصم من خزنة نفس فرع الفاتورة الأصلية فقط
        $branchCashbox = Cashbox::where('branch_id', $invoice->branch_id)->first();

        if (!$branchCashbox) {
            return back()->withInput()->with('error', 'لا توجد خزنة مرتبطة بفرع الفاتورة الأصلية.');
        }

        DB::beginTransaction();

        try {
            $returnItems = [];
            $totalReturn = 0;

            foreach ($request->invoice_item_id as $index => $invoiceItemId) {
                $qty = (float) ($request->return_quantity[$index] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)
                    ->where('id', $invoiceItemId)
                    ->first();

                if (!$invoiceItem) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'أحد أصناف الفاتورة غير صحيح.');
                }

                $alreadyReturned = (float) SalesReturnItem::where('invoice_item_id', $invoiceItem->id)->sum('returned_quantity');
                $available = max(0, (float) $invoiceItem->quantity_meter - $alreadyReturned);

                if ($qty > $available) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'الكمية المرتجعة أكبر من المتاح للصنف: ' . $invoiceItem->product_name);
                }

                $lineTotal = $qty * (float) $invoiceItem->unit_price;

                $returnItems[] = [
                    'invoice_item' => $invoiceItem,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                ];

                $totalReturn += $lineTotal;
            }

            if ($totalReturn <= 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'أدخل كمية مرتجعة صحيحة لصنف واحد على الأقل.');
            }

            $oldTotal = (float) $invoice->total_amount;
            $oldPaid = (float) $invoice->paid_amount;
            $oldRemaining = (float) $invoice->remaining_amount;

            if ($totalReturn > $oldTotal) {
                DB::rollBack();
                return back()->withInput()->with('error', 'إجمالي المرتجع أكبر من إجمالي الفاتورة.');
            }

            // الأولوية للتسوية من المديونية
            $settledAmount = min($oldRemaining, $totalReturn);

            // الزيادة فقط تُرد نقدًا من خزنة الفرع
            $refundAmount = $totalReturn - $settledAmount;

            if ($refundAmount > 0 && $refundAmount > (float) $branchCashbox->balance) {
                DB::rollBack();
                return back()->withInput()->with('error', 'رصيد خزنة الفرع لا يكفي لرد المبلغ النقدي.');
            }

            $invoice->remaining_amount = max(0, $oldRemaining - $settledAmount);
            $invoice->paid_amount = max(0, $oldPaid - $refundAmount);
            $invoice->total_amount = max(0, $oldTotal - $totalReturn);

            if ($invoice->remaining_amount == 0 && $invoice->paid_amount > 0) {
                $invoice->payment_status = 'paid';
            } elseif ($invoice->remaining_amount > 0 && $invoice->paid_amount > 0) {
                $invoice->payment_status = 'partial';
            } else {
                $invoice->payment_status = 'due';
            }

            $notes = 'سبب المرتجع: ' . trim($request->reason);
            if ($settledAmount > 0 && $refundAmount > 0) {
                $notes .= ' | تم خصم ' . number_format($settledAmount, 2) . ' ج.م من المديونية ورد ' . number_format($refundAmount, 2) . ' ج.م نقدًا';
            } elseif ($settledAmount > 0) {
                $notes .= ' | تم خصم ' . number_format($settledAmount, 2) . ' ج.م من المديونية';
            } elseif ($refundAmount > 0) {
                $notes .= ' | تم رد ' . number_format($refundAmount, 2) . ' ج.م نقدًا';
            }

            $invoice->save();

            $salesReturn = SalesReturn::create([
                'invoice_id' => $invoice->id,
                'created_by' => Auth::id(),
                'total_amount' => $totalReturn,
                'refund_amount' => $refundAmount,
                'notes' => $notes,
            ]);

            foreach ($returnItems as $row) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'invoice_item_id' => $row['invoice_item']->id,
                    'product_id' => $row['invoice_item']->product_id,
                    'product_name' => $row['invoice_item']->product_name,
                    'returned_quantity' => $row['qty'],
                    'unit_price' => $row['invoice_item']->unit_price,
                    'line_total' => $row['line_total'],
                ]);

                $product = Product::find($row['invoice_item']->product_id);
                if ($product) {
                    $product->quantity_meter += $row['qty'];
                    $product->save();
                }
            }

            // مهم جدًا: الرد النقدي يخرج من خزنة فرع الفاتورة وليس المركزية
            if ($refundAmount > 0) {
                CashboxTransaction::create([
                    'cashbox_id' => $branchCashbox->id,
                    'created_by' => Auth::id(),
                    'invoice_id' => $invoice->id,
                    'type' => 'OUT',
                    'amount' => $refundAmount,
                    'reason' => 'رد نقدي للعميل: ' . $invoice->customer_name . ' | مرتجع بيع #' . $salesReturn->id . ' | فرع الفاتورة #' . $invoice->branch_id,
                    'reference_code' => 'RET-' . $salesReturn->id,
                ]);
            }

            DB::commit();

            return redirect()->route('sales-returns.index')->with('success', 'تم حفظ المرتجع بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ المرتجع: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $salesReturn = SalesReturn::with('invoice')->find($id);

        if (!$salesReturn) {
            return redirect()->route('sales-returns.index')->with('error', 'المرتجع غير موجود.');
        }

        $items = SalesReturnItem::where('sales_return_id', $salesReturn->id)->get();

        return view('sales_returns.show', compact('salesReturn', 'items'));
    }

    public function print($id)
    {
        $salesReturn = SalesReturn::with('invoice')->find($id);

        if (!$salesReturn) {
            return redirect()->route('sales-returns.index')->with('error', 'المرتجع غير موجود.');
        }

        $items = SalesReturnItem::where('sales_return_id', $salesReturn->id)->get();

        return view('sales_returns.print', compact('salesReturn', 'items'));
    }
}