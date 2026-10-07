@extends('layouts.master')
@section('title', 'تفاصيل الحساب - ' . $account->name)
@section('content')
<div class="container py-4">
    <div class="card mb-4 shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📊 تفاصيل الحساب: {{ $account->name }}</h5>
            <a href="{{ route('fund.index') }}" class="btn btn-outline-secondary btn-sm">↩️ رجوع</a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">الرصيد الحالي</h6>
                            <h2 class="mb-0 fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($balance, 2) }}</h2>
                            <small>كود الحساب: {{ $account->code }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">عدد المعاملات</h6>
                            <h2 class="mb-0 fw-bold text-primary">{{ $transactions->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">نوع الحساب</h6>
                            <h2 class="mb-0 fw-bold">{{ $account->type }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header bg-white"><h5 class="mb-0">📜 سجل المعاملات</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>رقم المعاملة</th><th>التاريخ</th><th>النوع</th><th class="text-end">المبلغ</th><th>ملاحظات</th></tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td><code>{{ $transaction->transaction_no }}</code></td>
                                <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($transaction->type == 'transfer_in') <span class="badge bg-success">تحويل وارد</span>
                                    @elseif($transaction->type == 'transfer_out') <span class="badge bg-danger">تحويل صادر</span>
                                    @else <span class="badge bg-secondary">{{ $transaction->type }}</span> @endif
                                </td>
                                <td class="text-end fw-bold">{{ number_format($transaction->amount, 2) }}</td>
                                <td>{{ $transaction->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">لا توجد معاملات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection