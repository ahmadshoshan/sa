<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاتورة {{ $invoice->invoice_no }}</title>

    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            width: 72mm;
            margin: 0 auto;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 12px;
            direction: rtl;
            color: #000;
        }

        .no-print {
            text-align: center;
            margin: 8px;
        }

        .no-print button {
            padding: 6px 12px;
            margin: 0 3px;
            cursor: pointer;
        }

        .text-center {
            text-align: center;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
        }

        .small {
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 1px 0;
            vertical-align: top;
        }

        .left {
            text-align: left;
        }

        .total-line {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ طباعة</button>
    <button onclick="window.close()">إغلاق</button>
</div>

<div class="text-center">
    <div class="company-name">
        {{ $settings['company_name'] ?? 'نظام المبيعات' }}
    </div>

    @if(!empty($settings['company_phone']))
        <div class="small">{{ $settings['company_phone'] }}</div>
    @endif

    @if(!empty($settings['company_address']))
        <div class="small">{{ $settings['company_address'] }}</div>
    @endif

    @if(!empty($settings['tax_number']))
        <div class="small">الرقم الضريبي: {{ $settings['tax_number'] }}</div>
    @endif
</div>

<div class="divider"></div>

<table>
    <tr>
        <td>رقم الفاتورة</td>
        <td class="left">{{ $invoice->invoice_no }}</td>
    </tr>

    <tr>
        <td>التاريخ</td>
        <td class="left">{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
    </tr>

    <tr>
        <td>العميل</td>
        <td class="left">{{ $invoice->customer?->name }}</td>
    </tr>
</table>

<div class="divider"></div>

<table>
    @foreach($invoice->items as $item)
        <tr>
            <td colspan="2">{{ $item->product?->name }}</td>
        </tr>

        <tr>
            <td>
                {{ number_format((float) $item->quantity, 2) }} ×
                {{ number_format((float) $item->price, 2) }}
            </td>
            <td class="left">{{ number_format((float) $item->total, 2) }}</td>
        </tr>
    @endforeach
</table>

<div class="divider"></div>

<table>
    <tr>
        <td>الإجمالي الفرعي</td>
        <td class="left">{{ number_format((float) $invoice->subtotal, 2) }}</td>
    </tr>

    <tr>
        <td>الخصم</td>
        <td class="left">{{ number_format((float) $invoice->discount, 2) }}</td>
    </tr>

    <tr>
        <td>الضريبة</td>
        <td class="left">{{ number_format((float) $invoice->tax, 2) }}</td>
    </tr>

    <tr class="total-line">
        <td>الإجمالي</td>
        <td class="left">{{ number_format((float) $invoice->total, 2) }} {{ $settings['currency'] ?? '' }}</td>
    </tr>

    <tr>
        <td>المدفوع</td>
        <td class="left">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
    </tr>

    <tr>
        <td>المتبقي</td>
        <td class="left">{{ number_format((float) $invoice->remaining_amount, 2) }}</td>
    </tr>
</table>

<div class="divider"></div>

<div class="text-center small">
    {{ $settings['invoice_footer'] ?? 'شكرًا لتعاملكم معنا' }}
</div>

</body>
</html>