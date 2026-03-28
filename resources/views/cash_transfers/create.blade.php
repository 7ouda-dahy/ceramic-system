@extends('layouts.app', ['title' => 'تحويل جديد'])

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('cash-transfers.store') }}" onsubmit="return confirmAction('تأكيد تنفيذ التحويل؟');">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">من خزنة</label>
                    <select name="from_cashbox_id" class="form-select" required>
                        <option value="">اختر الخزنة المصدر</option>
                        @foreach($cashboxes as $cashbox)
                            <option value="{{ $cashbox->id }}">{{ $cashbox->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">إلى خزنة</label>
                    <select name="to_cashbox_id" class="form-select" required>
                        <option value="">اختر الخزنة الوجهة</option>
                        @foreach($cashboxes as $cashbox)
                            <option value="{{ $cashbox->id }}">{{ $cashbox->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">المبلغ</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" placeholder="ملاحظة اختيارية">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">تنفيذ التحويل</button>
            </div>
        </form>
    </div>
</div>
@endsection