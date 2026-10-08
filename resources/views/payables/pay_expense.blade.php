@extends('layouts.master')
@section('title', 'دفع مصروف - ' . $expense->expense_no)
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">💳 دفع مصروف: {{ $expense->expense_no }}</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4"><strong>التصنيف:</strong> {{ $expense->category?->name ?? '-' }}</div>
                            <div class="col-md-4"><strong>التاريخ:</strong> {{ $expense->expense_date }}</div>
                            <div class="col-md-4"><strong>الوصف:</strong> {{ $expense->description ?? '-' }}</div>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-md-4"><strong>الإجمالي:</strong> {{ number_format($expense->total, 2) }}</div>
                            <div class="col-md-4"><strong>المدفوع:</strong> {{ number_format($expense->paid_amount, 2) }}</div>
                            <div class="col-md-4"><strong class="text-danger">المتبقي:</strong> {{ number_format($expense->remaining_amount, 2) }}</div>
                        </div>
                    </div>

                    <form action="{{ route('payables.pay-expense', $expense->id) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="1" min="1" max="{{ $expense->remaining_amount }}"
                                       value="{{ $expense->remaining_amount }}" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select form-control-lg" required>
                                    <option value="cash">نقدي</option>
                                    <option value="card"> انستا/كاش</option>
                                    <option value="bank_transfer">تحويل بنكي</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الدفع من حساب <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select form-control-lg" required>
                                    @foreach($cashAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ملاحظات</label>
                                <input type="text" name="notes" class="form-control" placeholder="ملاحظات اختيارية...">
                            </div>
                            <div class="col-12 d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">✅ تأكيد الدفع</button>
                                <a href="{{ route('payables.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection