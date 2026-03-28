@extends('layouts.app', ['title' => 'الموردون والذمم'])

@push('styles')
<style>
    .page-hero {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 18px;
        margin-bottom: 22px;
    }

    .hero-card {
        background: linear-gradient(135deg, #111827, #143d91);
        color: #fff;
        border-radius: 20px;
        padding: 22px;
        box-shadow: var(--archon-shadow);
    }

    .hero-card h2 {
        font-size: 1.45rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .hero-card p {
        margin: 0;
        color: rgba(255,255,255,.82);
    }

    .mini-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
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
        font-size: .92rem;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .mini-stat .value {
        font-size: 1.7rem;
        font-weight: 800;
        color: #111827;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1.1fr .7fr auto;
        gap: 14px;
        align-items: end;
    }

    .table-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .status-amount {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 120px;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 800;
        background: #fde2e4;
        color: #9f1239;
    }

    .empty-state {
        text-align: center;
        padding: 32px 16px;
        color: var(--archon-muted);
    }

    @media (max-width: 992px) {
        .page-hero,
        .filter-grid,
        .mini-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $suppliersCount = $suppliers->count();
    $totalDue = (float) $suppliers->sum('total_due');
@endphp

<div class="page-hero">
    <div class="hero-card">
        <h2>إدارة الموردين والذمم</h2>
        <p>مراجعة الالتزامات المستحقة على الشركة، والوصول السريع لفواتير الموردين المفتوحة وسدادها من نفس القسم.</p>
    </div>

    <div class="mini-stats">
        <div class="mini-stat">
            <div class="label">عدد الموردين الظاهرين</div>
            <div class="value">{{ number_format($suppliersCount) }}</div>
        </div>

        <div class="mini-stat">
            <div class="label">إجمالي الذمم المستحقة علينا</div>
            <div class="value">{{ number_format($totalDue, 2) }}</div>
        </div>
    </div>
</div>

<div class="page-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('suppliers.index') }}" data-no-loader="1">
            <div class="filter-grid">
                <div>
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="اسم المورد أو الهاتف">
                </div>

                <div>
                    <label class="form-label">ترتيب الذمم</label>
                    <select name="sort" class="form-select">
                        <option value="">افتراضي</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>الأقدم</option>
                        <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>الأحدث</option>
                        <option value="highest_due" {{ ($sort ?? '') === 'highest_due' ? 'selected' : '' }}>الأعلى مديونية</option>
                        <option value="lowest_due" {{ ($sort ?? '') === 'lowest_due' ? 'selected' : '' }}>الأقل مديونية</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary px-4">تطبيق</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light">إعادة ضبط</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="page-card">
    <div class="card-body">
        <div class="table-actions">
            <h3 class="section-title mb-0">قائمة الموردين أصحاب الذمم</h3>

            <div class="d-flex gap-2">
                <a href="{{ route('supplier-payments.create') }}" class="btn btn-primary">سداد مورد</a>
                <a href="{{ route('suppliers.create') }}" class="btn btn-light">إضافة مورد</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>المورد</th>
                        <th>الهاتف</th>
                        <th>إجمالي الذمم</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="fw-bold">{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td>
                                <a href="{{ route('suppliers.show', $supplier->id) }}" class="status-amount">
                                    {{ number_format($supplier->total_due, 2) }} ج.م
                                </a>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-outline-primary">عرض التفاصيل</a>
                                    <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-primary">سداد</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">لا يوجد موردون عليهم ذمم حاليًا.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection