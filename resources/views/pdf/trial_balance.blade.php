<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>ميزان المراجعة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #198754; color: white; padding: 10px 8px; text-align: right; font-size: 12px; }
        td { padding: 8px; border-bottom: 1px solid #e9ecef; font-size: 11px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-end { text-align: left; }
        .total-row { background: #198754 !important; color: white; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .footer { text-align: center; padding: 20px; border-top: 2px solid #198754; margin-top: 20px; color: #6c757d; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚖️ ميزان المراجعة</h1>
        <p>{{ $companyName }} - حتى تاريخ: {{ $toDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>الكود</th>
                <th>اسم الحساب</th>
                <th>النوع</th>
                <th class="text-end">مدين</th>
                <th class="text-end">دائن</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['code'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td class="text-end">{{ number_format($row['debit'], 2) }}</td>
                    <td class="text-end">{{ number_format($row['credit'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3">الإجمالي</td>
                <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; padding: 15px; background: {{ $balanced ? '#d4edda' : '#f8d7da' }}; border-radius: 8px;">
        <strong>
            @if($balanced)
                ✅ ميزان المراجعة متوازن
            @else
                ⚠️ ميزان المراجعة غير متوازن - الفرق: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
            @endif
        </strong>
    </div>

    <div class="footer">
        <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>