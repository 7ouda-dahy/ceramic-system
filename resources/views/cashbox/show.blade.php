@extends('layouts.app', ['title' => $cashbox->name])

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted mb-2">رصيد الخزنة الحالي</div>
                @php
                    $balanceClass = (float) $cashbox->balance <= 0
                        ? 'text-danger'
                        : ((float) $cashbox->balance <= 3000 ? 'text-warning' : 'text-primary');
                @endphp
                <div class="fs-2 fw-bold {{ $balanceClass }}">
                    {{ number_format((float) $cashbox->balance, 2) }} ج.م
                </div>
                <div class="small text-muted mt-2">الرصيد الفعلي الحالي للخزنة</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted mb-2">إجمالي الدخل</div>
                <div class="fs-2 fw-bold text-success">
                    {{ number_format((float) $totalIn, 2) }} ج.م
                </div>
                <div class="small text-muted mt-2">إجمالي الحركات الداخلة حسب الفلاتر الحالية</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted mb-2">إجمالي المصروفات</div>
                <div class="fs-2 fw-bold text-danger">
                    {{ number_format((float) $totalOut, 2) }} ج.م
                </div>
                <div class="small text-muted mt-2">إجمالي الحركات الخارجة حسب الفلاتر الحالية</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h5 class="mb-3">فلترة وبحث</h5>

        <form method="GET" action="{{ route('cashbox.show', request()->route('slug')) }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">الفترة</label>
                <select name="filter" class="form-select">
                    <option value="">الكل</option>
                    <option value="today" {{ request('filter') === 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="month" {{ request('filter') === 'month' ? 'selected' : '' }}>هذا الشهر</option>
                    <option value="custom" {{ request('filter') === 'custom' ? 'selected' : '' }}>فترة مخصصة</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">نوع الحركة</label>
                <select name="type" class="form-select">
                    <option value="">الكل</option>
                    <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>دخل</option>
                    <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>خرج</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">بحث</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="السبب / المرجع / رقم الحركة"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">تطبيق</button>
                <a href="{{ route('cashbox.show', request()->route('slug')) }}" class="btn btn-light border">إعادة تعيين</a>
                <a href="{{ route('cashbox.print', request()->route('slug')) }}" target="_blank" class="btn btn-outline-secondary">طباعة</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="mb-3">تسجيل مصروف</h5>

                <form method="POST" action="{{ route('cashbox.expense', request()->route('slug')) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">السبب</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="اكتب سبب المصروف"></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger">تسجيل المصروف</button>
                </form>
            </div>
        </div>
    </div>

    @php
        $currentCashboxId = $cashbox->id;
        $allCashboxes = \App\Models\Cashbox::where('id', '!=', $currentCashboxId)->orderBy('name')->get();
    @endphp

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="mb-3">تحويل إلى خزنة أخرى</h5>

                <form method="POST" action="{{ route('cashbox.transfer', request()->route('slug')) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">الخزنة المحول إليها</label>
                        <select name="to_cashbox_id" class="form-select" required>
                            <option value="">اختر الخزنة</option>
                            @foreach($allCashboxes as $targetCashbox)
                                <option value="{{ $targetCashbox->id }}">{{ $targetCashbox->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">سبب التحويل</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="اكتب سبب التحويل"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">تنفيذ التحويل</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-3">سجل الحركات</h5>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
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
                            <td>{{ $transaction->created_at?->format('Y-m-d h:i A') }}</td>
                            <td>{{ number_format((float) $transaction->amount, 2) }} ج.م</td>
                            <td class="text-wrap" style="min-width: 220px;">{{ $transaction->reason }}</td>
                            <td>
                                @if($transaction->type === 'IN')
                                    <span class="badge bg-success-subtle text-success border">دخل</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">خرج</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) ($transaction->balance_after ?? 0), 2) }} ج.م</td>
                            <td>{{ $transaction->reference_code ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">لا توجد حركات على هذه الخزنة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection