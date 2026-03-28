<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('grade', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        $products = $query->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'quantity_meter' => 'nullable|numeric|min:0',
        ]);

        $fullName = implode(' - ', [
            trim($request->company),
            trim($request->model),
            trim($request->size),
            trim($request->color),
            trim($request->grade),
        ]);

        Product::create([
            'company' => trim($request->company),
            'model' => trim($request->model),
            'color' => trim($request->color),
            'grade' => trim($request->grade),
            'size' => trim($request->size),
            'full_name' => $fullName,
            'purchase_price' => $request->purchase_price,
            'sale_price' => $request->sale_price,
            'average_cost' => $request->purchase_price,
            'quantity_meter' => $request->quantity_meter ?? 0,
        ]);

        return redirect()->route('products.index')->with('success', 'تمت إضافة الصنف بنجاح.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'company' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'quantity_meter' => 'nullable|numeric|min:0',
        ]);

        $fullName = implode(' - ', [
            trim($request->company),
            trim($request->model),
            trim($request->size),
            trim($request->color),
            trim($request->grade),
        ]);

        $product->update([
            'company' => trim($request->company),
            'model' => trim($request->model),
            'color' => trim($request->color),
            'grade' => trim($request->grade),
            'size' => trim($request->size),
            'full_name' => $fullName,
            'purchase_price' => $request->purchase_price,
            'sale_price' => $request->sale_price,
            'quantity_meter' => $request->quantity_meter ?? 0,
        ]);

        return redirect()->route('products.index')->with('success', 'تم تعديل الصنف بنجاح.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'تم حذف الصنف بنجاح.');
    }
}