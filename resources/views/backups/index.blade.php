@extends('layouts.app', ['title' => 'النسخ الاحتياطي'])

@section('content')
<div class="archon-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="archon-section-title mb-0">إدارة النسخ الاحتياطية</h3>

            <form method="POST" action="{{ route('backups.create') }}">
                @csrf
                <button class="btn btn-primary">إنشاء نسخة احتياطية جديدة</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>اسم الملف</th>
                        <th>الحجم</th>
                        <th>تاريخ التعديل</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                        <tr>
                            <td>{{ $file->getFilename() }}</td>
                            <td>{{ number_format($file->getSize() / 1024, 2) }} KB</td>
                            <td>{{ date('Y-m-d h:i A', $file->getMTime()) }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('backups.download', $file->getFilename()) }}" class="btn btn-sm btn-outline-primary">تحميل</a>

                                    <form method="POST" action="{{ route('backups.restore', $file->getFilename()) }}" onsubmit="return confirm('هل أنت متأكد من استرجاع هذه النسخة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">استرجاع</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="archon-empty">لا توجد نسخ احتياطية حتى الآن</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection