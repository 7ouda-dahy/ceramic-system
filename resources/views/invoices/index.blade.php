@extends('layouts.app', ['title' => 'فواتير البيع'])

@section('content')
@php
    $invoiceCount = $invoices->count();
    $totalSales = $invoices->sum('total_amount');
    $totalPaid = $invoices->sum('paid_amount');
    $totalRemaining = $invoices->sum('remaining_amount');
@endphp

<div class="container-fluid px-0">

    <div class="page-header-card mb-4">
        <div>
            <h2 class="page-header-title mb-1">فواتير البيع</h2>
            <div class="page-header-subtitle">متابعة جميع فواتير البيع وحالة السداد الخاصة بها</div>
        </div>

        <div class="page-header-actions d-flex gap-2 flex-wrap">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-action-primary">
                <i class="bi bi-plus-circle ms-1"></i>
                فاتورة جديدة
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div class="stat-label">عدد الفواتير</div>
                <div class="stat-value">{{ number_format($invoiceCount) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-label">إجمالي المبيعات</div>
                <div class="stat-value">{{ number_format($totalSales, 2) }} ج.م</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                <div class="stat-label">إجمالي المحصل</div>
                <div class="stat-value">{{ number_format($totalPaid, 2) }} ج.م</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-label">إجمالي المتبقي</div>
                <div class="stat-value">{{ number_format($totalRemaining, 2) }} ج.م</div>
            </div>
        </div>
    </div>

    <div class="page-card mb-4">
        <div class="card-head-custom">
            <div class="card-head-icon">
                <i class="bi bi-funnel"></i>
            </div>
            <div>
                <h5 class="card-head-title mb-1">بحث سريع</h5>
                <div class="card-head-subtitle">ابحث باسم العميل أو رقم الهاتف أو رقم الفاتورة</div>
            </div>
        </div>

        <div class="card-body p-4">
            <form method="GET" action="{{ route('invoices.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-10">
                        <label class="form-label form-label-archon">بحث</label>
                        <input
                            type="text"
                            name="search"
                            class="form-control form-control-archon"
                            value="{{ request('search') }}"
                            placeholder="اكتب اسم العميل أو الهاتف أو رقم الفاتورة">
                    </div>

                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary btn-action-primary w-100">
                            <i class="bi bi-search ms-1"></i>
                            بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="page-card">
        <div class="card-head-custom">
            <div class="card-head-icon">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <h5 class="card-head-title mb-1">سجل فواتير البيع</h5>
                <div class="card-head-subtitle">عرض جميع الفواتير مع المبالغ وحالة السداد</div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center archon-table">
                    <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $statusMap = [
                                'paid' => ['label' => 'مدفوعة', 'class' => 'success'],
                                'partial' => ['label' => 'مدفوعة جزئيًا', 'class' => 'warning'],
                                'due' => ['label' => 'آجلة', 'class' => 'danger'],
                            ];

                            $status = $statusMap[$invoice->payment_status] ?? ['label' => $invoice->payment_status ?? '-', 'class' => 'secondary'];
                        @endphp

                        <tr>
                            <td>
                                <span class="invoice-id-badge">#{{ $invoice->id }}</span>
                            </td>

                            <td class="fw-bold">{{ $invoice->customer_name ?? '-' }}</td>

                            <td>{{ $invoice->customer_phone ?? '-' }}</td>

                            <td>{{ number_format($invoice->total_amount ?? 0, 2) }} ج.م</td>

                            <td class="text-success fw-bold">{{ number_format($invoice->paid_amount ?? 0, 2) }} ج.م</td>

                            <td class="text-danger fw-bold">{{ number_format($invoice->remaining_amount ?? 0, 2) }} ج.م</td>

                            <td>
                                <span class="status-badge {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>

                            <td>{{ optional($invoice->created_at)->format('Y-m-d h:i A') }}</td>

                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <p>لا توجد فواتير بيع مسجلة.</p>
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

    .stat-card{
        position: relative;
        background: #fff;
        border: 1px solid var(--archon-border);
        border-radius: 20px;
        box-shadow: var(--archon-shadow);
        padding: 22px;
        overflow: hidden;
        height: 100%;
    }

    .stat-card::after{
        content:'';
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        width:6px;
        border-radius: 0 10px 10px 0;
    }

    .stat-card-primary::after{ background: var(--archon-primary); }
    .stat-card-success::after{ background: var(--archon-success); }
    .stat-card-info::after{ background: var(--archon-info); }
    .stat-card-warning::after{ background: var(--archon-warning); }

    .stat-icon{
        width:52px;
        height:52px;
        border-radius:16px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.2rem;
        margin-bottom:14px;
        background:#eef4ff;
        color:var(--archon-primary);
    }

    .stat-label{
        color:var(--archon-muted);
        font-size:.95rem;
        margin-bottom:8px;
        font-weight:700;
    }

    .stat-value{
        font-size:1.4rem;
        font-weight:800;
        color:var(--archon-text);
    }

    .btn-action-primary{
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 700;
        min-height: 46px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }

    .card-head-custom{
        display:flex;
        align-items:center;
        gap:16px;
        padding: 22px 24px;
        border-bottom:1px solid var(--archon-border);
        background: linear-gradient(90deg, rgba(20,61,145,.05), rgba(20,61,145,.02));
    }

    .card-head-icon{
        width:52px;
        height:52px;
        border-radius:16px;
        background: linear-gradient(135deg, var(--archon-primary), var(--archon-primary-dark));
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.2rem;
        flex-shrink:0;
        box-shadow: 0 10px 20px rgba(20,61,145,.22);
    }

    .card-head-title{
        font-size:1.08rem;
        font-weight:800;
        color:var(--archon-text);
    }

    .card-head-subtitle{
        color:var(--archon-muted);
        font-size:.94rem;
    }

    .form-label-archon{
        font-weight:800;
        color:#233246;
        margin-bottom:10px;
        font-size:.96rem;
    }

    .form-control-archon{
        min-height:52px;
        border-radius:16px !important;
        border:1px solid #d7e1ef !important;
        background:#fbfdff;
        padding:12px 16px;
        transition:.2s ease;
    }

    .form-control-archon:focus{
        background:#fff;
        border-color:var(--archon-primary-light) !important;
        box-shadow:0 0 0 4px rgba(29,79,184,.10) !important;
    }

    .archon-table thead th{
        background: linear-gradient(90deg, var(--archon-primary-dark), var(--archon-primary));
        color:#fff;
        border:0;
        white-space: nowrap;
    }

    .invoice-id-badge{
        display:inline-block;
        background:#eef4ff;
        color:var(--archon-primary-dark);
        padding:8px 12px;
        border-radius:12px;
        font-weight:800;
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

    .action-buttons{
        display:flex;
        gap:6px;
        justify-content:center;
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
    }
</style>
@endpush