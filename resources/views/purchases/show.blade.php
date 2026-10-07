@extends('layouts.master')

@section('title', 'عرض فاتورة شراء')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">فاتورة شراء رقم: {{ $invoice->invoice_no }}</h5>

            <div class="d-flex gap-2">
                <a href="{{ route('purchases.print.a4', $invoice) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    طباعة A4
                </a>

                <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">
                    رجوع
                </a>                 <a href="{{ route('pdf.invoice', $invoice->id) }}" target="_blank" class="btn btn-pdf">
                    📄 طباعة PDF
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>المورد:</strong><br>
                    {{ $invoice->supplier?->name }}
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
                    <span class="badge bg-success">مرحّلة</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">الأصناف</div>

        <div class="card-body">
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
                            <span>{{ $payment->payment_method }}</span>
                        </div>
                    @empty
                        <p class="text-center mb-0">لا توجد دفعات على هذه الفاتورة.</p>
                    @endforelse
                </div>
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