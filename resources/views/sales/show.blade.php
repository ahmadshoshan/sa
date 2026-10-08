@extends('layouts.master')

@section('title', 'عرض فاتورة بيع')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">فاتورة بيع رقم: {{ $invoice->invoice_no }}</h5>

            <div class="d-flex gap-2 flex-wrap">

                @if ((float) $invoice->remaining_amount > 0)
                    <a href="{{ route('sales.installments', $invoice) }}" class="btn btn-outline-success btn-sm">
                        💳 التقسيط
                    </a>
                @else
                    <a href="{{ route('sales.installments', $invoice) }}" class="btn btn-outline-secondary btn-sm">
                        💳 عرض الأقساط
                    </a>
                @endif

                <a href="{{ route('sales.print.a4', $invoice) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    طباعة A4
                </a>

                <a href="{{ route('sales.print.thermal', $invoice) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                    طباعة حرارية
                </a>

                <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
                    رجوع
                </a> <a href="{{ route('pdf.invoice', $invoice->id) }}" target="_blank" class="btn btn-pdf">
                    📄 طباعة PDF
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>العميل:</strong><br>
                    {{ $invoice->customer?->name }}
                </div>

                <div class="col-md-3">
                    <strong>المخزن:</strong><br>
                    {{ $invoice->warehouse?->name }}
                </div>

                <div class="col-md-2">
                    <strong>التاريخ:</strong><br>
                    {{ $invoice->invoice_date?->format('Y-m-d') }}
                </div>

                <div class="col-md-2">
                    <strong>الاستحقاق:</strong><br>
                    {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}
                </div>

                <div class="col-md-2">
                    <strong>الحالة:</strong><br>

                    @if ($invoice->status === 'posted')
                        <span class="badge bg-success">مرحّلة</span>
                    @elseif($invoice->status === 'draft')
                        <span class="badge bg-warning">مسودة</span>
                    @elseif($invoice->status === 'cancelled')
                        <span class="badge bg-danger">ملغاة</span>
                    @else
                        <span class="badge bg-secondary">{{ $invoice->status }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">الأصناف</div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
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
                        @foreach ($invoice->items as $item)
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
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">المدفوعات</div>

                <div class="card-body">
                    @forelse($invoice->payments as $payment)
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>{{ $payment->payment_no }}</span>
                            <span>{{ number_format((float) $payment->amount, 2) }}</span>
                            <span>{{ $payment->payment_date?->format('Y-m-d') }}</span>
                        </div>
                    @empty
                        <p class="text-center mb-0">لا توجد دفعات على هذه الفاتورة.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">المرتجعات</div>
        
                @forelse($invoice->returns as $return)
                
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span>{{ $return->invoice_no }}</span>
                        <span>{{ number_format((float) $return->total, 2) }}</span>
                        <span>{{ $return->invoice_date?->format('Y-m-d') }}</span>
                    </div>
                @empty
                    <p class="text-center mb-0">لا توجد مرتجعات على هذه الفاتورة.</p>
                @endforelse
                 
               

            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>الإجمالي قبل الخصم</span>
                        <strong>{{ number_format((float) $invoice->subtotal, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>الخصم</span>
                        <strong>{{ number_format((float) $invoice->discount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>الضريبة</span>
                        <strong>{{ number_format((float) $invoice->tax, 2) }}</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>الإجمالي النهائي</span>
                        <strong>{{ number_format((float) $invoice->total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>المدفوع</span>
                        <strong>{{ number_format((float) $invoice->paid_amount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>المتبقي</span>
                        <strong>{{ number_format((float) $invoice->remaining_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
