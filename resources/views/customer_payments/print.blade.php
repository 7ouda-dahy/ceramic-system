<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سند سداد عميل</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            margin: 14px;
            direction: rtl;
            color: #000;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .brand {
            text-align: right;
        }

        .brand .title {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .brand .sub {
            font-size: 18px;
        }

        .doc-title {
            text-align: left;
            font-size: 26px;
            font-weight: 700;
            margin-top: 8px;
        }

        .box {
            border: 1px solid #cfcfcf;
            padding: 12px 14px;
            margin-bottom: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
            font-size: 15px;
        }

        .highlight {
            font-weight: 700;
            color: #143d91;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background: #143d91;
            color: #fff;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body onload="window.print()">
<div class="container">

    <div class="header">
        <div class="brand">
            <div class="title">Archon</div>
            <div class="sub">نظام إدارة السيراميك</div>
        </div>

        <div class="doc-title">
            سند سداد عميل
            <div style="font-size:16px; font-weight:400; margin-top:6px;">
                رقم السند: {{ $payment->reference_code }}
            </div>
        </div>
    </div>

    <div class="box">
        <div class="info-grid">
            <div><strong>اسم العميل:</strong> {{ $payment->customer?->name }}</div>
            <div><strong>التاريخ:</strong> {{ $payment->created_at?->format('Y-m-d h:i A') }}</div>

            <div class="highlight"><strong>إجمالي السداد:</strong> {{ number_format($payment->total_amount, 2) }} ج.م</div>
            <div class="highlight"><strong>المتبقي على العميل بعد السداد:</strong> {{ number_format($customerRemainingAfter, 2) }} ج.م</div>

            <div><strong>إجمالي مديونية العميل قبل السداد:</strong> {{ number_format($customerDueBefore, 2) }} ج.م</div>
            <div><strong>الملاحظات:</strong> {{ $payment->notes ?: '—' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>رقم الفاتورة</th>
                <th>الخزنة</th>
                <th>قبل السداد</th>
                <th>مبلغ السداد</th>
                <th>بعد السداد</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payment->items as $item)
                <tr>
                    <td>#{{ $item->invoice_id }}</td>
                    <td>{{ $item->cashbox?->name ?? '-' }}</td>
                    <td>{{ number_format((float) $item->remaining_before, 2) }} ج.م</td>
                    <td>{{ number_format((float) $item->amount, 2) }} ج.م</td>
                    <td>{{ number_format((float) $item->remaining_after, 2) }} ج.م</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد بنود</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        تم الإنشاء بواسطة Archon ERP
    </div>

</div>
</body>
</html>