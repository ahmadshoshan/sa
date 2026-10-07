@extends('layouts.master')

@section('title', 'تقسيط فاتورة')

@section('content')

    @php
        $remaining = (float) $invoice->remaining_amount;
        $hasPaidInstallments = (float) $invoice->installments->sum('paid_amount') > 0;
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">تقسيط الفاتورة: {{ $invoice->invoice_no }}</h5>

            <div class="d-flex gap-2">
                <a href="{{ route('sales.show', $invoice) }}" class="btn btn-secondary btn-sm">رجوع للفاتورة</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>العميل:</strong><br>
                    {{ $invoice->customer?->name }}
                </div>

                <div class="col-md-3">
                    <strong>إجمالي الفاتورة:</strong><br>
                    {{ number_format((float) $invoice->total, 2) }}
                </div>

                <div class="col-md-3">
                    <strong>المدفوع:</strong><br>
                    {{ number_format((float) $invoice->paid_amount, 2) }}
                </div>

                <div class="col-md-3">
                    <strong>المتبقي:</strong><br>
                    {{ number_format($remaining, 2) }}
                </div>
            </div>
        </div>
    </div>

    @if($remaining > 0 && !$hasPaidInstallments)
        <div class="card mb-4">
            <div class="card-header">إنشاء خطة تقسيط</div>

            <div class="card-body">

                @include('partials.errors')

                <form method="POST" action="{{ route('sales.installments.generate', $invoice) }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">عدد الأقساط</label>
                            <input type="number"
                                   name="count"
                                   value="{{ old('count', 3) }}"
                                   min="1"
                                   max="120"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">الفاصل بين الأقساط (يوم)</label>
                            <input type="number"
                                   name="interval_days"
                                   value="{{ old('interval_days', 30) }}"
                                   min="0"
                                   max="365"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">تاريخ أول قسط</label>
                            <input type="date"
                                   name="first_date"
                                   value="{{ old('first_date', date('Y-m-d')) }}"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">إنشاء الجدولة</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">الأقساط</div>

        <div class="card-body">

            @if($invoice->installments->count())
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>تاريخ الاستحقاق</th>
                                <th>قيمة القسط</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>الحالة</th>
                                <th>السداد</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($invoice->installments as $installment)
                                @php
                                    $installmentRemaining = (float) $installment->amount - (float) $installment->paid_amount;
                                    $progress = (float) $installment->amount > 0
                                        ? round(((float) $installment->paid_amount / (float) $installment->amount) * 100)
                                        : 0;
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $installment->due_date?->format('Y-m-d') }}

                                        @if($installment->status !== 'paid' && $installment->due_date->isPast())
                                            <span class="badge bg-danger">متأخر</span>
                                        @elseif($installment->status !== 'paid' && $installment->due_date->isToday())
                                            <span class="badge bg-warning">مستحق اليوم</span>
                                        @endif
                                    </td>

                                    <td>{{ number_format((float) $installment->amount, 2) }}</td>
                                    <td>{{ number_format((float) $installment->paid_amount, 2) }}</td>
                                    <td>{{ number_format($installmentRemaining, 2) }}</td>

                                    <td style="min-width: 160px;">
                                        @if($installment->status === 'paid')
                                            <span class="badge bg-success">مدفوع</span>
                                        @elseif($installment->status === 'partial')
                                            <span class="badge bg-warning">مدفوع جزئيًا</span>
                                        @else
                                            <span class="badge bg-secondary">غير مدفوع</span>
                                        @endif

                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </td>

                                    <td style="min-width: 260px;">
                                        @if($installmentRemaining > 0 && $remaining > 0)
                                            <form method="POST" action="{{ route('installments.pay', $installment) }}" class="row g-2">
                                                @csrf

                                                <div class="col-4">
                                                    <input type="number"
                                                           name="amount"
                                                           step="1"
                                                           min="0.01"
                                                           max="{{ min($installmentRemaining, $remaining) }}"
                                                           value="{{ min($installmentRemaining, $remaining) }}"
                                                           class="form-control form-control-sm"
                                                           required>
                                                </div>

                                                <div class="col-4">
                                                    <select name="payment_method" class="form-select form-select-sm" required>
                                                        <option value="cash">نقدي</option>
                                                        <option value="card">بطاقة</option>
                                                        <option value="bank_transfer">تحويل</option>
                                                        <option value="cheque">شيك</option>
                                                    </select>
                                                </div>

                                                <div class="col-4">
                                                    <button class="btn btn-success btn-sm w-100">دفع</button>
                                                </div>

                                                <div class="col-12">
                                                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted mb-0">
                    لا توجد خطة تقسيط لهذه الفاتورة حتى الآن.
                </p>
            @endif

        </div>
    </div>

@endsection