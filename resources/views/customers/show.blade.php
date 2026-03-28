@extends('layouts.app', ['title' => 'تفاصيل العميل'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>اسم العميل:</strong> {{ $customer->name }}</div>
            <div class="col-md-4"><strong>الهاتف:</strong> {{ $customer->phone ?: '-' }}</div>
            <div class="col-md-4"><strong>إجمالي المديونية:</strong> {{ number_format($totalDue, 2) }} ج.م</div>
        </div>

        <div class="mt-3">
            <a href="{{ route('customer-payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">سداد من نفس القسم</a>
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
                        <th>إجمالي الفاتورة</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice->id) }}">#{{ $invoice->id }}</a></td>
                            <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }} ج.م</td>
                            <td>{{ number_format($invoice->remaining_amount, 2) }} ج.م</td>
                            <td>{{ $invoice->payment_status_ar }}</td>
                            <td>{{ $invoice->created_at?->format('Y-m-d h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">لا توجد فواتير مفتوحة لهذا العميل.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection