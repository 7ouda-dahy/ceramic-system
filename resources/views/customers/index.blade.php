@extends('layouts.app', ['title' => 'العملاء والذمم'])

@push('styles')
<style>
    .page-hero {
        background: linear-gradient(135deg, #143d91, #1e56c5);
        color: #fff;
        border-radius: 20px;
        padding: 22px 24px;
        margin-bottom: 22px;
        box-shadow: var(--archon-shadow);
    }

    .page-hero h2 {
        margin: 0 0 8px;
        font-size: 1.6rem;
        font-weight: 800;
    }

    .page-hero p {
        margin: 0;
        color: rgba(255,255,255,.88);
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .mini-stat {
        background: #fff;
        border: 1px solid var(--archon-border);
        border-radius: 18px;
        padding: 18px;
        box-shadow: var(--archon-shadow);
    }

    .mini-stat .label {
        color: var(--archon-muted);
        font-size: .95rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .mini-stat .value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--archon-primary-dark);
        line-height: 1.1;
    }

    .tools-card {
        margin-bottom: 18px;
    }

    .action-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .due-pill {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 999px;
        font-weight: 800;
        font-size: .92rem;
        min-width: 110px;
    }

    .due-high {
        background: #f8d7da;
        color: #842029;
    }

    .due-medium {
        background: #fff3cd;
        color: #8a6700;
    }

    .due-low {
        background: #d1e7dd;
        color: #0f5132;
    }

    .table-actions {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    @media (max-width: 900px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-hero">
    <h2>إدارة العملاء والذمم</h2>
    <p>متابعة العملاء الذين عليهم مديونيات، مع البحث والترتيب والوصول السريع لتفاصيل كل عميل.</p>
</div>

<div class="stats-row">
    <div class="mini-stat">
        <div class="label">إجمالي الذمم المستحقة لنا</div>
        <div class="value">{{ number_format($totalDue, 2) }}</div>
    </div>

    <div class="mini-stat">
        <div class="label">عدد العملاء الظاهرين</div>
        <div class="value">{{ number_format($customersCount) }}</div>
    </div>

    <div class="mini-stat">
        <div class="label">متوسط المديونية</div>
        <div class="value">{{ number_format($averageDue, 2) }}</div>
    </div>
</div>

<div class="page-card tools-card">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" data-no-loader="1">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="اسم العميل أو الهاتف">
                </div>

                <div class="col-md-3">
                    <label class="form-label">ترتيب الذمم</label>
                    <select name="sort" class="form-select">
                        <option value="">افتراضي</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>الأقدم</option>
                        <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>الأحدث</option>
                        <option value="highest_due" {{ ($sort ?? '') === 'highest_due' ? 'selected' : '' }}>الأعلى مديونية</option>
                        <option value="lowest_due" {{ ($sort ?? '') === 'lowest_due' ? 'selected' : '' }}>الأقل مديونية</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">تطبيق</button>
                </div>

                <div class="col-md-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-light w-100">إعادة ضبط</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="action-row">
    <a href="{{ route('customer-payments.create') }}" class="btn btn-primary">سداد عميل</a>
    <a href="{{ route('customers.create') }}" class="btn btn-light">إضافة عميل</a>
</div>

<div class="page-card">
    <div class="card-body">
        <h3 class="section-title">قائمة العملاء أصحاب الذمم</h3>

        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>العنوان</th>
                        <th>المديونية المستحقة لنا</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @php
                            $dueClass = $customer->total_due >= 20000
                                ? 'due-high'
                                : ($customer->total_due >= 5000 ? 'due-medium' : 'due-low');
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $customer->name }}</td>
                            <td>{{ $customer->phone ?: '-' }}</td>
                            <td>{{ $customer->address ?: '-' }}</td>
                            <td>
                                <a href="{{ route('customers.show', $customer->id) }}">
                                    <span class="due-pill {{ $dueClass }}">
                                        {{ number_format($customer->total_due, 2) }} ج.م
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">عرض التفاصيل</a>
                                    <a href="{{ route('customer-payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">سداد</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted py-4">لا يوجد عملاء عليهم ذمم حاليًا.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection