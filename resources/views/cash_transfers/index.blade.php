@extends('layouts.app', ['title' => 'التحويلات'])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="mb-0">قائمة التحويلات</h5>
            <a href="{{ route('cash-transfers.create') }}" class="btn btn-primary btn-sm">تحويل جديد</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>الكود</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>المبلغ</th>
                        <th>ملاحظات</th>
                        <th>بواسطة</th>
                        <th>التاريخ والوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->reference_code }}</td>
                            <td>{{ $transfer->fromCashbox->name }}</td>
                            <td>{{ $transfer->toCashbox->name }}</td>
                            <td>{{ number_format($transfer->amount,2) }} ج.م</td>
                            <td>{{ $transfer->notes ?: '-' }}</td>
                            <td>{{ $transfer->creator?->name ?: '-' }}</td>
                            <td class="date-text">{{ $transfer->created_at?->format('Y-m-d') }}<br>{{ $transfer->created_at?->format('h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">لا توجد تحويلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection