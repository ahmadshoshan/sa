<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير المشتريات</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .summary-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-right: 4px solid #198754; }
        .summary-box h3 { color: #198754; font-size: 14px; margin-bottom: 5px; }
        .summary-box .value { font-size: 20px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #198754; color: white; padding: 10px 8px; text-align: right; font-size: 12px; }
        td { padding: 8px; border-bottom: 1px solid #e9ecef; font-size: 11px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-end { text-align: left; }
        .footer { text-align: center; padding: 20px; border-top: 2px solid #198754; margin-top: 20px; color: #6c757d; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛒 تقرير المشتريات</h1>
        <p>{{ $companyName }} - الفترة: {{ $from ?? 'البداية' }} إلى {{ $to ?? now()->format('Y-m-d') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <h3>عدد الفواتير</h3>
            <div class="value">{{ $invoices->count() }}</div>
        </div>
        <div class="summary-box">
            <h3>إجمالي المشتريات</h3>
            <div class="value">{{ number_format($total, 2) }}</div>
        </div>
        <div class="summary-box">
            <h3>المتبقي</h3>
            <div class="value text-danger">{{ number_format($remaining, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>رقم الفاتورة</th>
                <th>التاريخ</th>
                <th>المورد</th>
                <th class="text-end">الإجمالي</th>
                <th class="text-end">المدفوع</th>
                <th class="text-end">المتبقي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $index => $inv)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $inv->invoice_no }}</td>
                    <td>{{ $inv->invoice_date }}</td>
                    <td>{{ $inv->customer?->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($inv->total, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($inv->paid_amount, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($inv->remaining_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #198754; color: white;">
                <td colspan="4"><strong>الإجمالي</strong></td>
                <td class="text-end"><strong>{{ number_format($total, 2) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($paid, 2) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($remaining, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>