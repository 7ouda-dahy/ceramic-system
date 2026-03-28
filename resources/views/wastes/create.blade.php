@extends('layouts.app', ['title' => 'تسجيل هالك جديد'])

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('wastes.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الصنف</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">اختر الصنف</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->full_name }} | المتاح: {{ number_format($product->quantity_meter, 2) }} م
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">الفرع</label>
                    <select name="branch_id" class="form-select">
                        <option value="">بدون فرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">الكمية الهالكة بالمتر</label>
                    <input type="number" step="0.01" min="0.01" name="quantity_meter" class="form-control" value="{{ old('quantity_meter') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">السبب</label>
                    <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required placeholder="مثال: كسر - شرخ - تلف أثناء النقل">
                </div>

                <div class="col-md-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="ملاحظات إضافية إن وجدت">{{ old('notes') }}</textarea>
                </div>

                <div class="col-md-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">حفظ العملية</button>
                    <a href="{{ route('wastes.index') }}" class="btn btn-light border">إلغاء</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection