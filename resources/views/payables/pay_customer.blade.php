@extends('layouts.master')
@section('title', 'دفع مستحقات - ' . $customer->name)
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💳 دفع مستحقات العميل: {{ $customer->name }}</h2>
        <a href="{{ route('payables.customers') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); color: white;">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div style="opacity: 0.9; font-size: 13px;">العميل</div>
                    <div style="font-size: 20px; font-weight: bold;">{{ $customer->name }}</div>
                </div>
                <div class="col-md-4">
                    <div style="opacity: 0.9; font-size: 13px;">الكود</div>
                    <div style="font-size: 20px;">{{ $customer->code }}</div>
                </div>
                <div class="col-md-4 text-end">
                    <div style="opacity: 0.9; font-size: 13px;">المستحق للعميل</div>
                    <div style="font-size: 28px; font-weight: bold;">{{ number_format($owedAmount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('payables.pay-customer', $customer->id) }}">
        @csrf
        
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">💰 بيانات الدفع</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">المبلغ *</label>
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $owedAmount }}"
                               value="{{ old('amount', $owedAmount) }}" class="form-control form-control-lg" required>
                        <small class="text-muted">الحد الأقصى: {{ number_format($owedAmount, 2) }}</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">طريقة الدفع *</label>
                        <select name="payment_method" class="form-select form-select-lg" required>
                            <option value="cash">💵 نقدي</option>
                            <option value="card">💳  انستا/كاش</option>
                            <option value="bank_transfer">🏦 تحويل بنكي</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">الحساب *</label>
                        <select name="account_id" class="form-select form-select-lg" required>
                            <option value="">-- اختر الحساب --</option>
                            @foreach(\App\Models\Account::whereIn('code', ['1001', '1002', '1003'])->get() as $account)
                                <option value="{{ $account->id }}">
                                    @if($account->code === '1001') 💵 @elseif($account->code === '1002') 🏦 @else 💳 @endif
                                    {{ $account->name }} ({{ $account->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <textarea name="notes" rows="2" class="form-control" 
                                  placeholder="سبب الدفع أو أي ملاحظات...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-danger btn-lg">
                    💳 تأكيد الدفع
                </button>
            </div>
        </div>
    </form>
</div>
@endsection