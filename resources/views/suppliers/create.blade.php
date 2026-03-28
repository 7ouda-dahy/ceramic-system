@extends('layouts.app', ['title' => 'إضافة مورد'])

@push('styles')
<style>
    .form-shell {
        max-width: 1100px;
        margin: 0 auto;
    }

    .form-hero {
        background: linear-gradient(135deg, var(--archon-primary-dark), var(--archon-primary));
        color: #fff;
        border-radius: 22px;
        padding: 26px 28px;
        box-shadow: var(--archon-shadow);
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }

    .form-hero::after {
        content: '';
        position: absolute;
        width: 180px;
        height: 180px;
        left: -35px;
        bottom: -70px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .form-hero h2 {
        margin: 0 0 8px;
        font-size: 1.8rem;
        font-weight: 800;
    }

    .form-hero p {
        margin: 0;
        color: rgba(255,255,255,.85);
        font-size: .98rem;
    }

    .supplier-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 20px;
    }

    .info-panel,
    .form-panel {
        background: #fff;
        border: 1px solid var(--archon-border);
        border-radius: 20px;
        box-shadow: var(--archon-shadow);
        overflow: hidden;
    }

    .panel-head {
        padding: 18px 22px;
        border-bottom: 1px solid var(--archon-border);
        background: #f8fbff;
    }

    .panel-head h3 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--archon-primary-dark);
    }

    .panel-body {
        padding: 22px;
    }

    .hint-list {
        display: grid;
        gap: 14px;
    }

    .hint-item {
        border: 1px solid #e9edf5;
        border-radius: 16px;
        padding: 14px 16px;
        background: #fbfdff;
    }

    .hint-item .title {
        font-weight: 800;
        color: var(--archon-text);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hint-item .desc {
        color: var(--archon-muted);
        font-size: .92rem;
        line-height: 1.7;
    }

    .supplier-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .field-card {
        border: 1px solid #edf1f7;
        border-radius: 16px;
        padding: 16px;
        background: #fff;
    }

    .field-card.full {
        grid-column: 1 / -1;
    }

    .field-label {
        font-size: .95rem;
        font-weight: 800;
        margin-bottom: 10px;
        color: var(--archon-text);
        display: block;
    }

    .field-note {
        margin-top: 8px;
        font-size: .84rem;
        color: var(--archon-muted);
    }

    .actions-bar {
        margin-top: 22px;
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .btn-save {
        min-width: 180px;
        min-height: 48px;
        border-radius: 14px;
        font-weight: 800;
    }

    .btn-cancel {
        min-width: 130px;
        min-height: 48px;
        border-radius: 14px;
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .supplier-layout {
            grid-template-columns: 1fr;
        }

        .supplier-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="form-shell">
    <div class="form-hero">
        <h2>إضافة مورد جديد</h2>
        <p>سجل بيانات المورد الأساسية لربطه بفواتير الشراء والذمم وسداد الموردين داخل النظام.</p>
    </div>

    <div class="supplier-layout">
        <div class="info-panel">
            <div class="panel-head">
                <h3>إرشادات سريعة</h3>
            </div>
            <div class="panel-body">
                <div class="hint-list">
                    <div class="hint-item">
                        <div class="title">
                            <i class="bi bi-person-vcard"></i>
                            اسم المورد
                        </div>
                        <div class="desc">
                            استخدم الاسم التجاري أو الاسم المعروف في فواتير الشراء لتسهيل البحث والرجوع لاحقًا.
                        </div>
                    </div>

                    <div class="hint-item">
                        <div class="title">
                            <i class="bi bi-telephone"></i>
                            رقم الهاتف
                        </div>
                        <div class="desc">
                            يفضل إدخال رقم الهاتف الأساسي للمورد أو المسؤول عنه لتسهيل التواصل.
                        </div>
                    </div>

                    <div class="hint-item">
                        <div class="title">
                            <i class="bi bi-geo-alt"></i>
                            العنوان
                        </div>
                        <div class="desc">
                            العنوان اختياري لكنه مفيد في الطباعة والمراجعة والمتابعة لاحقًا.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-panel">
            <div class="panel-head">
                <h3>بيانات المورد</h3>
            </div>

            <div class="panel-body">
                <form method="POST" action="{{ route('suppliers.store') }}">
                    @csrf

                    <div class="supplier-form-grid">
                        <div class="field-card">
                            <label class="field-label">اسم المورد</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                placeholder="مثال: شركة مايوركا / محمد نجم"
                                required
                            >
                            <div class="field-note">هذا الحقل إجباري.</div>
                        </div>

                        <div class="field-card">
                            <label class="field-label">رقم الهاتف</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="{{ old('phone') }}"
                                placeholder="أدخل رقم الهاتف"
                            >
                            <div class="field-note">يمكن تركه فارغًا إذا لم يتوفر.</div>
                        </div>

                        <div class="field-card full">
                            <label class="field-label">العنوان</label>
                            <textarea
                                name="address"
                                class="form-control"
                                rows="5"
                                placeholder="أدخل عنوان المورد إن وجد"
                            >{{ old('address') }}</textarea>
                            <div class="field-note">العنوان اختياري.</div>
                        </div>
                    </div>

                    <div class="actions-bar">
                        <button type="submit" class="btn btn-primary btn-save">حفظ المورد</button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light border btn-cancel">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection