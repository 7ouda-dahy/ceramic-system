@extends('layouts.app', ['title' => 'فواتير الشراء'])

@push('styles')
<style>
    .purchase-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .purchase-stat-card {
        border-radius: 18px;
        padding: 18px 20px;
        color: #fff;
        box-shadow: var(--archon-shadow);
        min-height: 120px;
    }

    .purchase-stat-card .label {
        font-size: .95rem;
        font-weight: 700;
        opacity: .95;
        margin-bottom: 12px;
    }

    .purchase-stat-card .value {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .purchase-stat-card .meta {
        margin-top: 8px;
        font-size: .85rem;
        opacity: .88;
    }

    .bg-count   { background: linear-gradient(135deg, #111827, #374151); }
    .bg-total   { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .bg-paid    { background: linear-gradient(135deg, #198754, #20a86b); }
    .bg-due     { background: linear-gradient(135deg, #dc3545, #ef4444); }

    .filter-card {
        margin-bottom: 22px;
    }

    .status-badge {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
    }

    .status-paid {
        background: #d1e7dd;
        color: #0f5132;
    }

    .status-partial {
        background: #fff3cd;
        color: #8a6d00;
    }

    .status-due {
        background: #f8d7da;
        color: #842029;
    }

    .purchase-actions {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .amount-main {
        font-weight: 800;
        color: var(--archon-text);
    }

    .amount-sub {
        font-size: .83rem;
        color: var(--archon-muted);
        margin-top: 3px;
    }

    .toolbar-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    @media (max-width: 1200px) {
        .purchase-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .purchase-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="purchase-stats">
    <div class="purchase-stat-card bg-count">
        <div class="label">عدد الفواتير</div>
        <div class="value">{{ number_format($stats['count']) }}</div>
        <div class="meta">إجمالي عدد فواتير الشراء</div>
    </div>

    <div class="purchase-stat-card bg-total">
        <div class="label">إجمالي الشراء</div>
        <div class="value">{{ number_format($stats['total'], 2) }} ج.م</div>
        <div class="meta">إجمالي قيمة فواتير الشراء</div>
    </div>

    <div class="purchase-stat-card bg-paid">
        <div class="label">المدفوع</div>
        <div class="value">{{ number_format($stats['paid'], 2) }} ج.م</div>
        <div class="meta">المسدد للموردين</div>
    </div>

    <div class="purchase-stat-card bg-due">
        <div class="label">المتبقي</div>
        <div class="value">{{ number_format($stats['due'], 2) }} ج.م</div>
        <div class="meta">الذمم المفتوحة على الموردين</div>
    </div>
</div>

<div class="page-card filter-card">
    <div class="card-body">
        <div class="toolbar-row">
            <h3 class="section-title mb-0">بحث وفلترة فواتير الشراء</h3>
            <a href="{{ route('purchase-invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle ms-1"></i>
                فاتورة شراء جديدة
            </a>
        </div>

        <form method="GET" action="{{ route('purchase-invoices.index') }}" data-no-loader="1">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label">بحث</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="رقم الفاتورة / اسم المورد / الهاتف / مرجع المورد"
                    >
                </div>

                <div class="col-lg-2">
                    <label class="form-label">الحالة</label>
                    <select name="payment_status" class="form-select">
                        <option value="">كل الحالات</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>مدفوعة</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>مدفوعة جزئيًا</option>
                        <option value="due" {{ request('payment_status') === 'due' ? 'selected' : '' }}>آجلة</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-lg-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-lg-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary w-100">تطبيق</button>
                    <a href="{{ route('purchase-invoices.index') }}" class="btn btn-light w-100">مسح</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="page-card">
    <div class="card-body">
        <div class="toolbar-row">
            <h3 class="section-title mb-0">سجل فواتير الشراء</h3>
            <div class="text-muted">عدد النتائج: {{ $invoices->count() }}</div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المورد</th>
                        <th>الهاتف</th>
                        <th>مرجع المورد</th>
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
                        <tr>
                            <td><strong>#{{ $invoice->id }}</strong></td>
                            <td>{{ $invoice->supplier_name }}</td>
                            <td>{{ $invoice->supplier_phone ?: '-' }}</td>
                            <td>{{ $invoice->supplier_invoice_reference ?: '-' }}</td>

                            <td>
                                <div class="amount-main">{{ number_format($invoice->total_amount, 2) }} ج.م</div>
                            </td>

                            <td>
                                <div class="amount-main text-success">{{ number_format($invoice->paid_amount, 2) }} ج.م</div>
                            </td>

                            <td>
                                <div class="amount-main text-danger">{{ number_format($invoice->remaining_amount, 2) }} ج.م</div>
                            </td>

                            <td>
                                @if($invoice->payment_status === 'paid')
                                    <span class="status-badge status-paid">مدفوعة</span>
                                @elseif($invoice->payment_status === 'partial')
                                    <span class="status-badge status-partial">مدفوعة جزئيًا</span>
                                @else
                                    <span class="status-badge status-due">آجلة</span>
                                @endif
                            </td>

                            <td>
                                <div class="amount-main">{{ $invoice->created_at?->format('Y-m-d') }}</div>
                                <div class="amount-sub">{{ $invoice->created_at?->format('h:i A') }}</div>
                            </td>

                            <td>
                                <div class="purchase-actions">
                                    <a href="{{ route('purchase-invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                    <a href="{{ route('purchase-invoices.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">طباعة</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-muted py-4">لا توجد فواتير شراء مطابقة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection