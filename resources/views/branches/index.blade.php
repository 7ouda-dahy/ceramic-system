@extends('layouts.app', ['title' => 'الفروع والخزن'])

@section('content')
<div class="row g-4">
    @foreach($branches as $branch)
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>{{ $branch->name }}</h5>
                    <div class="text-muted mb-2">الخزنة المرتبطة: {{ $branch->cashbox->name ?? 'لا توجد خزنة' }}</div>
                    <div><strong>رصيد الخزنة:</strong> <span class="currency">{{ number_format($branch->cashbox->balance ?? 0, 2) }}</span></div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>الخزنة المركزية</h5>
                @php $central = \App\Models\Cashbox::where('is_central', true)->first(); @endphp
                <div class="text-muted mb-2">خزنة مستقلة بدون فرع</div>
                <div><strong>الرصيد:</strong> <span class="currency">{{ number_format($central?->balance ?? 0, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection