@extends('layouts.app', ['title' => 'تفاصيل سداد العميل'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>رقم السند:</strong> {{ $payment->reference_code }}</div>
            <div class="col-md-3"><strong>العميل:</strong> {{ $payment->customer?->name }}</div>
            <div class="col-md-3"><strong>إجمالي السداد:</strong> {{ number_format($payment->total_amount, 2) }} ج.م</div>
            <div class="col-md-3"><strong>التاريخ:</strong> {{ $payment->created_at?->format('Y-m-d h:i A') }}</div>
        </div>

        <hr>

        <div><strong>إجمالي مديونية العميل قبل السداد:</strong> {{ number_format($customerDueBefore, 2) }} ج.م</div>
        <div><strong>المتبقي على العميل بعد السداد:</strong> {{ number_format($customerRemainingAfter, 2) }} ج.م</div>
        <div><strong>الملاحظات:</strong> {{ $payment->notes ?: '—' }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('customer-payments.print', $payment->id) }}" target="_blank" class="btn btn-secondary">طباعة سند السداد</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>الخزنة</th>
                        <th>قبل السداد</th>
                        <th>مبلغ السداد</th>
                        <th>بعد السداد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payment->items as $item)
                        <tr>
                            <td>#{{ $item->invoice_id }}</td>
                            <td>{{ $item->cashbox?->name ?? '-' }}</td>
                            <td>{{ number_format((float) $item->remaining_before, 2) }} ج.م</td>
                            <td>{{ number_format((float) $item->amount, 2) }} ج.م</td>
                            <td>{{ number_format((float) $item->remaining_after, 2) }} ج.م</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">لا توجد بنود.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection