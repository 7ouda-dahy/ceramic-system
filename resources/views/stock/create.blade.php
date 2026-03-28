@extends('layouts.app', ['title' => 'إضافة للمخزون'])

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1 fw-bold">إضافة للمخزون</h3>
                        <p class="text-muted mb-0">
                            هذه الشاشة مخصصة للإضافة اليدوية أو الطارئة للمخزون، وليست بديلًا عن فاتورة الشراء.
                        </p>
                    </div>

                    <div class="text-end">
                        <span class="badge text-bg-warning px-3 py-2 fs-6">إضافة يدوية / طوارئ</span>
                    </div>
                </div>

                <div class="alert alert-info border-0">
                    <div class="fw-bold mb-1">معلومة مهمة</div>
                    <div>
                        عند اختيار سعر شراء جديد، سيتم تحديث سعر شراء الصنف الحالي.  
                        أما إذا تُرك فارغًا، فسيتم فقط زيادة الكمية دون تعديل السعر.
                    </div>
                </div>

                <form method="POST" action="{{ route('stock.store') }}">
                    @csrf

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label fw-bold">الصنف</label>
                            <select name="product_id" class="form-select form-select-lg" required>
                                <option value="">اختر الصنف</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->full_name }}
                                        @if(isset($product->quantity_meter))
                                            | المتاح الحالي: {{ number_format($product->quantity_meter, 2) }} م
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">اختر الصنف المطلوب زيادة كميته.</div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-bold">الكمية بالمتر</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="quantity_meter"
                                value="{{ old('quantity_meter') }}"
                                class="form-control form-control-lg"
                                required
                            >
                            <div class="form-text">أدخل الكمية التي ستُضاف للمخزون.</div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-bold">سعر الشراء الجديد (اختياري)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="purchase_price"
                                value="{{ old('purchase_price') }}"
                                class="form-control form-control-lg"
                            >
                            <div class="form-text">يُستخدم فقط إذا أردت تحديث سعر الشراء الحالي للصنف.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted">
                            تأكد من اختيار الصنف الصحيح والكمية قبل الحفظ.
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-light border px-4">
                                رجوع للأصناف
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save ms-1"></i>
                                حفظ الإضافة للمخزون
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection