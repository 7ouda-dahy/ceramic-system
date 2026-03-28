@extends('layouts.app', ['title' => 'تفاصيل الفاتورة'])

@section('content')
@php
    $statusMap = [
        'paid' => ['label' => 'مدفوعة', 'class' => 'success'],
        'partial' => ['label' => 'مدفوعة جزئيًا', 'class' => 'warning'],
        'due' => ['label' => 'آجلة', 'class' => 'danger'],
    ];

    $status = $statusMap[$invoice->payment_status] ?? ['label' => $invoice->payment_status ?? '-', 'class' => 'secondary'];
@endphp

<div class="container-fluid px-0">

    <div class="page-header-card mb-4">
        <div>
            <h2 class="page-header-title mb-1">تفاصيل الفاتورة #{{ $invoice->id }}</h2>
            <div class="page-header-subtitle">عرض بيانات العميل والأصناف والحساب النهائي للفاتورة</div>
        </div>

        <div class="page-header-actions d-flex gap-2 flex-wrap">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-archon">
                <i class="bi bi-arrow-right-circle ms-1"></i>
                رجوع
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="page-card h-100">
                <div class="card-head-custom">
                    <div class="card-head-icon"><i class="bi bi-person-badge"></i></div>
                    <div>
                        <h5 class="card-head-title mb-1">بيانات العميل</h5>
                        <div class="card-head-subtitle">البيانات الأساسية المرتبطة بالفاتورة</div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ $invoice->customer_name ?? '-' }}</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">الهاتف</div>
                            <div class="info-value">{{ $invoice->customer_phone ?? '-' }}</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">التاريخ</div>
                            <div class="info-value">{{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : '-' }}</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">الوقت</div>
                            <div class="info-value">{{ $invoice->created_at ? $invoice->created_at->format('h:i A') : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="page-card h-100">
                <div class="card-head-custom">
                    <div class="card-head-icon"><i class="bi bi-calculator"></i></div>
                    <div>
                        <h5 class="card-head-title mb-1">الحساب</h5>
                        <div class="card-head-subtitle">ملخص القيم المالية وحالة السداد</div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="summary-list">
                        <div class="summary-item">
                            <span>الإجمالي بعد الخصم</span>
                            <strong>{{ number_format($invoice->total_amount, 2) }} ج.م</strong>
                        </div>

                        <div class="summary-item">
                            <span>الخصم</span>
                            <strong>{{ number_format($invoice->discount_value, 2) }} ج.م</strong>
                        </div>

                        <div class="summary-item">
                            <span>سبب الخصم</span>
                            <strong>{{ $invoice->discount_reason ?: '-' }}</strong>
                        </div>

                        <div class="summary-item">
                            <span>المدفوع</span>
                            <strong class="text-success">{{ number_format($invoice->paid_amount, 2) }} ج.م</strong>
                        </div>

                        <div class="summary-item">
                            <span>المتبقي</span>
                            <strong class="text-danger">{{ number_format($invoice->remaining_amount, 2) }} ج.م</strong>
                        </div>

                        <div class="summary-item">
                            <span>الحالة</span>
                            <strong>
                                <span class="status-badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-card">
        <div class="card-head-custom">
            <div class="card-head-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div>
                <h5 class="card-head-title mb-1">الأصناف</h5>
                <div class="card-head-subtitle">عرض تفاصيل الأصناف المضافة داخل الفاتورة</div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center archon-table">
                    <thead>
                    <tr>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->product_name }}</td>
                            <td>{{ number_format($item->quantity_meter, 2) }} م</td>
                            <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                            <td class="fw-bold text-primary">{{ number_format($item->line_total, 2) }} ج.م</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-box"></i>
                                    <p>لا توجد أصناف داخل هذه الفاتورة.</p>
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
    .page-header-card{
        background: linear-gradient(135deg, #ffffff 0%, #f7faff 100%);
        border: 1px solid var(--archon-border);
        border-radius: 22px;
        box-shadow: var(--archon-shadow);
        padding: 24px 26px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:20px;
        flex-wrap:wrap;
    }

    .page-header-title{
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--archon-text);
    }

    .page-header-subtitle{
        color: var(--archon-muted);
        font-size: .97rem;
    }

    .btn-outline-archon{
        border:1px solid var(--archon-primary);
        color: var(--archon-primary);
        background:#fff;
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 700;
    }

    .btn-outline-archon:hover{
        background: var(--archon-primary);
        color:#fff;
    }

    .card-head-custom{
        display:flex;
        align-items:center;
        gap:16px;
        padding: 24px 28px;
        border-bottom:1px solid var(--archon-border);
        background: linear-gradient(90deg, rgba(20,61,145,.05), rgba(20,61,145,.02));
    }

    .card-head-icon{
        width:54px;
        height:54px;
        border-radius:16px;
        background: linear-gradient(135deg, var(--archon-primary), var(--archon-primary-dark));
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size: 1.35rem;
        flex-shrink:0;
        box-shadow: 0 10px 20px rgba(20,61,145,.22);
    }

    .card-head-title{
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--archon-text);
    }

    .card-head-subtitle{
        color: var(--archon-muted);
        font-size: .94rem;
    }

    .info-grid{
        display:grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap:16px;
    }

    .info-box{
        background:#fbfdff;
        border:1px solid #e5edf7;
        border-radius:16px;
        padding:16px;
    }

    .info-label{
        color:var(--archon-muted);
        font-size:.9rem;
        margin-bottom:6px;
        font-weight:700;
    }

    .info-value{
        color:var(--archon-text);
        font-size:1rem;
        font-weight:800;
    }

    .summary-list{
        display:flex;
        flex-direction:column;
        gap:12px;
    }

    .summary-item{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        padding:14px 16px;
        background:#fbfdff;
        border:1px solid #e5edf7;
        border-radius:14px;
    }

    .summary-item span{
        color:var(--archon-muted);
        font-weight:700;
    }

    .summary-item strong{
        color:var(--archon-text);
        font-weight:800;
    }

    .archon-table thead th{
        background: linear-gradient(90deg, var(--archon-primary-dark), var(--archon-primary));
        color:#fff;
        border:0;
        white-space: nowrap;
    }

    .status-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:108px;
        padding:8px 12px;
        border-radius:12px;
        font-size:.88rem;
        font-weight:800;
    }

    .status-badge.success{
        background:#eaf8ef;
        color:#198754;
    }

    .status-badge.warning{
        background:#fff5df;
        color:#b7791f;
    }

    .status-badge.danger{
        background:#fdeaea;
        color:#dc3545;
    }

    .status-badge.secondary{
        background:#edf0f4;
        color:#667085;
    }

    .empty-state{
        text-align:center;
        padding:30px;
        color:var(--archon-muted);
    }

    .empty-state i{
        font-size:2rem;
        display:block;
        margin-bottom:10px;
    }

    @media (max-width: 768px){
        .page-header-card,
        .card-head-custom{
            padding:18px;
        }

        .page-header-title{
            font-size:1.3rem;
        }

        .info-grid{
            grid-template-columns: 1fr;
        }

        .summary-item{
            flex-direction:column;
            align-items:flex-start;
        }
    }
</style>
@endpush