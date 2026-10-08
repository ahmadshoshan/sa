@extends('layouts.master')
@section('title', 'دفع مرتب')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">👷 دفع مرتب موظف</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('payables.pay-salary') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم الموظف/الشريك <span class="text-danger">*</span></label>
                                <input type="text" name="employee_name" class="form-control form-control-lg" required
                                       placeholder="مثال: أحمد محمد">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="1" min="1" class="form-control form-control-lg" required>
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
                            <div class="col-md-12">
                                <label class="form-label">ملاحظات</label>
                                <input type="text" name="notes" class="form-control" placeholder="مثال: مرتب شهر أغسطس 2026">
                            </div>
                            <div class="col-12 d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">✅ تأكيد دفع المرتب</button>
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