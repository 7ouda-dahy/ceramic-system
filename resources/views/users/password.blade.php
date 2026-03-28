@extends('layouts.app', ['title' => 'تغيير كلمة المرور'])

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-3">تغيير كلمة المرور للمستخدم: {{ $user->name }}</h5>
        <form method="POST" action="{{ route('users.password.update', $user) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-warning">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection