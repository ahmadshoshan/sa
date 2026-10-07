<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>سند {{ $payment->type === 'receipt' ? 'قبض' : 'صرف' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 14px; color: #333; line-height: 1.8; padding: 30px; }
        .header { text-align: center; border-bottom: 3px double #198754; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #198754; font-size: 28px; }
        .header h2 { font-size: 20px; color: #666; margin-top: 5px; }
        .content { background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        .row { display: flex; margin-bottom: 15px; }
        .label { font-weight: bold; width: 150px; color: #555; }
        .value { flex: 1; padding: 8px 15px; background: white; border-radius: 5px; border: 1px solid #ddd; }
        .amount { font-size: 28px; font-weight: bold; color: #198754; text-align: center; padding: 20px; background: #d4edda; border-radius: 10px; margin: 20px 0; }
        .signatures { display: flex; justify-content: space-around; margin-top: 50px; padding-top: 20px; border-top: 2px solid #ddd; }
        .signature-box { text-align: center; min-width: 200px; }
        .signature-line { border-bottom: 2px solid #333; height: 50px; margin-bottom: 10px; }
        .footer { text-align: center; padding-top: 20px; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>سند {{ $payment->type === 'receipt' ? 'قبض' : 'صرف' }}</h2>
        <p>رقم: {{ $payment->payment_no }}</p>
    </div>

    <div class="content">
        <div class="row">
            <div class="label">التاريخ:</div>
            <div class="value">{{ $payment->payment_date }}</div>
        </div>
        
        <div class="row">
            <div class="label">استلمت من / دفعت إلى:</div>
            <div class="value">
                @if($payment->customer)
                    👤 السيد/ {{ $payment->customer->name }}
                @elseif($payment->supplier)
                    🏭 السيد/ {{ $payment->supplier->name }}
                @else
                    -
                @endif
            </div>
        </div>
        
        <div class="row">
            <div class="label">مبلغ وقدره:</div>
            <div class="value"><strong>{{ number_format($payment->amount, 2) }} {{ $currency }}</strong></div>
        </div>
        
        <div class="row">
            <div class="label">طريقة الدفع:</div>
            <div class="value">{{ $payment->payment_method }}</div>
        </div>
        
        @if($payment->account)
            <div class="row">
                <div class="label">الحساب:</div>
                <div class="value">{{ $payment->account->name }}</div>
            </div>
        @endif
        
        <div class="row">
            <div class="label">وذلك مقابل:</div>
            <div class="value">{{ $payment->notes ?? 'سداد مستحقات' }}</div>
        </div>
    </div>

    <div class="amount">
        {{ number_format($payment->amount, 2) }} {{ $currency }}
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>توقيع المستلم</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>توقيع المحاسب</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>توقيع المدير</div>
        </div>
    </div>

    <div class="footer">
        <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>