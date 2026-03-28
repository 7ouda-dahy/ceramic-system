@extends('layouts.app', ['title' => 'الملف الشخصي'])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4"><strong>الاسم:</strong> {{ auth()->user()->name }}</div>
            <div class="col-md-4"><strong>اسم المستخدم:</strong> {{ auth()->user()->username }}</div>
            <div class="col-md-4"><strong>الفرع:</strong> {{ auth()->user()->branch->name ?? 'غير مرتبط بفرع' }}</div>
        </div>
    </div>
</div>
@endsection