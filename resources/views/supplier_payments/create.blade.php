@extends('layouts.app', ['title' => 'سداد مورد'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="alert alert-info">
            سيتم السداد من: <strong>{{ $centralCashbox?->name ?? 'الخزنة المركزية' }}</strong>
        </div>

        <form method="GET" action="{{ route('supplier-payments.create') }}" data-no-loader="1">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">اختر المورد</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
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

@if($selectedSupplier)
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('supplier-payments.store') }}">
            @csrf
            <input type="hidden" name="supplier_id" value="{{ $selectedSupplier->id }}">

            <div class="mb-3 fw-bold">المورد: {{ $selectedSupplier->name }}</div>

            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>مبلغ السداد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openInvoices as $invoice)
                            <tr>
                                <td>#{{ $invoice->id }}</td>
                                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td>{{ number_format($invoice->remaining_amount, 2) }}</td>
                                <td>{{ $invoice->payment_status_ar }}</td>
                                <td>
                                    <input type="hidden" name="invoice_id[]" value="{{ $invoice->id }}">
                                    <input type="number" step="0.01" min="0" max="{{ $invoice->remaining_amount }}" name="payment_amount[]" class="form-control" value="0">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">لا توجد فواتير مفتوحة لهذا المورد.</td>
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