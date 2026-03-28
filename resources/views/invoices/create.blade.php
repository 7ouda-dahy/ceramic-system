@extends('layouts.app', ['title' => 'فاتورة بيع جديدة'])

@section('content')
<div class="container-fluid px-0">

    <div class="page-header-card mb-4">
        <div>
            <h2 class="page-header-title mb-1">فاتورة بيع جديدة</h2>
            <div class="page-header-subtitle">إنشاء فاتورة بيع وربطها بالفرع والعميل والأصناف والسداد</div>
        </div>

        <div class="page-header-actions">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-archon">
                <i class="bi bi-arrow-right-circle ms-1"></i>
                رجوع إلى الفواتير
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
        @csrf

        <div class="page-card mb-4">
            <div class="card-head-custom">
                <div class="card-head-icon">
                    <i class="bi bi-person-vcard"></i>
                </div>
                <div>
                    <h5 class="card-head-title mb-1">بيانات الفاتورة والعميل</h5>
                    <div class="card-head-subtitle">حدد الفرع وأدخل بيانات العميل الأساسية</div>
                </div>
            </div>

            <div class="card-body p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">الفرع</label>
                        <select name="branch_id" class="form-select form-control-archon" required>
                            <option value="">اختر الفرع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">اسم العميل</label>
                        <input type="text" name="customer_name" class="form-control form-control-archon" value="{{ old('customer_name') }}">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">الهاتف</label>
                        <input type="text" name="customer_phone" class="form-control form-control-archon" value="{{ old('customer_phone') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="page-card mb-4">
            <div class="card-head-custom">
                <div class="card-head-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <h5 class="card-head-title mb-1">الأصناف</h5>
                    <div class="card-head-subtitle">أضف صنفًا أو أكثر داخل نفس الفاتورة</div>
                </div>
            </div>

            <div class="card-body p-4 p-lg-5">
                <div id="items-wrapper">
                    <div class="invoice-item-row">
                        <div class="row g-3 align-items-end item-row-card">
                            <div class="col-lg-4">
                                <label class="form-label form-label-archon">الصنف</label>
                                <select name="product_id[]" class="form-select form-control-archon product-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            data-qty="{{ $product->quantity_meter }}"
                                            data-price="{{ $product->sale_price }}">
                                            {{ $product->full_name }} | المتاح: {{ number_format($product->quantity_meter, 2) }} م
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label form-label-archon">المتاح</label>
                                <input type="text" class="form-control form-control-archon available-qty" readonly>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label form-label-archon">الكمية</label>
                                <input type="number" step="0.01" name="quantity[]" class="form-control form-control-archon quantity-input" required>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label form-label-archon">سعر البيع</label>
                                <input type="number" step="0.01" name="sale_price[]" class="form-control form-control-archon price-input" required>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label form-label-archon">الإجمالي</label>
                                <input type="text" class="form-control form-control-archon line-total" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn btn-light btn-add-row" id="addItemRow">
                        <i class="bi bi-plus-circle ms-1"></i>
                        إضافة صنف
                    </button>
                </div>
            </div>
        </div>

        <div class="page-card">
            <div class="card-head-custom">
                <div class="card-head-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h5 class="card-head-title mb-1">الحساب والسداد</h5>
                    <div class="card-head-subtitle">راجع القيم النهائية وحدد طريقة السداد</div>
                </div>
            </div>

            <div class="card-body p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-lg-3">
                        <label class="form-label form-label-archon">الإجمالي قبل الخصم</label>
                        <input type="text" name="subtotal_preview" id="subtotalPreview" class="form-control form-control-archon total-preview-box" value="0.00" readonly>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label form-label-archon">الخصم</label>
                        <input type="number" step="0.01" name="discount_value" id="discountValue" class="form-control form-control-archon" value="{{ old('discount_value', 0) }}">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label form-label-archon">سبب الخصم</label>
                        <input type="text" name="discount_reason" class="form-control form-control-archon" value="{{ old('discount_reason') }}">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label form-label-archon">الإجمالي بعد الخصم</label>
                        <input type="text" name="total_preview" id="totalPreview" class="form-control form-control-archon total-preview-box" value="0.00" readonly>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">طريقة السداد</label>
                        <select name="payment_mode" id="paymentMode" class="form-select form-control-archon">
                            <option value="immediate" {{ old('payment_mode') == 'immediate' ? 'selected' : '' }}>فوري</option>
                            <option value="credit" {{ old('payment_mode') == 'credit' ? 'selected' : '' }}>أجل</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label form-label-archon">المدفوع / المقدم</label>
                        <input type="number" step="0.01" name="paid_amount" id="paidAmount" class="form-control form-control-archon" value="{{ old('paid_amount', 0) }}">
                    </div>
                </div>

                <div class="quick-note-box mt-4">
                    <div class="quick-note-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        عند اختيار <strong>فوري</strong> يجب أن يساوي المدفوع إجمالي الفاتورة بعد الخصم طبقًا للوجيك الحالي داخل النظام.
                    </div>
                </div>

                <div class="form-actions-bar mt-4">
                    <a href="{{ route('invoices.index') }}" class="btn btn-light btn-action-secondary">
                        <i class="bi bi-arrow-counterclockwise ms-1"></i>
                        رجوع
                    </a>

                    <button type="submit" class="btn btn-primary btn-action-primary">
                        <i class="bi bi-check2-square ms-1"></i>
                        حفظ فاتورة البيع
                    </button>
                </div>
            </div>
        </div>
    </form>

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

    .form-control-archon:focus,
    .form-select.form-control-archon:focus{
        background: #fff;
        border-color: var(--archon-primary-light) !important;
        box-shadow: 0 0 0 4px rgba(29,79,184,.10) !important;
    }

    .item-row-card{
        background:#fbfdff;
        border:1px solid #e5edf7;
        border-radius:18px;
        padding:18px 12px;
        margin-bottom:14px;
    }

    .btn-add-row{
        min-height:46px;
        border-radius:14px;
        font-weight:700;
        border:1px dashed #c4d2e6;
    }

    .total-preview-box{
        background:#f0f6ff !important;
        font-weight:800;
        color:var(--archon-primary-dark);
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

@push('scripts')
<script>
    function updateRow(row) {
        const product = row.querySelector('.product-select');
        const availableInput = row.querySelector('.available-qty');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const lineTotalInput = row.querySelector('.line-total');

        const selectedOption = product.options[product.selectedIndex];
        const availableQty = parseFloat(selectedOption?.dataset?.qty || 0);
        const salePrice = parseFloat(selectedOption?.dataset?.price || 0);
        const quantity = parseFloat(quantityInput.value || 0);
        const price = parseFloat(priceInput.value || salePrice || 0);

        if (product.value) {
            availableInput.value = availableQty.toFixed(2) + ' م';
            if (!priceInput.value) {
                priceInput.value = salePrice.toFixed(2);
            }
        } else {
            availableInput.value = '';
        }

        lineTotalInput.value = (quantity * price).toFixed(2);
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;

        document.querySelectorAll('.invoice-item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value || 0);
            const price = parseFloat(row.querySelector('.price-input').value || 0);
            subtotal += quantity * price;
        });

        const discount = parseFloat(document.getElementById('discountValue').value || 0);
        const finalTotal = Math.max(subtotal - discount, 0);

        document.getElementById('subtotalPreview').value = subtotal.toFixed(2);
        document.getElementById('totalPreview').value = finalTotal.toFixed(2);
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            updateRow(e.target.closest('.invoice-item-row'));
        }
    });

    document.addEventListener('input', function(e) {
        if (
            e.target.classList.contains('quantity-input') ||
            e.target.classList.contains('price-input') ||
            e.target.id === 'discountValue'
        ) {
            const row = e.target.closest('.invoice-item-row');
            if (row) {
                updateRow(row);
            } else {
                updateTotals();
            }
        }
    });

    document.getElementById('addItemRow').addEventListener('click', function() {
        const wrapper = document.getElementById('items-wrapper');
        const firstRow = wrapper.querySelector('.invoice-item-row');
        const clone = firstRow.cloneNode(true);

        clone.querySelectorAll('input').forEach(input => input.value = '');
        clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

        const rowCard = clone.querySelector('.item-row-card');
        if (!clone.querySelector('.remove-row-btn')) {
            const removeCol = document.createElement('div');
            removeCol.className = 'col-lg-12 text-start';

            removeCol.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">
                    <i class="bi bi-trash ms-1"></i>
                    حذف الصنف
                </button>
            `;

            rowCard.appendChild(removeCol);
        }

        wrapper.appendChild(clone);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row-btn')) {
            const rows = document.querySelectorAll('.invoice-item-row');
            if (rows.length > 1) {
                e.target.closest('.invoice-item-row').remove();
                updateTotals();
            }
        }
    });

    document.querySelectorAll('.invoice-item-row').forEach(updateRow);
    updateTotals();
</script>
@endpush