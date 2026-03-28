@extends('layouts.app', ['title' => 'تفاصيل عملية الهالك'])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <strong>رقم العملية:</strong>
                <div>#{{ $record->id }}</div>
            </div>

            <div class="col-md-4">
                <strong>الصنف:</strong>
                <div>{{ $record->product_name }}</div>
            </div>

            <div class="col-md-4">
                <strong>الكمية الهالكة:</strong>
                <div>{{ number_format($record->quantity_meter, 2) }} م</div>
            </div>

            <div class="col-md-4">
                <strong>قيمة الوحدة:</strong>
                <div>{{ number_format($record->unit_cost, 2) }} ج.م</div>
            </div>

            <div class="col-md-4">
                <strong>إجمالي القيمة:</strong>
                <div>{{ number_format($record->total_cost, 2) }} ج.م</div>
            </div>

            <div class="col-md-4">
                <strong>الفرع:</strong>
                <div>{{ $record->branch?->name ?? '-' }}</div>
            </div>

            <div class="col-md-4">
                <strong>السبب:</strong>
                <div>{{ $record->reason }}</div>
            </div>

            <div class="col-md-4">
                <strong>بواسطة:</strong>
                <div>{{ $record->creator?->name ?? '-' }}</div>
            </div>

            <div class="col-md-4">
                <strong>التاريخ والوقت:</strong>
                <div>{{ $record->created_at?->format('Y-m-d h:i A') }}</div>
            </div>

            <div class="col-md-12">
                <strong>الملاحظات:</strong>
                <div>{{ $record->notes ?: '-' }}</div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('wastes.index') }}" class="btn btn-light border">رجوع</a>
        </div>
    </div>
</div>
@endsection