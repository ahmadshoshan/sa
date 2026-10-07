<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>قائمة الدخل</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 14px; color: #333; line-height: 1.8; padding: 30px; }
        .header { text-align: center; border-bottom: 3px double #198754; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #198754; font-size: 24px; }
        .header p { color: #666; margin-top: 5px; }
        .content { background: #f8f9fa; padding: 25px; border-radius: 10px; }
        .row { display: flex; justify-content: space-between; padding: 12px 15px; border-bottom: 1px solid #e9ecef; }
        .row:last-child { border-bottom: none; }
        .row.total { background: #198754; color: white; font-size: 18px; font-weight: bold; margin-top: 20px; border-radius: 8px; padding: 15px; }
        .row.subtotal { background: #e7f3ff; font-weight: bold; }
        .label { font-weight: bold; }
        .value { font-weight: bold; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .footer { text-align: center; padding-top: 30px; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 قائمة الدخل</h1>
        <p>{{ $companyName }}</p>
        <p>الفترة من {{ $from }} إلى {{ $to }}</p>
    </div>

    <div class="content">
        <div class="row subtotal">
            <div class="label">الإيرادات</div>
            <div class="value text-success">{{ number_format($revenue, 2) }} {{ $currency }}</div>
        </div>
        
        <div class="row subtotal" style="background: #fff3cd;">
            <div class="label">المصروفات</div>
            <div class="value text-danger">{{ number_format($expense, 2) }} {{ $currency }}</div>
        </div>
        
        <div class="row total">
            <div class="label">صافي {{ $net >= 0 ? 'الربح' : 'الخسارة' }}</div>
            <div class="value">{{ number_format($net, 2) }} {{ $currency }}</div>
        </div>
    </div>

    <div class="footer">
        <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>