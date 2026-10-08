@extends('layouts.master')

@section('title', 'تقرير المدفوعات')

@section('content')

    <h4 class="mb-4">تقرير المدفوعات</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.payments') }}">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">النوع</label>
                        <select name="type" class="form-select">
                            <option value="">الكل</option>
                            <option value="receipt" @selected(request('type') == 'receipt')>قبض</option>
                            <option value="payment" @selected(request('type') == 'payment')>صرف</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select">
                            <option value="">الكل</option>
                            <option value="cash" @selected(request('payment_method') == 'cash')>نقدي</option>
                            <option value="card" @selected(request('payment_method') == 'card')> انستا/كاش</option>
                            <option value="bank_transfer" @selected(request('payment_method') == 'bank_transfer')>تحويل بنكي</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('reports.payments') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>رقم السند</th>
                        <th>التاريخ</th>
                        <th>النوع</th>
                        <th>الطرف</th>
                        <th>الفاتورة</th>
                        <th>المبلغ</th>
                        <th>الطريقة</th>
                        <th>طباعة</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_no }}</td>
                            <td>{{ $payment->payment_date?->format('Y-m-d') }}</td>

                            <td>
                                @if($payment->type === 'receipt')
                                    <span class="badge bg-success">قبض</span>
                                @else
                                    <span class="badge bg-danger">صرف</span>
                                @endif
                            </td>

                            <td>
                                @if($payment->customer)
                                    {{ $payment->customer->name }}
                                @elseif($payment->supplier)
                                    {{ $payment->supplier->name }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ $payment->invoice?->invoice_no ?? '-' }}</td>
                            <td>{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>
                                <a href="{{ route('payments.print', $payment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    طباعة
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد نتائج.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection