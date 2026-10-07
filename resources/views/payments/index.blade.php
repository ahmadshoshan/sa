@extends('layouts.master')

@section('title', 'السندات')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>سندات القبض والصرف</h4>

        @if(auth()->user()->can('payments.create'))
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                إضافة سند
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>رقم السند</th>
                            <th>وصف </th>
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
                                <td>{{ $payment->notes }}</td>
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
                                <td colspan="8" class="text-center">
                                    لا توجد سندات.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection