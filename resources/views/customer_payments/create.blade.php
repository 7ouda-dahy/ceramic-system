@extends('layouts.app', ['title' => 'سداد عميل'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customer-payments.create') }}" data-no-loader="1">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">اختر العميل</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <button class="btn btn-primary w-100">عرض الفواتير المفتوحة</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedCustomer)
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('customer-payments.store') }}">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">

            <div class="mb-3 fw-bold">العميل: {{ $selectedCustomer->name }}</div>

            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>الخزنة التي سيُسجل بها السداد</th>
                            <th>مبلغ السداد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openInvoices as $invoice)
                            <tr>
                                <td>#{{ $invoice->id }}</td>
                                <td>{{ number_format($invoice->remaining_amount, 2) }}</td>
                                <td>{{ $invoice->payment_status_ar }}</td>
                                <td>{{ $invoice->cashbox_name }}</td>
                                <td>
                                    <input type="hidden" name="invoice_id[]" value="{{ $invoice->id }}">
                                    <input type="number" step="0.01" min="0" max="{{ $invoice->remaining_amount }}" name="payment_amount[]" class="form-control" value="0">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">لا توجد فواتير مفتوحة لهذا العميل.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($openInvoices->count())
            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">تسجيل السداد</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endif
@endsection