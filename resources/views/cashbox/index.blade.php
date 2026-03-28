@extends('layouts.app', ['title' => $cashbox->name])

@push('styles')
<style>
    .cashbox-filter-card,
    .cashbox-form-card,
    .cashbox-table-card {
        border: 1px solid var(--archon-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: var(--archon-shadow);
        margin-bottom: 22px;
        overflow: hidden;
    }

    .cashbox-filter-card .card-body,
    .cashbox-form-card .card-body,
    .cashbox-table-card .card-body {
        padding: 22px;
    }

    .cashbox-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .cashbox-stat-card {
        border-radius: 20px;
        padding: 24px;
        color: #fff;
        min-height: 145px;
        box-shadow: var(--archon-shadow);
        position: relative;
        overflow: hidden;
    }

    .cashbox-stat-card::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        left: -24px;
        bottom: -30px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .cashbox-stat-income {
        background: linear-gradient(135deg, #198754, #24a86a);
    }

    .cashbox-stat-expense {
        background: linear-gradient(135deg, #dc3545, #ef5f6f);
    }

    .cashbox-stat-balance {
        background: linear-gradient(135deg, #143d91, #1f5bca);
    }

    .cashbox-stat-label {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 14px;
        opacity: .96;
    }

    .cashbox-stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 10px;
    }

    .cashbox-stat-meta {
        font-size: .92rem;
        opacity: .88;
    }

    .cashbox-form-title {
        font-size: 1.05rem;
        font-weight: 800;
        margin-bottom: 18px;
        color: var(--archon-text);
    }

    .cashbox-form-stack {
        display: flex;
        flex-direction: column;
        gap: 22px;
        margin-bottom: 24px;
    }

    .cashbox-section-block {
        border: 1px solid var(--archon-border);
        border-radius: 16px;
        padding: 20px;
        background: #fff;
    }

    .cashbox-table-card .table thead th {
        white-space: nowrap;
    }

    .type-badge {
        display: inline-block;
        min-width: 90px;
        text-align: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: .86rem;
        font-weight: 800;
    }

    .type-in {
        background: #d1fae5;
        color: #065f46;
    }

    .type-out {
        background: #fee2e2;
        color: #991b1b;
    }

    .reason-cell {
        max-width: 320px;
        white-space: normal;
        word-break: break-word;
    }

    .ref-code {
        font-family: Arial, sans-serif;
        direction: ltr;
        display: inline-block;
    }

    @media (max-width: 1100px) {
        .cashbox-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="cashbox-filter-card">
    <div class="card-body">
        <form method="GET" action="{{ route('cashbox.show', request()->route('slug')) }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">نوع الفلتر</label>
                    <select name="filter" class="form-select" onchange="toggleCashboxDates(this.value)">
                        <option value="today" {{ $filterType === 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>هذا الشهر</option>
                        <option value="custom" {{ $filterType === 'custom' ? 'selected' : '' }}>فترة مخصصة</option>
                    </select>
                </div>

                <div class="col-md-3 cashbox-date-col" style="{{ $filterType === 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-3 cashbox-date-col" style="{{ $filterType === 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">تطبيق الفلتر</button>
                    <a href="{{ route('cashbox.print', array_merge(['slug' => request()->route('slug')], request()->query())) }}" target="_blank" class="btn btn-light border w-100">طباعة</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="cashbox-stats">
    {{-- يمين --}}
    <div class="cashbox-stat-card cashbox-stat-income">
        <div class="cashbox-stat-label">إجمالي الدخل ({{ $filterLabel }})</div>
        <div class="cashbox-stat-value">{{ number_format($totalIn, 2) }} ج.م</div>
        <div class="cashbox-stat-meta">جميع حركات الدخل حسب الفلتر المختار</div>
    </div>

    {{-- وسط --}}
    <div class="cashbox-stat-card cashbox-stat-expense">
        <div class="cashbox-stat-label">إجمالي المصروفات ({{ $filterLabel }})</div>
        <div class="cashbox-stat-value">{{ number_format($totalOut, 2) }} ج.م</div>
        <div class="cashbox-stat-meta">جميع حركات المصروفات حسب الفلتر المختار</div>
    </div>

    {{-- شمال --}}
    <div class="cashbox-stat-card cashbox-stat-balance">
        <div class="cashbox-stat-label">الرصيد الحالي ({{ $filterLabel }})</div>
        <div class="cashbox-stat-value">{{ number_format($cashbox->balance, 2) }} ج.م</div>
        <div class="cashbox-stat-meta">الرصيد الحالي الفعلي للخزنة الآن</div>
    </div>
</div>

<div class="cashbox-form-stack">
    <div class="cashbox-form-card">
        <div class="card-body">
            <div class="cashbox-form-title">تسجيل مصروف</div>

            <form method="POST" action="{{ route('cashbox.expense', request()->route('slug')) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="أدخل مبلغ المصروف" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">السبب <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="اكتب سبب المصروف بشكل واضح" required></textarea>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">حفظ المصروف</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cashbox-form-card">
        <div class="card-body">
            <div class="cashbox-form-title">تحويل بين الخزن</div>

            <form method="POST" action="{{ route('cashbox.transfer', request()->route('slug')) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">الخزنة المحول إليها</label>
                        <select name="to_cashbox_id" class="form-select" required>
                            <option value="">اختر الخزنة</option>
                            @foreach($availableCashboxes as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="أدخل مبلغ التحويل" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">سبب التحويل <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="مثال: توريد للمركزية / مصروف فرع / تسوية" required></textarea>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">تنفيذ التحويل</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="cashbox-table-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0">سجل الحركات</h3>
            <div class="text-muted small">إجمالي الحركات: {{ $transactions->count() }}</div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>رقم الحركة</th>
                        <th>التاريخ والوقت</th>
                        <th>المبلغ</th>
                        <th>السبب</th>
                        <th>نوع الحركة</th>
                        <th>الرصيد بعد الحركة</th>
                        <th>المرجع</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ optional($transaction->created_at)->format('Y-m-d h:i A') }}</td>
                            <td>{{ number_format($transaction->amount, 2) }} ج.م</td>
                            <td class="reason-cell">{{ $transaction->reason }}</td>
                            <td>
                                @if($transaction->type === 'IN')
                                    <span class="type-badge type-in">دخل</span>
                                @else
                                    <span class="type-badge type-out">مصروف</span>
                                @endif
                            </td>
                            <td>
                                {{ number_format((float) ($transaction->balance_after ?? 0), 2) }} ج.م
                            </td>
                            <td>
                                @if($transaction->reference_code)
                                    <span class="ref-code">{{ $transaction->reference_code }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted py-4">لا توجد حركات على هذه الخزنة حسب الفلتر الحالي</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCashboxDates(value) {
        const cols = document.querySelectorAll('.cashbox-date-col');
        cols.forEach(col => {
            col.style.display = value === 'custom' ? '' : 'none';
        });
    }
</script>
@endpush