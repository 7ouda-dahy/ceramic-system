@extends('layouts.app', ['title' => 'فاتورة شراء جديدة'])

@push('styles')
<style>
    .invoice-page {
        display: grid;
        gap: 20px;
    }

    .purchase-card {
        background: #fff;
        border: 1px solid var(--archon-border);
        border-radius: 18px;
        box-shadow: var(--archon-shadow);
        overflow: hidden;
    }

    .purchase-card-header {
        padding: 16px 20px;
        background: linear-gradient(90deg, var(--archon-primary-dark), var(--archon-primary));
        color: #fff;
        font-weight: 800;
        font-size: 1rem;
    }

    .purchase-card-body {
        padding: 20px;
    }

    .purchase-alert {
        background: #eef5ff;
        border: 1px solid #cfe0ff;
        color: #143d91;
        border-radius: 14px;
        padding: 14px 16px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .supplier-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .items-table .form-control,
    .items-table .form-select {
        min-height: 42px;
        border-radius: 10px;
    }

    .items-table th {
        white-space: nowrap;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        align-items: end;
    }

    .summary-box {
        background: #f8fbff;
        border: 1px solid #dbe7f6;
        border-radius: 14px;
        padding: 14px 16px;
    }

    .summary-box .label {
        color: var(--archon-muted);
        font-size: .9rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .summary-box .value {
        color: var(--archon-primary-dark);
        font-size: 1.35rem;
        font-weight: 800;
    }

    .notes-box textarea {
        min-height: 110px;
        border-radius: 14px;
    }

    .invoice-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 18px;
    }

    .btn-add-row {
        border-radius: 12px;
        font-weight: 700;
    }

    .btn-save-invoice {
        min-width: 190px;
        border-radius: 12px;
        font-weight: 800;
        padding: 12px 20px;
    }

    .remove-row-btn {
        min-width: 100px;
        border-radius: 10px;
    }

    @media (max-width: 1200px) {
        .supplier-grid,
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .supplier-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .invoice-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-save-invoice {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="invoice-page">
    <form method="POST" action="{{ route('purchase-invoices.store') }}">
        @csrf

        <div class="purchase-card">
            <div class="purchase-card-header">بيانات فاتورة الشراء</div>
            <div class="purchase-card-body">
                <div class="purchase-alert">
                    سيتم السداد من: <strong>{{ $centralCashbox?->name ?? 'الخزنة المركزية' }}</strong>
                </div>

                <div class="supplier-grid">
                    <div>
                        <label class="form-label fw-bold">المورد</label>
                        <select id="supplierSelect" class="form-select">
                            <option value="">اختر المورد</option>
                            @foreach($suppliers as $supplier)
                                <option
                                    value="{{ $supplier->id }}"
                                    data-name="{{ $supplier->name }}"
                                    data-phone="{{ $supplier->phone }}"
                                >
                                    {{ $supplier->name }} {{ $supplier->phone ? '| ' . $supplier->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-bold">اسم المورد</label>
                        <input type="text" name="supplier_name" id="supplierName" class="form-control" value="{{ old('supplier_name') }}" required>
                    </div>

                    <div>
                        <label class="form-label fw-bold">الهاتف</label>
                        <input type="text" name="supplier_phone" id="supplierPhone" class="form-control" value="{{ old('supplier_phone') }}">
                    </div>

                    <div>
                        <label class="form-label fw-bold">مرجع فاتورة المورد</label>
                        <input type="text" name="supplier_invoice_reference" class="form-control" value="{{ old('supplier_invoice_reference') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="purchase-card">
            <div class="purchase-card-header">بنود الفاتورة</div>
            <div class="purchase-card-body">
                <div class="table-responsive">
                    <table class="table align-middle text-center items-table mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 280px;">الصنف</th>
                                <th style="min-width: 140px;">الكمية</th>
                                <th style="min-width: 160px;">سعر الشراء</th>
                                <th style="min-width: 170px;">الإجمالي</th>
                                <th style="min-width: 110px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-items-wrapper">
                            <tr class="purchase-row">
                                <td>
                                    <select name="product_id[]" class="form-select" required>
                                        <option value="">اختر الصنف</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->full_name }} | آخر شراء: {{ number_format($product->purchase_price ?? 0, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <input type="number" step="0.01" name="quantity[]" class="form-control purchase-qty" required>
                                </td>

                                <td>
                                    <input type="number" step="0.01" name="price[]" class="form-control purchase-price" required>
                                </td>

                                <td>
                                    <input type="text" class="form-control purchase-line-total" readonly>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-outline-danger remove-row-btn">حذف</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="invoice-actions">
                    <button type="button" class="btn btn-outline-primary btn-add-row" id="addPurchaseRowBtn">
                        <i class="bi bi-plus-circle ms-1"></i> إضافة صنف
                    </button>

                    <div class="text-muted fw-bold">يمكنك إضافة أكثر من صنف داخل نفس الفاتورة</div>
                </div>
            </div>
        </div>

        <div class="purchase-card">
            <div class="purchase-card-header">الملخص المالي</div>
            <div class="purchase-card-body">
                <div class="summary-grid mb-4">
                    <div class="summary-box">
                        <div class="label">إجمالي الفاتورة</div>
                        <div class="value" id="purchaseGrandTotalBox">0.00 ج.م</div>
                        <input type="hidden" id="purchaseGrandTotal">
                    </div>

                    <div>
                        <label class="form-label fw-bold">طريقة السداد</label>
                        <select name="payment_mode" id="purchasePaymentMode" class="form-select">
                            <option value="immediate">فوري</option>
                            <option value="credit">أجل / جزئي</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-bold">المدفوع / المقدم</label>
                        <input type="number" step="0.01" name="paid_amount" id="purchasePaidAmount" class="form-control" value="{{ old('paid_amount', 0) }}">
                    </div>

                    <div class="summary-box">
                        <div class="label">المتبقي</div>
                        <div class="value" id="purchaseRemainingBox">0.00 ج.م</div>
                        <input type="hidden" id="purchaseRemaining">
                    </div>
                </div>

                <div class="notes-box mb-4">
                    <label class="form-label fw-bold">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary btn-save-invoice" type="submit">
                        <i class="bi bi-check2-circle ms-1"></i> حفظ فاتورة الشراء
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function bindPurchaseRow(row) {
    const qtyInput = row.querySelector('.purchase-qty');
    const priceInput = row.querySelector('.purchase-price');
    const totalInput = row.querySelector('.purchase-line-total');
    const removeBtn = row.querySelector('.remove-row-btn');

    function recalcRow() {
        const qty = parseFloat(qtyInput.value || 0);
        const price = parseFloat(priceInput.value || 0);
        totalInput.value = (qty * price).toFixed(2);
        recalcPurchaseInvoice();
    }

    qtyInput.addEventListener('input', recalcRow);
    priceInput.addEventListener('input', recalcRow);

    removeBtn.addEventListener('click', function() {
        const rows = document.querySelectorAll('.purchase-row');
        if (rows.length > 1) {
            row.remove();
            recalcPurchaseInvoice();
        }
    });
}

function recalcPurchaseInvoice() {
    let total = 0;
    document.querySelectorAll('.purchase-line-total').forEach(input => {
        total += parseFloat(input.value || 0);
    });

    document.getElementById('purchaseGrandTotal').value = total.toFixed(2);
    document.getElementById('purchaseGrandTotalBox').innerText = total.toFixed(2) + ' ج.م';

    const mode = document.getElementById('purchasePaymentMode').value;
    const paidInput = document.getElementById('purchasePaidAmount');

    if (mode === 'immediate') {
        paidInput.value = total.toFixed(2);
        paidInput.readOnly = true;
    } else {
        paidInput.readOnly = false;
    }

    const paid = parseFloat(paidInput.value || 0);
    const remaining = Math.max(0, total - paid);

    document.getElementById('purchaseRemaining').value = remaining.toFixed(2);
    document.getElementById('purchaseRemainingBox').innerText = remaining.toFixed(2) + ' ج.م';
}

document.querySelectorAll('.purchase-row').forEach(bindPurchaseRow);

document.getElementById('purchasePaymentMode').addEventListener('change', recalcPurchaseInvoice);
document.getElementById('purchasePaidAmount').addEventListener('input', recalcPurchaseInvoice);

document.getElementById('addPurchaseRowBtn').addEventListener('click', function() {
    const firstRow = document.querySelector('.purchase-row');
    const clone = firstRow.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => input.value = '');
    clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

    document.getElementById('purchase-items-wrapper').appendChild(clone);
    bindPurchaseRow(clone);
    recalcPurchaseInvoice();
});

document.getElementById('supplierSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    document.getElementById('supplierName').value = option.getAttribute('data-name') || '';
    document.getElementById('supplierPhone').value = option.getAttribute('data-phone') || '';
});

recalcPurchaseInvoice();
</script>
@endpush