@extends('layouts.master')
@section('title', 'تحصيل من - ' . $supplier->name)
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💳 تحصيل من المورد: {{ $supplier->name }}</h2>
        <a href="{{ route('collections.suppliers') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white;">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div style="opacity: 0.9; font-size: 13px;">المورد</div>
                    <div style="font-size: 20px; font-weight: bold;">{{ $supplier->name }}</div>
                </div>
                <div class="col-md-4">
                    <div style="opacity: 0.9; font-size: 13px;">الكود</div>
                    <div style="font-size: 20px;">{{ $supplier->code }}</div>
                </div>
                <div class="col-md-4 text-end">
                    <div style="opacity: 0.9; font-size: 13px;">المستحق لنا</div>
                    <div style="font-size: 28px; font-weight: bold;">{{ number_format($owedAmount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('collections.collect-from-supplier', $supplier->id) }}">
        @csrf
        
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">💰 بيانات التحصيل</h5>
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
                        <label class="form-label fw-bold">طريقة التحصيل *</label>
                        <select name="payment_method" class="form-select form-select-lg" required>
                            <option value="cash">💵 نقدي</option>
                        
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
                                  placeholder="سبب التحصيل أو أي ملاحظات...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success btn-lg">
                    💳 تأكيد التحصيل
                </button>
            </div>
        </div>
    </form>
</div>
@endsection