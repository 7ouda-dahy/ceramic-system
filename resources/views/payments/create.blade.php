@extends('layouts.app', ['title' => 'سداد عميل'])

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">الفاتورة</label>
                    <select name="invoice_id" class="form-select" required>
                        <option value="">اختر الفاتورة</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                #{{ $invoice->id }} | {{ $invoice->customer_name }} | {{ $invoice->branch?->name }} | المتبقي: {{ number_format($invoice->remaining_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">المبلغ</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">تسجيل السداد</button>
            </div>
        </form>
    </div>
</div>
@endsection