@extends('layouts.app', ['title' => 'تعديل صنف'])

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('products.update', $product->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">الشركة</label>
                    <input type="text" name="company" class="form-control" value="{{ old('company', $product->company) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">الموديل</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $product->model) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">اللون</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color', $product->color) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">الفرز</label>
                    <input type="text" name="grade" class="form-control" value="{{ old('grade', $product->grade) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">المقاس</label>
                    <input type="text" name="size" class="form-control" value="{{ old('size', $product->size) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">سعر الشراء</label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">سعر البيع</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">الكمية بالمتر</label>
                    <input type="number" step="0.01" name="quantity_meter" class="form-control" value="{{ old('quantity_meter', $product->quantity_meter) }}">
                </div>

                <div class="col-md-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('products.index') }}" class="btn btn-light border">إلغاء</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection