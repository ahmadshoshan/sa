<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طباعة مرتجع بيع {{ $invoice->invoice_no }}</title>

    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; margin: 0; padding: 0; color: #000; }
        .no-print { text-align: center; margin: 10px; }
        .no-print button { padding: 8px 16px; margin: 0 5px; cursor: pointer; }
        .invoice-paper { width: 190mm; margin: auto; }
        .text-center { text-align: center; }
        .company-name { font-size: 22px; font-weight: bold; }
        .company-info { font-size: 12px; color: #333; }
        .invoice-title { font-size: 18px; font-weight: bold; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
        th { background: #f2f2f2; }
        .info-table td { border: none; padding: 2px 0; font-size: 13px; }
        .totals-table { width: 70mm; margin-top: 10mm; }
        .totals-table td { border: none; padding: 3px 0; font-size: 13px; }
        .totals-table .grand-total { font-size: 15px; font-weight: bold; border-top: 1px solid #000; }
        .footer { margin-top: 25px; font-size: 12px; text-align: center; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ طباعة</button>
    <button onclick="window.close()">إغلاق</button>
</div>

<div class="invoice-paper">

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

        <div class="invoice-title">
            مرتجع بيع
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>رقم المرتجع:</strong> {{ $invoice->invoice_no }}</td>
            <td><strong>التاريخ:</strong> {{ $invoice->invoice_date?->format('Y-m-d') }}</td>
        </tr>

        <tr>
            <td><strong>العميل:</strong> {{ $invoice->customer?->name }}</td>
            <td><strong>الهاتف:</strong> {{ $invoice->customer?->phone ?? '-' }}</td>
        </tr>

        <tr>
            <td><strong>المخزن:</strong> {{ $invoice->warehouse?->name }}</td>
            <td></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الصنف</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الخصم</th>
                <th>الضريبة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>

        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                    <td>{{ number_format((float) $item->price, 2) }}</td>
                    <td>{{ number_format((float) $item->discount, 2) }}</td>
                    <td>{{ number_format((float) $item->tax, 2) }}</td>
                    <td>{{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>الإجمالي:</td>
            <td>{{ number_format((float) $invoice->total, 2) }} {{ $settings['currency'] ?? '' }}</td>
        </tr>

        <tr>
            <td>المسترد:</td>
            <td>{{ number_format((float) $invoice->paid_amount, 2) }} {{ $settings['currency'] ?? '' }}</td>
        </tr>

        <tr>
            <td>المتبقي:</td>
            <td>{{ number_format((float) $invoice->remaining_amount, 2) }} {{ $settings['currency'] ?? '' }}</td>
        </tr>
    </table>

    @if($invoice->notes)
        <div style="margin-top: 15px; font-size: 12px;">
            <strong>ملاحظات:</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        {{ $settings['invoice_footer'] ?? 'شكرًا لتعاملكم معنا' }}
    </div>

</div>

</body>
</html>