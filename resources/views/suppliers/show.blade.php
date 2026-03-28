@extends('layouts.app', ['title' => 'تفاصيل المورد'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>اسم المورد:</strong> {{ $supplier->name }}</div>
            <div class="col-md-4"><strong>الهاتف:</strong> {{ $supplier->phone ?: '-' }}</div>
            <div class="col-md-4"><strong>إجمالي الذمم:</strong> {{ number_format($totalDue, 2) }} ج.م</div>
        </div>

        <div class="mt-3">
            <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">سداد للمورد</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>مرجع المورد</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('purchase-invoices.show', $invoice->id) }}">#{{ $invoice->id }}</a></td>
                            <td>{{ $invoice->supplier_invoice_reference ?: '-' }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }} ج.م</td>
                            <td>{{ number_format($invoice->remaining_amount, 2) }} ج.م</td>
                            <td>{{ $invoice->payment_status_ar }}</td>
                            <td>{{ $invoice->created_at?->format('Y-m-d h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">لا توجد فواتير لهذا المورد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection