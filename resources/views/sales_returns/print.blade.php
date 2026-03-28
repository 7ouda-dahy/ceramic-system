<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة المرتجع</title>
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
        <div class="sub">سند مرتجع بيع</div>
    </div>

    <div class="box">
        <div><strong>رقم المرتجع:</strong> #{{ $salesReturn->id }}</div>
        <div><strong>رقم الفاتورة:</strong> #{{ $salesReturn->invoice_id }}</div>
        <div><strong>العميل:</strong> {{ $salesReturn->invoice?->customer_name }}</div>
        <div><strong>تاريخ المرتجع:</strong> {{ $salesReturn->created_at?->format('Y-m-d h:i A') }}</div>
        <div><strong>إجمالي المرتجع:</strong> {{ number_format($salesReturn->total_amount, 2) }} ج.م</div>
        <div><strong>المبلغ المرتد نقدًا:</strong> {{ number_format($salesReturn->refund_amount, 2) }} ج.م</div>
        <div><strong>الملاحظات:</strong> {{ $salesReturn->notes }}</div>
    </div>

    <table class="mt">
        <thead>
            <tr>
                <th>الصنف</th>
                <th>الكمية المرتجعة</th>
                <th>سعر البيع</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->returned_quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">لا توجد أصناف</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>