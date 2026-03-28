@extends('layouts.app', ['title' => 'المستخدمون'])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="mb-0">قائمة المستخدمين</h5>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">إضافة مستخدم</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>الفرع</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->branch->name ?? 'عام' }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-danger">موقوف</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('users.password.edit', $user) }}" class="btn btn-sm btn-warning">تغيير كلمة المرور</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection