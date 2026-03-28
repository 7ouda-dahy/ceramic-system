@extends('layouts.app', ['title' => 'مرتجع بيع'])

@section('content')
<div class="page-card">
    <div class="card-body">
        <form method="GET" action="{{ route('sales-returns.create') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold">اختر الفاتورة</label>
                    <select name="invoice_id" class="form-select" required>
                        <option value="">اختر فاتورة البيع</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}" {{ request('invoice_id') == $inv->id ? 'selected' : '' }}>
                                #{{ $inv->id }} - {{ $inv->customer_name ?? 'بدون اسم عميل' }} - {{ number_format($inv->total_amount ?? 0, 2) }} ج.م
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">عرض الفاتورة</button>
                </div>
            </div>
        </form>

        @if($selectedInvoice)
            <form method="POST" action="{{ route('sales-returns.store') }}">
                @csrf

                <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">رقم الفاتورة</label>
                        <div class="form-control bg-light">#{{ $selectedInvoice->id }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">العميل</label>
                        <div class="form-control bg-light">{{ $selectedInvoice->customer_name ?? '-' }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">تاريخ الفاتورة</label>
                        <div class="form-control bg-light">{{ $selectedInvoice->created_at?->format('Y-m-d h:i A') }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">إجمالي الفاتورة الحالي</label>
                        <div class="form-control bg-light">{{ number_format((float) ($selectedInvoice->total_amount ?? 0), 2) }} ج.م</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">المتبقي</label>
                        <div class="form-control bg-light">{{ number_format((float) ($selectedInvoice->remaining_amount ?? 0), 2) }} ج.م</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">الأيام منذ الفاتورة</label>
                        <div class="form-control bg-light">
                            {{ $daysPassed ?? 0 }} يوم
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">حالة السداد</label>
                        <div class="form-control bg-light">
                            @php
                                $statusAr = match($selectedInvoice->payment_status ?? '') {
                                    'paid' => 'مدفوعة',
                                    'partial' => 'مدفوعة جزئيًا',
                                    'due' => 'آجلة',
                                    default => $selectedInvoice->payment_status ?? '-'
                                };
                            @endphp
                            {{ $statusAr }}
                        </div>
                    </div>
                </div>

                @if($warning)
                    <div class="alert alert-warning mb-4">
                        {{ $warning }}
                    </div>
                @endif

                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center align-middle" id="returnsTable">
                        <thead>
                            <tr>
                                <th>الصنف</th>
                                <th>الكمية المباعة</th>
                                <th>تم إرجاعه سابقًا</th>
                                <th>المتاح للمرتجع</th>
                                <th>سعر البيع</th>
                                <th>كمية المرتجع</th>
                                <th>قيمة المرتجع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td class="text-end">{{ $item->product_name }}</td>
                                    <td>{{ number_format((float) $item->sold_quantity, 2) }}</td>
                                    <td>{{ number_format((float) $item->already_returned, 2) }}</td>
                                    <td class="available-qty">{{ number_format((float) $item->available, 2) }}</td>
                                    <td class="sale-price">{{ number_format((float) $item->price, 2) }}</td>
                                    <td>
                                        <input type="hidden" name="invoice_item_id[]" value="{{ $item->invoice_item_id }}">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="{{ (float) $item->available }}"
                                            name="return_quantity[]"
                                            class="form-control return-qty-input"
                                            value="0"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            class="form-control line-total-input"
                                            value="0.00"
                                            readonly
                                        >
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">لا توجد أصناف متاحة للمرتجع في هذه الفاتورة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->count())
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">سبب المرتجع</label>
                            <select name="reason" class="form-select" required>
                                <option value="">اختر السبب</option>
                                <option value="كسر">كسر</option>
                                <option value="عيب صناعة">عيب صناعة</option>
                                <option value="مقاس غير مناسب">مقاس غير مناسب</option>
                                <option value="لون غير مناسب">لون غير مناسب</option>
                                <option value="أخرى">أخرى</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">ملاحظات</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                        </div>
                    </div>

                    <div class="page-card mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2 fs-5">
                                <div><strong>إجمالي المرتجع:</strong> <span id="totalReturnAmount">0.00</span> ج.م</div>
                                <div><strong>سيتم خصمه من المديونية:</strong> <span id="deductFromDue">0.00</span> ج.م</div>
                                <div><strong>سيتم رده نقدًا:</strong> <span id="cashRefund">0.00</span> ج.م</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">حفظ المرتجع</button>
                @endif
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const remainingAmount = parseFloat(@json((float) ($selectedInvoice->remaining_amount ?? 0))) || 0;

    function recalcReturnTable() {
        let totalReturn = 0;

        document.querySelectorAll('#returnsTable tbody tr').forEach(function (row) {
            const qtyInput = row.querySelector('.return-qty-input');
            const lineTotalInput = row.querySelector('.line-total-input');
            const priceCell = row.querySelector('.sale-price');
            const availableCell = row.querySelector('.available-qty');

            if (!qtyInput || !lineTotalInput || !priceCell || !availableCell) {
                return;
            }

            const price = parseFloat((priceCell.textContent || '0').replace(/,/g, '').trim()) || 0;
            const availableQty = parseFloat((availableCell.textContent || '0').replace(/,/g, '').trim()) || 0;
            let qty = parseFloat(qtyInput.value || 0) || 0;

            if (qty < 0) qty = 0;

            if (qty > availableQty) {
                qty = availableQty;
                qtyInput.value = availableQty.toFixed(2);
            }

            const lineTotal = qty * price;
            lineTotalInput.value = lineTotal.toFixed(2);
            totalReturn += lineTotal;
        });

        const deductFromDue = Math.min(totalReturn, remainingAmount);
        const cashRefund = Math.max(0, totalReturn - remainingAmount);

        const totalReturnAmountEl = document.getElementById('totalReturnAmount');
        const deductFromDueEl = document.getElementById('deductFromDue');
        const cashRefundEl = document.getElementById('cashRefund');

        if (totalReturnAmountEl) totalReturnAmountEl.textContent = totalReturn.toFixed(2);
        if (deductFromDueEl) deductFromDueEl.textContent = deductFromDue.toFixed(2);
        if (cashRefundEl) cashRefundEl.textContent = cashRefund.toFixed(2);
    }

    document.querySelectorAll('.return-qty-input').forEach(function (input) {
        input.addEventListener('input', recalcReturnTable);
        input.addEventListener('change', recalcReturnTable);
        input.addEventListener('keyup', recalcReturnTable);
    });

    recalcReturnTable();
});
</script>
@endpush