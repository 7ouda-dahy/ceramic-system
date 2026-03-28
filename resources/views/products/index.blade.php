@extends('layouts.app', ['title' => 'الأصناف'])

@section('content')
@php
    $productsCount = $products->count();
    $totalStock = $products->sum('quantity_meter');
    $lowStockCount = $products->where('quantity_meter', '<=', 500)->count();
    $avgSalePrice = $productsCount > 0 ? $products->avg('sale_price') : 0;
@endphp

<div class="erp-page">

    <div class="erp-stats-grid mb-4">
        <div class="erp-stat-card stat-dark">
            <div class="stat-card-title">عدد الأصناف</div>
            <div class="stat-card-value">{{ number_format($productsCount) }}</div>
        </div>

        <div class="erp-stat-card stat-blue">
            <div class="stat-card-title">إجمالي المخزون</div>
            <div class="stat-card-value">{{ number_format($totalStock, 2) }} م</div>
        </div>

        <div class="erp-stat-card stat-green">
            <div class="stat-card-title">متوسط سعر البيع</div>
            <div class="stat-card-value">{{ number_format($avgSalePrice, 2) }} ج.م</div>
        </div>

        <div class="erp-stat-card stat-red">
            <div class="stat-card-title">أصناف منخفضة المخزون</div>
            <div class="stat-card-value">{{ number_format($lowStockCount) }}</div>
        </div>
    </div>

    <div class="erp-section-card mb-4">
        <div class="erp-section-header">
            <h3>بحث</h3>
        </div>

        <div class="erp-section-body">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8">
                        <input
                            type="text"
                            name="search"
                            class="form-control erp-input"
                            value="{{ request('search') }}"
                            placeholder="بحث باسم الشركة / الموديل / المقاس / اللون / الفرز">
                    </div>

                    <div class="col-lg-2">
                        <button type="submit" class="btn erp-btn-primary w-100">بحث</button>
                    </div>

                    <div class="col-lg-2">
                        <a href="{{ route('products.create') }}" class="btn erp-btn-light w-100">
                            إضافة صنف جديد
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="erp-section-card">
        <div class="erp-section-header">
            <h3>سجل الأصناف</h3>
        </div>

        <div class="erp-section-body p-0">
            <div class="table-responsive">
                <table class="table erp-table text-center align-middle mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>الشركة</th>
                        <th>الموديل</th>
                        <th>المقاس</th>
                        <th>اللون</th>
                        <th>الفرز</th>
                        <th>سعر الشراء</th>
                        <th>سعر البيع</th>
                        <th>المخزون</th>
                        <th>الإجراءات</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="fw-bold">#{{ $product->id }}</td>
                            <td class="fw-bold">{{ $product->company }}</td>
                            <td>{{ $product->model }}</td>
                            <td>{{ $product->size }}</td>
                            <td>{{ $product->color }}</td>
                            <td>{{ $product->grade }}</td>
                            <td>{{ number_format($product->purchase_price, 2) }} ج.م</td>
                            <td class="text-primary fw-bold">{{ number_format($product->sale_price, 2) }} ج.م</td>
                            <td>
                                @if(($product->quantity_meter ?? 0) <= 500)
                                    <span class="stock-badge stock-low">{{ number_format($product->quantity_meter, 2) }} م</span>
                                @else
                                    <span class="stock-badge stock-good">{{ number_format($product->quantity_meter, 2) }} م</span>
                                @endif
                            </td>
                            <td>
                                <div class="erp-action-group">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn erp-btn-outline btn-sm">
                                        تعديل
                                    </a>

                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn erp-btn-danger btn-sm" onclick="return confirm('هل تريد حذف الصنف؟')">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="erp-empty-state">
                                    لا توجد أصناف مسجلة حتى الآن
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .erp-page{
        padding: 6px 0 0;
    }

    .erp-stats-grid{
        display:grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap:20px;
    }

    .erp-stat-card{
        border-radius: 22px;
        color:#fff;
        padding:26px 28px;
        min-height:134px;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .10);
    }

    .stat-dark{
        background: linear-gradient(90deg, #101827, #334155);
    }

    .stat-blue{
        background: linear-gradient(90deg, #173f97, #2758cb);
    }

    .stat-green{
        background: linear-gradient(90deg, #1d8d52, #27ae60);
    }

    .stat-red{
        background: linear-gradient(90deg, #e4374b, #f05b6b);
    }

    .stat-card-title{
        font-size: 1.15rem;
        font-weight: 800;
        text-align: right;
    }

    .stat-card-value{
        font-size: 2rem;
        font-weight: 900;
        line-height: 1.1;
        text-align: center;
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

    .erp-input{
        min-height: 58px;
        border-radius: 16px;
        border: 1px solid #d7dfeb;
        font-size: 1.05rem;
        padding: 12px 18px;
        box-shadow: none !important;
    }

    .erp-input:focus{
        border-color: #2758cb;
    }

    .erp-btn-primary{
        background: #1f48a6;
        color:#fff;
        border:none;
        border-radius: 14px;
        min-height: 58px;
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
        min-height:58px;
        font-weight:800;
        padding:10px 20px;
    }

    .erp-btn-light:hover{
        background:#eef2f7;
        color:#111827;
    }

    .erp-btn-outline{
        background:#fff;
        color:#2563eb;
        border:1px solid #2563eb;
        border-radius:10px;
        font-weight:700;
        min-width:60px;
    }

    .erp-btn-outline:hover{
        background:#eff6ff;
        color:#1d4ed8;
        border-color:#1d4ed8;
    }

    .erp-btn-danger{
        background:#fff;
        color:#dc3545;
        border:1px solid #dc3545;
        border-radius:10px;
        font-weight:700;
        min-width:60px;
    }

    .erp-btn-danger:hover{
        background:#fff1f2;
        color:#b91c1c;
        border-color:#b91c1c;
    }

    .erp-table thead th{
        background:#163d95 !important;
        color:#fff !important;
        font-size:1.05rem;
        font-weight:800;
        border:none !important;
        padding:18px 14px !important;
        white-space:nowrap;
    }

    .erp-table tbody td{
        padding:18px 14px !important;
        border-color:#e8edf5 !important;
        font-size:1rem;
        vertical-align:middle;
    }

    .erp-table tbody tr:hover{
        background:#fafcff;
    }

    .stock-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:110px;
        padding:8px 14px;
        border-radius:999px;
        font-size:.92rem;
        font-weight:800;
    }

    .stock-good{
        background:#e8f8ee;
        color:#198754;
    }

    .stock-low{
        background:#fde8e8;
        color:#dc3545;
    }

    .erp-action-group{
        display:flex;
        justify-content:center;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .erp-empty-state{
        padding:30px;
        text-align:center;
        color:#64748b;
        font-weight:700;
    }

    @media (max-width: 1200px){
        .erp-stats-grid{
            grid-template-columns: repeat(2, minmax(0,1fr));
        }
    }

    @media (max-width: 768px){
        .erp-stats-grid{
            grid-template-columns: 1fr;
        }

        .erp-section-header h3{
            font-size: 1.45rem;
        }

        .erp-section-body{
            padding: 0 16px 16px;
        }

        .erp-section-header{
            padding: 18px 16px 12px;
        }

        .stat-card-value{
            font-size:1.6rem;
        }
    }
</style>
@endpush