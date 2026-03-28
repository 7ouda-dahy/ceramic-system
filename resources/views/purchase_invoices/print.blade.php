<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة فاتورة الشراء</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            margin: 20px;
            color: #111;
        }

        .header {
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .sub {
            color: #555;
            margin-bottom: 4px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #bbb;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #143d91;
            color: white;
        }

        .mt {
            margin-top: 20px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="title">Archon</div>
        <div class="sub">فاتورة شراء</div>
    </div>

    <div class="box">
        <div><strong>رقم الفاتورة:</strong> #{{ $invoice->id }}</div>
        <div><strong>المورد:</strong> {{ $invoice->supplier_name }}</div>
        <div><strong>الهاتف:</strong> {{ $invoice->supplier_phone ?: '-' }}</div>
        <div><strong>مرجع فاتورة المورد:</strong> {{ $invoice->supplier_invoice_reference ?: '-' }}</div>
        <div><strong>الخزنة المستخدمة:</strong> {{ $centralCashbox?->name ?? 'الخزنة المركزية' }}</div>
        <div><strong>الإجمالي:</strong> {{ number_format($invoice->total_amount, 2) }} ج.م</div>
        <div><strong>المدفوع:</strong> {{ number_format($invoice->paid_amount, 2) }} ج.م</div>
        <div><strong>المتبقي:</strong> {{ number_format($invoice->remaining_amount, 2) }} ج.م</div>
        <div><strong>حالة السداد:</strong> {{ $invoice->payment_status }}</div>
        <div><strong>الملاحظات:</strong> {{ $invoice->notes ?: '—' }}</div>
        <div><strong>التاريخ:</strong> {{ $invoice->created_at?->format('Y-m-d h:i A') }}</div>
    </div>

    <table class="mt">
        <thead>
            <tr>
                <th>الصنف</th>
                <th>الكمية</th>
                <th>سعر الشراء</th>
                <th>الإجمالي</th>
                <th>ملاحظات الصنف</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->quantity_meter, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->line_total, 2) }}</td>
                    <td>{{ $item->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد أصناف</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>