<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Archon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0080ff, #eaf4ff);
            font-family: Tahoma, Arial, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: 0;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15,23,42,.15);
        }
        .logo-title {
            color: #0080ff;
            font-weight: 800;
            font-size: 28px;
        }
    </style>
</head>
<body>
<div class="card login-card">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
            <div class="logo-title">Archon</div>
            <div class="text-muted">تسجيل الدخول إلى النظام</div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.perform') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">اسم المستخدم</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">دخول</button>
        </form>
    </div>
</div>
</body>
</html>