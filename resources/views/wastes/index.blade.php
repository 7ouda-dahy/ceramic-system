@extends('layouts.app', ['title' => 'الهالك'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('wastes.index') }}" data-no-loader="1">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="الصنف أو السبب أو رقم العملية">
                </div>

                <div class="col-md-3">
                    <label class="form-label">الفرع</label>
                    <select name="branch_id" class="form-select">
                        <option value="">كل الفروع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
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

                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100">تطبيق</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-6">
                <h6>إجمالي كمية الهالك</h6>
                <div class="fw-bold fs-4">{{ number_format($summaryQuantity, 2) }} م</div>
            </div>
            <div class="col-md-6">
                <h6>إجمالي قيمة الهالك</h6>
                <div class="fw-bold fs-4">{{ number_format($summaryValue, 2) }} ج.م</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="fw-bold">سجل الهالك</div>
            <a href="{{ route('wastes.create') }}" class="btn btn-primary">تسجيل هالك جديد</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>قيمة الوحدة</th>
                        <th>إجمالي القيمة</th>
                        <th>السبب</th>
                        <th>الفرع</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td>{{ $record->product_name }}</td>
                            <td>{{ number_format($record->quantity_meter, 2) }} م</td>
                            <td>{{ number_format($record->unit_cost, 2) }} ج.م</td>
                            <td>{{ number_format($record->total_cost, 2) }} ج.م</td>
                            <td>{{ $record->reason }}</td>
                            <td>{{ $record->branch?->name ?? '-' }}</td>
                            <td>{{ $record->created_at?->format('Y-m-d h:i A') }}</td>
                            <td>
                                <a href="{{ route('wastes.show', $record->id) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">لا توجد عمليات هالك مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection