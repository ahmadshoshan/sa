<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طباعة سند {{ $payment->payment_no }}</title>

    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; margin: 0; padding: 0; color: #000; }
        .no-print { text-align: center; margin: 10px; }
        .no-print button { padding: 8px 16px; margin: 0 5px; cursor: pointer; }
        .voucher-paper { width: 170mm; margin: auto; }
        .text-center { text-align: center; }
        .company-name { font-size: 22px; font-weight: bold; }
        .company-info { font-size: 12px; color: #333; }
        .voucher-title { font-size: 20px; font-weight: bold; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { border: 1px solid #000; padding: 10px; font-size: 14px; }
        .label { width: 45mm; background: #f2f2f2; font-weight: bold; }
        .signatures { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature-box { width: 50mm; text-align: center; font-size: 13px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ طباعة</button>
    <button onclick="window.close()">إغلاق</button>
</div>

<div class="voucher-paper">

    <div class="text-center">
        <div class="company-name">
            {{ $settings['company_name'] ?? 'نظام المبيعات' }}
        </div>

        <div class="company-info">
            @if(!empty($settings['company_address']))
                {{ $settings['company_address'] }}<br>
            @endif

            @if(!empty($settings['company_phone']))
                هاتف: {{ $settings['company_phone'] }}<br>
            @endif

            @if(!empty($settings['tax_number']))
                الرقم الضريبي: {{ $settings['tax_number'] }}
            @endif
        </div>

        <div class="voucher-title">
            @if($payment->type === 'receipt')
                سند قبض
            @else
                سند صرف
            @endif
        </div>
    </div>

    <table>
        <tr>
            <td class="label">رقم السند</td>
            <td>{{ $payment->payment_no }}</td>
        </tr>

        <tr>
            <td class="label">التاريخ</td>
            <td>{{ $payment->payment_date?->format('Y-m-d') }}</td>
        </tr>

        <tr>
            <td class="label">النوع</td>
            <td>
                @if($payment->type === 'receipt')
                    قبض
                @else
                    صرف
                @endif
            </td>
        </tr>

        <tr>
            <td class="label">الطرف</td>
            <td>
                @if($payment->customer)
                    {{ $payment->customer->name }}
                @elseif($payment->supplier)
                    {{ $payment->supplier->name }}
                @else
                    -
                @endif
            </td>
        </tr>

        <tr>
            <td class="label">الفاتورة المرتبطة</td>
            <td>{{ $payment->invoice?->invoice_no ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">طريقة الدفع</td>
            <td>
                @php
                    $methods = [
                        'cash' => 'نقدي',
                        'card' => ' انستا/كاش',
                        'bank_transfer' => 'تحويل بنكي',
              
                    ];
                @endphp

                {{ $methods[$payment->payment_method] ?? $payment->payment_method }}
            </td>
        </tr>

        <tr>
            <td class="label">المبلغ</td>
            <td>
                {{ number_format((float) $payment->amount, 2) }}
                {{ $settings['currency'] ?? '' }}
            </td>
        </tr>

        <tr>
            <td class="label">البيان</td>
            <td>{{ $payment->notes ?? '-' }}</td>
        </tr>
    </table>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                المستلم
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                المحاسب
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                المدير المالي
            </div>
        </div>
    </div>

</div>

</body>
</html>