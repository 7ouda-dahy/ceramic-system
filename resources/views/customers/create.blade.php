@extends('layouts.app', ['title' => 'إضافة عميل'])

@section('content')
<div class="container-fluid px-0">

    <div class="page-header-card mb-4">
        <div>
            <h2 class="page-header-title mb-1">إضافة عميل</h2>
            <div class="page-header-subtitle">تسجيل عميل جديد داخل النظام لتتبعه في الفواتير والذمم</div>
        </div>

        <div class="page-header-actions">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-archon">
                <i class="bi bi-arrow-right-circle ms-1"></i>
                رجوع إلى العملاء
            </a>
        </div>
    </div>

    <div class="page-card">
        <div class="card-head-custom">
            <div class="card-head-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <div>
                <h5 class="card-head-title mb-1">بيانات العميل</h5>
                <div class="card-head-subtitle">أدخل البيانات الأساسية للعميل بشكل واضح ودقيق</div>
            </div>
        </div>

        <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">اسم العميل</label>
                        <input type="text" name="name" class="form-control form-control-archon" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control form-control-archon" value="{{ old('phone') }}">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">العنوان</label>
                        <input type="text" name="address" class="form-control form-control-archon" value="{{ old('address') }}">
                    </div>
                </div>

                <div class="quick-note-box mt-4">
                    <div class="quick-note-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        يفضل تسجيل <strong>الاسم ورقم الهاتف</strong> بنفس الصيغة المعتمدة لديك لتجنب تكرار العملاء أثناء إنشاء الفواتير.
                    </div>
                </div>

                <div class="form-actions-bar mt-4">
                    <a href="{{ route('customers.index') }}" class="btn btn-light btn-action-secondary">
                        <i class="bi bi-arrow-counterclockwise ms-1"></i>
                        رجوع
                    </a>

                    <button type="submit" class="btn btn-primary btn-action-primary">
                        <i class="bi bi-check2-square ms-1"></i>
                        حفظ العميل
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .page-header-card{
        background: linear-gradient(135deg, #ffffff 0%, #f7faff 100%);
        border: 1px solid var(--archon-border);
        border-radius: 22px;
        box-shadow: var(--archon-shadow);
        padding: 24px 26px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:20px;
        flex-wrap:wrap;
    }

    .page-header-title{
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--archon-text);
    }

    .page-header-subtitle{
        color: var(--archon-muted);
        font-size: .97rem;
    }

    .btn-outline-archon{
        border:1px solid var(--archon-primary);
        color: var(--archon-primary);
        background:#fff;
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 700;
    }

    .btn-outline-archon:hover{
        background: var(--archon-primary);
        color:#fff;
    }

    .card-head-custom{
        display:flex;
        align-items:center;
        gap:16px;
        padding: 24px 28px;
        border-bottom:1px solid var(--archon-border);
        background: linear-gradient(90deg, rgba(20,61,145,.05), rgba(20,61,145,.02));
    }

    .card-head-icon{
        width:54px;
        height:54px;
        border-radius:16px;
        background: linear-gradient(135deg, var(--archon-primary), var(--archon-primary-dark));
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size: 1.35rem;
        flex-shrink:0;
        box-shadow: 0 10px 20px rgba(20,61,145,.22);
    }

    .card-head-title{
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--archon-text);
    }

    .card-head-subtitle{
        color: var(--archon-muted);
        font-size: .94rem;
    }

    .form-label-archon{
        font-weight: 800;
        color: #233246;
        margin-bottom: 10px;
        font-size: .96rem;
    }

    .form-control-archon{
        min-height: 54px;
        border-radius: 16px !important;
        border: 1px solid #d7e1ef !important;
        background: #fbfdff;
        padding: 12px 16px;
        font-size: 1rem;
        transition: .2s ease;
    }

    .form-control-archon:focus{
        background: #fff;
        border-color: var(--archon-primary-light) !important;
        box-shadow: 0 0 0 4px rgba(29,79,184,.10) !important;
    }

    .quick-note-box{
        display:flex;
        gap:14px;
        align-items:flex-start;
        background: #f7faff;
        border:1px dashed #cbd8ea;
        border-radius: 18px;
        padding: 16px 18px;
        color:#506176;
    }

    .quick-note-icon{
        width:38px;
        height:38px;
        border-radius:12px;
        background: rgba(20,61,145,.10);
        color: var(--archon-primary);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size: 1rem;
        flex-shrink:0;
    }

    .form-actions-bar{
        display:flex;
        justify-content:flex-end;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .btn-action-primary,
    .btn-action-secondary{
        min-width: 140px;
        min-height: 48px;
        border-radius: 14px;
        font-weight: 800;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }

    @media (max-width: 768px){
        .page-header-card,
        .card-head-custom{
            padding: 18px;
        }

        .page-header-title{
            font-size: 1.3rem;
        }

        .form-actions-bar{
            justify-content:stretch;
        }

        .btn-action-primary,
        .btn-action-secondary{
            width:100%;
        }
    }
</style>
@endpush