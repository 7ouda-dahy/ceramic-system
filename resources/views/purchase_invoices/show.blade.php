@extends('layouts.app', ['title' => 'تفاصيل فاتورة الشراء'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>رقم الفاتورة:</strong> #{{ $invoice->id }}</div>
            <div class="col-md-3"><strong>المورد:</strong> {{ $invoice->supplier_name }}</div>
            <div class="col-md-3"><strong>الهاتف:</strong> {{ $invoice->supplier_phone ?: '-' }}</div>
            <div class="col-md-3"><strong>مرجع المورد:</strong> {{ $invoice->supplier_invoice_reference ?: '-' }}</div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-3"><strong>الإجمالي:</strong> {{ number_format($invoice->total_amount, 2) }} ج.م</div>
            <div class="col-md-3"><strong>المدفوع:</strong> {{ number_format($invoice->paid_amount, 2) }} ج.م</div>
            <div class="col-md-3"><strong>المتبقي:</strong> {{ number_format($invoice->remaining_amount, 2) }} ج.م</div>
            <div class="col-md-3"><strong>الحالة:</strong> {{ $invoice->payment_status }}</div>
        </div>

        <hr>

        <div><strong>الملاحظات:</strong> {{ $invoice->notes ?: '—' }}</div>
        <div><strong>التاريخ:</strong> {{ $invoice->created_at?->format('Y-m-d h:i A') }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الشراء</th>
                        <th>الإجمالي</th>
                        <th>ملاحظات الصنف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ number_format($item->quantity_meter, 2) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                            <td>{{ number_format($item->line_total, 2) }} ج.م</td>
                            <td>{{ $item->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">لا توجد أصناف.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('purchase-invoices.print', $invoice->id) }}" target="_blank" class="btn btn-secondary">طباعة</a>
        </div>
    </div>
</div>
@endsection