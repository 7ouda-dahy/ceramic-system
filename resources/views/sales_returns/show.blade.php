@extends('layouts.app', ['title' => 'تفاصيل المرتجع'])

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>رقم المرتجع:</strong> #{{ $salesReturn->id }}</div>
            <div class="col-md-3"><strong>رقم الفاتورة:</strong> #{{ $salesReturn->invoice_id }}</div>
            <div class="col-md-3"><strong>العميل:</strong> {{ $salesReturn->invoice?->customer_name }}</div>
            <div class="col-md-3"><strong>التاريخ:</strong> {{ $salesReturn->created_at?->format('Y-m-d h:i A') }}</div>
        </div>

        <hr>

        <div><strong>إجمالي المرتجع:</strong> {{ number_format($salesReturn->total_amount, 2) }} ج.م</div>
        <div><strong>المبلغ المرتد نقدًا:</strong> {{ number_format($salesReturn->refund_amount, 2) }} ج.م</div>
        <div><strong>الملاحظات:</strong> {{ $salesReturn->notes }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th>الكمية المرتجعة</th>
                        <th>سعر البيع</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ number_format($item->returned_quantity, 2) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                            <td>{{ number_format($item->line_total, 2) }} ج.م</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">لا توجد أصناف.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('sales-returns.print', $salesReturn->id) }}" target="_blank" class="btn btn-secondary">طباعة</a>
        </div>
    </div>
</div>
@endsection