@extends('layouts.app', ['title' => 'إضافة صنف جديد'])

@section('content')
<div class="erp-page">

    <div class="erp-section-card">
        <div class="erp-section-header">
            <h3>إضافة صنف جديد</h3>
        </div>

        <div class="erp-section-body">
            <form method="POST" action="{{ route('products.store') }}">
                @csrf

                <div class="row g-4">

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">الشركة</label>
                        <input type="text" name="company" class="form-control erp-input" value="{{ old('company') }}" required>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">الموديل</label>
                        <input type="text" name="model" class="form-control erp-input" value="{{ old('model') }}" required>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">المقاس</label>
                        <input type="text" name="size" class="form-control erp-input" value="{{ old('size') }}" required>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">اللون</label>
                        <input type="text" name="color" class="form-control erp-input" value="{{ old('color') }}">
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">الفرز</label>
                        <input type="text" name="grade" class="form-control erp-input" value="{{ old('grade') }}">
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">سعر الشراء</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control erp-input" value="{{ old('purchase_price') }}" required>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">سعر البيع</label>
                        <input type="number" step="0.01" name="sale_price" class="form-control erp-input" value="{{ old('sale_price') }}" required>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label class="erp-label">الكمية (متر)</label>
                        <input type="number" step="0.01" name="quantity_meter" class="form-control erp-input" value="{{ old('quantity_meter') }}" required>
                    </div>

                </div>

                <div class="erp-form-actions">
                    <a href="{{ route('products.index') }}" class="btn erp-btn-light">رجوع</a>
                    <button type="submit" class="btn erp-btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .erp-page{
        padding: 6px 0 0;
    }

    .erp-section-card{
        background:#fff;
        border-radius: 22px;
        border: 1px solid #e2e8f0;
        overflow:hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
    }

    .erp-section-header{
        padding: 22px 24px 12px;
    }

    .erp-section-header h3{
        margin:0;
        font-size: 1.9rem;
        font-weight: 900;
        color: #0f172a;
        text-align: right;
    }

    .erp-section-body{
        padding: 0 24px 24px;
    }

    .erp-label{
        display:block;
        margin-bottom:10px;
        color:#111827;
        font-size:1.05rem;
        font-weight:800;
    }

    .erp-input{
        min-height: 58px;
        border-radius: 16px;
        border: 1px solid #d7dfeb;
        font-size: 1.05rem;
        padding: 12px 18px;
        box-shadow: none !important;
        background:#fff;
    }

    .erp-input:focus{
        border-color: #2758cb;
    }

    .erp-btn-primary{
        background: #1f48a6;
        color:#fff;
        border:none;
        border-radius: 14px;
        min-height: 52px;
        min-width: 120px;
        font-weight: 800;
        padding: 10px 20px;
    }

    .erp-btn-primary:hover{
        background:#173b8d;
        color:#fff;
    }

    .erp-btn-light{
        background:#f8fafc;
        color:#111827;
        border:1px solid #d9e2ec;
        border-radius:14px;
        min-height:52px;
        min-width: 120px;
        font-weight:800;
        padding:10px 20px;
    }

    .erp-btn-light:hover{
        background:#eef2f7;
        color:#111827;
    }

    .erp-form-actions{
        display:flex;
        justify-content:flex-start;
        gap:12px;
        margin-top:30px;
        flex-wrap:wrap;
    }

    @media (max-width: 768px){
        .erp-section-header h3{
            font-size: 1.45rem;
        }

        .erp-section-body{
            padding: 0 16px 16px;
        }

        .erp-section-header{
            padding: 18px 16px 12px;
        }

        .erp-form-actions .btn{
            width:100%;
        }
    }
</style>
@endpush