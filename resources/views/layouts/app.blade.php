<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Archon ERP' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/archon.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
<div class="app-shell">
    <aside class="archon-sidebar">
        <div class="archon-sidebar-brand">
            <div class="brand-title">Archon</div>
            <div class="brand-subtitle">Ceramic Management System</div>
        </div>

        <div class="archon-sidebar-nav">
            @php
                $currentRoute = request()->route()?->getName();
            @endphp

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-dashboard" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-speedometer2"></i> لوحة التحكم</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-dashboard">
                    <a href="{{ route('dashboard') }}" class="{{ $currentRoute === 'dashboard' ? 'active' : '' }}">الصفحة الرئيسية</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-products" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-box-seam"></i> الأصناف</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-products">
                    <a href="{{ route('products.index') }}" class="{{ $currentRoute === 'products.index' ? 'active' : '' }}">عرض الأصناف</a>
                    <a href="{{ route('products.create') }}" class="{{ $currentRoute === 'products.create' ? 'active' : '' }}">إضافة صنف</a>
                    <a href="{{ route('stock.create') }}" class="{{ $currentRoute === 'stock.create' ? 'active' : '' }}">إضافة مخزون</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-sales" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-receipt"></i> المبيعات</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-sales">
                    <a href="{{ route('invoices.index') }}" class="{{ $currentRoute === 'invoices.index' ? 'active' : '' }}">فواتير البيع</a>
                    <a href="{{ route('invoices.create') }}" class="{{ $currentRoute === 'invoices.create' ? 'active' : '' }}">فاتورة بيع جديدة</a>
                    <a href="{{ route('sales-returns.index') }}" class="{{ $currentRoute === 'sales-returns.index' ? 'active' : '' }}">مرتجعات البيع</a>
                    <a href="{{ route('sales-returns.create') }}" class="{{ $currentRoute === 'sales-returns.create' ? 'active' : '' }}">إنشاء مرتجع</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-purchases" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-cart3"></i> المشتريات</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-purchases">
                    <a href="{{ route('purchase-invoices.index') }}" class="{{ $currentRoute === 'purchase-invoices.index' ? 'active' : '' }}">فواتير الشراء</a>
                    <a href="{{ route('purchase-invoices.create') }}" class="{{ $currentRoute === 'purchase-invoices.create' ? 'active' : '' }}">فاتورة شراء جديدة</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-customers" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-people"></i> العملاء</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-customers">
                    <a href="{{ route('customers.index') }}" class="{{ $currentRoute === 'customers.index' ? 'active' : '' }}">العملاء والذمم</a>
                    <a href="{{ route('customers.create') }}" class="{{ $currentRoute === 'customers.create' ? 'active' : '' }}">إضافة عميل</a>
                    <a href="{{ route('customer-payments.create') }}" class="{{ $currentRoute === 'customer-payments.create' ? 'active' : '' }}">سداد عميل</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-suppliers" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-truck"></i> الموردون</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-suppliers">
                    <a href="{{ route('suppliers.index') }}" class="{{ $currentRoute === 'suppliers.index' ? 'active' : '' }}">الموردون والذمم</a>
                    <a href="{{ route('suppliers.create') }}" class="{{ $currentRoute === 'suppliers.create' ? 'active' : '' }}">إضافة مورد</a>
                    <a href="{{ route('supplier-payments.create') }}" class="{{ $currentRoute === 'supplier-payments.create' ? 'active' : '' }}">سداد مورد</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-cashbox" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-wallet2"></i> الخزنة</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-cashbox">
                    <a href="{{ route('cashbox.show', 'central') }}" class="{{ request()->is('cashbox/central') ? 'active' : '' }}">الخزنة المركزية</a>
                    <a href="{{ route('cashbox.show', 'beni-ebeid') }}" class="{{ request()->is('cashbox/beni-ebeid') ? 'active' : '' }}">خزنة بني عبيد</a>
                    <a href="{{ route('cashbox.show', 'fikriya') }}" class="{{ request()->is('cashbox/fikriya') ? 'active' : '' }}">خزنة الفكرية</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-users" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-person-gear"></i> المستخدمون</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-users">
                    <a href="{{ route('users.index') }}" class="{{ $currentRoute === 'users.index' ? 'active' : '' }}">عرض المستخدمين</a>
                    <a href="{{ route('users.create') }}" class="{{ $currentRoute === 'users.create' ? 'active' : '' }}">إضافة مستخدم</a>
                </div>
            </div>

            <div class="archon-sidebar-section">
                <button class="archon-sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#menu-system" aria-expanded="true">
                    <span class="label-wrap"><i class="bi bi-hdd-stack"></i> النظام</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show archon-submenu" id="menu-system">
                    <a href="{{ route('branches.index') }}" class="{{ $currentRoute === 'branches.index' ? 'active' : '' }}">الفروع والخزن</a>
                    <a href="{{ route('backups.index') }}" class="{{ $currentRoute === 'backups.index' ? 'active' : '' }}">النسخ الاحتياطي</a>
                    <a href="{{ route('profile.show') }}" class="{{ $currentRoute === 'profile.show' ? 'active' : '' }}">الملف الشخصي</a>
                </div>
            </div>
        </div>
    </aside>

    <div class="archon-main">
        <header class="archon-topbar">
            <div>
                <h1 class="archon-topbar-title">{{ $title ?? 'لوحة التحكم' }}</h1>
                <div class="archon-topbar-sub">نظام تشغيل ومتابعة المخزون والمبيعات والمشتريات</div>
            </div>

            <div class="archon-topbar-actions">
                <div class="archon-search-box">
                    <i class="bi bi-search"></i>
                    <input id="archonQuickSearch" type="text" placeholder="بحث سريع داخل النظام">
                </div>

                <div class="archon-user-chip">
                    <i class="bi bi-person-circle fs-4"></i>
                    <div>
                        <div class="name">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="role">{{ auth()->user()->username ?? 'admin' }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="archon-logout-btn" type="submit">خروج</button>
                </form>
            </div>
        </header>

        <main class="archon-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <div class="archon-footer-note">
                تم التصميم والتنفيذ بواسطة Archon
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/archon.js') }}"></script>
@stack('scripts')
</body>
</html>