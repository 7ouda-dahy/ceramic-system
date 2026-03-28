<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function create()
    {
        $products = Product::orderBy('full_name')->get();
        return view('stock.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_meter' => 'required|numeric|min:0.01',
            'purchase_price' => 'nullable|numeric|min:0.01',
        ]);

        $product = Product::findOrFail($request->product_id);
        $addedQty = (float) $request->quantity_meter;
        $newPurchasePrice = $request->filled('purchase_price') ? (float) $request->purchase_price : null;

        $oldQty = (float) $product->quantity_meter;
        $oldAvg = (float) $product->average_cost;

        if ($newPurchasePrice !== null) {
            $finalQty = $oldQty + $addedQty;
            $newAverageCost = $finalQty > 0
                ? (($oldQty * $oldAvg) + ($addedQty * $newPurchasePrice)) / $finalQty
                : $newPurchasePrice;

            $product->purchase_price = $newPurchasePrice;
            $product->average_cost = round($newAverageCost, 2);
        }

        $product->quantity_meter = $oldQty + $addedQty;
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'quantity_meter' => $addedQty,
            'type' => 'IN',
        ]);

        return redirect()->route('products.index')->with('success', 'تمت إضافة الكمية للمخزون بنجاح.');
    }
}