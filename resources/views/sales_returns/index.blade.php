@extends('layouts.app', ['title' => 'عرض المرتجعات'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('sales-returns.index') }}" data-no-loader="1">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="رقم المرتجع / رقم الفاتورة / اسم العميل">
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
                    <label class="form-label">نوع التسوية</label>
                    <select name="refund_filter" class="form-select">
                        <option value="">الكل</option>
                        <option value="cash" {{ request('refund_filter') === 'cash' ? 'selected' : '' }}>به رد نقدي</option>
                        <option value="settlement" {{ request('refund_filter') === 'settlement' ? 'selected' : '' }}>تسوية فقط</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">تطبيق</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>رقم المرتجع</th>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>إجمالي المرتجع</th>
                        <th>رد نقدي</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td>#{{ $return->id }}</td>
                            <td>#{{ $return->invoice_id }}</td>
                            <td>{{ $return->invoice?->customer_name }}</td>
                            <td>{{ number_format($return->total_amount, 2) }} ج.م</td>
                            <td>{{ number_format($return->refund_amount, 2) }} ج.م</td>
                            <td>{{ $return->created_at?->format('Y-m-d h:i A') }}</td>
                            <td class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('sales-returns.show', $return->id) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                <a href="{{ route('sales-returns.print', $return->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">طباعة</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">لا توجد مرتجعات.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection