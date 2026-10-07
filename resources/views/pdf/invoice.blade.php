<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $invoice->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .info-box { flex: 1; }
        .info-box h3 { font-size: 14px; color: #198754; margin-bottom: 8px; border-bottom: 2px solid #198754; padding-bottom: 4px; }
        .info-box p { margin: 3px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #198754; color: white; padding: 10px 8px; text-align: right; font-size: 12px; }
        td { padding: 8px; border-bottom: 1px solid #e9ecef; font-size: 11px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .totals { width: 40%; margin-right: auto; margin-left: 0; }
        .totals table { background: #f8f9fa; }
        .totals td { padding: 8px 12px; }
        .totals .grand-total { background: #198754; color: white; font-size: 14px; font-weight: bold; }
        .footer { text-align: center; padding: 20px; border-top: 2px solid #198754; margin-top: 20px; }
        .footer p { color: #6c757d; font-size: 11px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>
            @if($invoice->type === 'sale')
                🧾 فاتورة بيع رقم: {{ $invoice->invoice_no }}
            @elseif($invoice->type === 'purchase')
                🛒 فاتورة شراء رقم: {{ $invoice->invoice_no }}
            @elseif($invoice->type === 'sale_return')
                ↩️ مرتجع بيع رقم: {{ $invoice->invoice_no }}
            @else
                ↪️ مرتجع شراء رقم: {{ $invoice->invoice_no }}
            @endif
        </p>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>📋 بيانات الفاتورة</h3>
            <p><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_no }}</p>
            <p><strong>التاريخ:</strong> {{ $invoice->invoice_date }}</p>
            <p><strong>المخزن:</strong> {{ $invoice->warehouse?->name ?? '-' }}</p>
            <p><strong>الحالة:</strong> 
                <span class="badge {{ $invoice->remaining_amount == 0 ? 'badge-success' : 'badge-warning' }}">
                    {{ $invoice->remaining_amount == 0 ? 'مدفوعة' : 'مستحقة' }}
                </span>
            </p>
        </div>
        <div class="info-box">
            <h3>{{ $invoice->type === 'purchase' ? '🏭 المورد' : '👤 العميل' }}</h3>
            @if($invoice->type === 'purchase')
                <p><strong>الاسم:</strong> {{ $invoice->supplier?->name ?? '-' }}</p>
                <p><strong>الكود:</strong> {{ $invoice->supplier?->code ?? '-' }}</p>
                <p><strong>الهاتف:</strong> {{ $invoice->supplier?->phone ?? '-' }}</p>
            @else
                <p><strong>الاسم:</strong> {{ $invoice->customer?->name ?? '-' }}</p>
                <p><strong>الكود:</strong> {{ $invoice->customer?->code ?? '-' }}</p>
                <p><strong>الهاتف:</strong> {{ $invoice->customer?->phone ?? '-' }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الصنف</th>
                <th>الكود</th>
                <th class="text-center">الكمية</th>
                <th class="text-end">السعر</th>
                <th class="text-end">الخصم</th>
                <th class="text-end">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product?->name ?? '-' }}</td>
                    <td>{{ $item->product?->code ?? '-' }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($item->price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($item->total, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>الإجمالي قبل الضريبة:</td>
                <td class="text-end">{{ number_format($invoice->subtotal, 2) }} {{ $currency }}</td>
            </tr>
            @if($invoice->discount > 0)
                <tr>
                    <td>الخصم:</td>
                    <td class="text-end text-danger">- {{ number_format($invoice->discount, 2) }} {{ $currency }}</td>
                </tr>
            @endif
            @if($invoice->tax > 0)
                <tr>
                    <td>الضريبة:</td>
                    <td class="text-end">{{ number_format($invoice->tax, 2) }} {{ $currency }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td>الإجمالي النهائي:</td>
                <td class="text-end">{{ number_format($invoice->total, 2) }} {{ $currency }}</td>
            </tr>
            <tr>
                <td>المدفوع:</td>
                <td class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }} {{ $currency }}</td>
            </tr>
            <tr>
                <td><strong>المتبقي:</strong></td>
                <td class="text-end text-danger"><strong>{{ number_format($invoice->remaining_amount, 2) }} {{ $currency }}</strong></td>
            </tr>
        </table>
    </div>

    @if($invoice->payments->count() > 0)
        <h3 style="margin-top: 20px; color: #198754;">💰 المدفوعات</h3>
        <table>
            <thead>
                <tr>
                    <th>رقم السند</th>
                    <th>التاريخ</th>
                    <th>المبلغ</th>
                    <th>طريقة الدفع</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_no }}</td>
                        <td>{{ $payment->payment_date }}</td>
                        <td>{{ number_format($payment->amount, 2) }} {{ $currency }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ $payment->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($invoice->notes)
        <div style="margin-top: 20px; padding: 10px; background: #fff3cd; border-right: 4px solid #ffc107;">
            <strong>📝 ملاحظات:</strong> {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        <p>شكراً لتعاملكم معنا - {{ $companyName }}</p>
        <p>تم الطباعة في: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>