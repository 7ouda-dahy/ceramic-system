@extends('layouts.app', ['title' => 'تفاصيل سداد المورد'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>رقم السند:</strong> {{ $payment->reference_code }}</div>
            <div class="col-md-3"><strong>المورد:</strong> {{ $payment->supplier?->name }}</div>
            <div class="col-md-3"><strong>الخزنة:</strong> {{ $payment->cashbox?->name }}</div>
            <div class="col-md-3"><strong>التاريخ:</strong> {{ $payment->created_at?->format('Y-m-d h:i A') }}</div>
        </div>

        <hr>

        <div><strong>إجمالي مديونية المورد قبل السداد:</strong> {{ number_format($supplierDueBefore, 2) }} ج.م</div>
        <div><strong>إجمالي السداد:</strong> {{ number_format($payment->total_amount, 2) }} ج.م</div>
        <div><strong>المتبقي على المورد بعد السداد:</strong> {{ number_format($supplierRemainingAfter, 2) }} ج.م</div>
        <div><strong>ملاحظات:</strong> {{ $payment->notes ?: '—' }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('supplier-payments.print', $payment->id) }}" target="_blank" class="btn btn-secondary">طباعة</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>رقم فاتورة الشراء</th>
                        <th>قبل السداد</th>
                        <th>مبلغ السداد</th>
                        <th>بعد السداد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payment->items as $item)
                        <tr>
                            <td>#{{ $item->purchase_invoice_id }}</td>
                            <td>{{ number_format((float) $item->remaining_before, 2) }} ج.م</td>
                            <td>{{ number_format((float) $item->amount, 2) }} ج.م</td>
                            <td>{{ number_format((float) $item->remaining_after, 2) }} ج.م</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">لا توجد بنود.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection