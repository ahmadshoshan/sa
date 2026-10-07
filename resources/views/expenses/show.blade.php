@extends('layouts.master')

@section('title', 'عرض مصروف')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">مصروف رقم: {{ $expense->expense_no }}</h5>

            <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>التاريخ:</strong><br>{{ $expense->expense_date?->format('Y-m-d') }}
                </div>

                <div class="col-md-3">
                    <strong>التصنيف:</strong><br>{{ $expense->category?->name }}
                </div>

                <div class="col-md-3">
                    <strong>الحساب:</strong><br>{{ $expense->category?->account?->name ?? '-' }}
                </div>

                <div class="col-md-3">
                    <strong>الشريك:</strong><br>{{ $expense->partner?->name ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>المبلغ</span>
                        <strong>{{ number_format((float) $expense->amount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>الضريبة</span>
                        <strong>{{ number_format((float) $expense->tax_amount, 2) }}</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>الإجمالي</span>
                        <strong>{{ number_format((float) $expense->total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>المدفوع</span>
                        <strong>{{ number_format((float) $expense->paid_amount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>المتبقي</span>
                        <strong>{{ number_format((float) $expense->remaining_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">الدفعات</div>

                <div class="card-body">
                    @forelse($expense->payments as $payment)
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>{{ $payment->payment_no }}</span>
                            <span>{{ number_format((float) $payment->amount, 2) }}</span>
                            <span>{{ $payment->payment_method }}</span>
                        </div>
                    @empty
                        <p class="text-center mb-0">لا توجد دفعات.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection