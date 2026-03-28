<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $productsCount = Product::count();
        $stockQty = (float) Product::sum('quantity_meter');

        $salesDue = (float) Invoice::sum('remaining_amount');
        $purchaseDue = class_exists(\App\Models\PurchaseInvoice::class)
            ? (float) PurchaseInvoice::sum('remaining_amount')
            : 0.0;

        $todayProfit = (float) InvoiceItem::whereDate('created_at', today())->sum('profit_amount');
        $totalSales = (float) Invoice::sum('total_amount');

        $topProducts = InvoiceItem::select('product_name', DB::raw('SUM(quantity_meter) as total_qty'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::where('quantity_meter', '<=', 500)
            ->orderBy('quantity_meter')
            ->limit(5)
            ->get();

        $cashboxes = Cashbox::orderBy('id')->get();
        $totalCashBalance = (float) $cashboxes->sum('balance');

        $monthlySales = Invoice::selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $monthlyProfit = InvoiceItem::selectRaw('DATE(created_at) as day, SUM(profit_amount) as total')
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chartLabels = [];
        $salesChartData = [];
        $profitChartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();

            $chartLabels[] = $date->format('d/m');
            $salesChartData[] = (float) ($monthlySales[$key]->total ?? 0);
            $profitChartData[] = (float) ($monthlyProfit[$key]->total ?? 0);
        }

        return view('dashboard', compact(
            'productsCount',
            'stockQty',
            'salesDue',
            'purchaseDue',
            'todayProfit',
            'totalSales',
            'topProducts',
            'lowStockProducts',
            'cashboxes',
            'totalCashBalance',
            'chartLabels',
            'salesChartData',
            'profitChartData'
        ));
    }
}