<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب - {{ $supplier->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; margin-bottom: 5px; }
        .info-section { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .info-section p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #198754; color: white; padding: 10px 8px; text-align: right; font-size: 12px; }
        td { padding: 8px; border-bottom: 1px solid #e9ecef; font-size: 11px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-end { text-align: left; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .summary { background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .footer { text-align: center; padding: 20px; border-top: 2px solid #198754; margin-top: 20px; color: #6c757d; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏭 كشف حساب مورد</h1>
        <p>{{ $companyName }}</p>
    </div>

    <div class="info-section">
        <p><strong>المورد:</strong> {{ $supplier->name }}</p>
        <p><strong>الكود:</strong> {{ $supplier->code }}</p>
        <p><strong>الهاتف:</strong> {{ $supplier->phone ?? '-' }}</p>
        <p><strong>الفترة:</strong> {{ $from ?? 'البداية' }} إلى {{ $to ?? now()->format('Y-m-d') }}</p>
    </div>

    <h3 style="margin-bottom: 10px; color: #198754;">📄 الفواتير والمدفوعات</h3>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>النوع</th>
                <th>الرقم</th>
                <th class="text-end">مدين</th>
                <th class="text-end">دائن</th>
                <th class="text-end">الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @php
                $all = collect();
                foreach ($invoices as $inv) {
                    $all->push([
                        'date' => $inv->invoice_date,
                        'type' => 'فاتورة شراء',
                        'no' => $inv->invoice_no,
                        'debit' => $inv->total,
                        'credit' => 0,
                    ]);
                }
                foreach ($payments as $pay) {
                    $all->push([
                        'date' => $pay->payment_date,
                        'type' => $pay->type === 'receipt' ? 'سند قبض' : 'سند صرف',
                        'no' => $pay->payment_no,
                        'debit' => $pay->type === 'payment' ? $pay->amount : 0,
                        'credit' => $pay->type === 'receipt' ? $pay->amount : 0,
                    ]);
                }
                $all = $all->sortBy('date');
                $runningBalance = $supplier->opening_balance;
            @endphp
            @foreach($all as $item)
                @php
                    $runningBalance += $item['debit'] - $item['credit'];
                @endphp
                <tr>
                    <td>{{ $item['date'] }}</td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['no'] }}</td>
                    <td class="text-end {{ $item['debit'] > 0 ? 'text-danger' : '' }}">{{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}</td>
                    <td class="text-end {{ $item['credit'] > 0 ? 'text-success' : '' }}">{{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}</td>
                    <td class="text-end"><strong>{{ number_format($runningBalance, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <strong>إجمالي المبيعات:</strong>
            <span class="text-danger">{{ number_format($invoices->sum('total'), 2) }} {{ $currency }}</span>
        </div>
        <div class="summary-row">
            <strong>إجمالي المدفوعات:</strong>
            <span class="text-success">{{ number_format($payments->where('type', 'receipt')->sum('amount'), 2) }} {{ $currency }}</span>
        </div>
        <div class="summary-row" style="border-top: 2px solid #0d6efd; padding-top: 10px; margin-top: 10px;">
            <strong>الرصيد الحالي:</strong>
            <strong>{{ number_format($supplier->current_balance, 2) }} {{ $currency }}</strong>
        </div>
    </div>

    <div class="footer">
        <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>