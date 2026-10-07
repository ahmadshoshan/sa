@extends('layouts.master')
@section('title', 'التحويل النقدي')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🔄 التحويل النقدي بين الحسابات</h5>
                </div>
                <div class="card-body">
                    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    <form action="{{ route('fund.store-transfer') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">من حساب <span class="text-danger">*</span></label>
                                <select name="from_account_id" class="form-select form-select-lg" required>
                                    <option value="">اختر الحساب المصدر</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('from_account_id') == $account->id)>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">إلى حساب <span class="text-danger">*</span></label>
                                <select name="to_account_id" class="form-select form-select-lg" required>
                                    <option value="">اختر الحساب المستلم</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('to_account_id') == $account->id)>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">المبلغ <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="1" min="1" class="form-control form-control-lg" value="{{ old('amount') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">التاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control form-control-lg" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">ملاحظات</label>
                                <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <h6>ℹ️ معلومات القيد المحاسبي:</h6>
                                    <ul class="mb-0">
                                        <li>سيتم إنشاء قيد محاسبي تلقائياً</li>
                                        <li>مدين: الحساب المستلم</li>
                                        <li>دائن: الحساب المصدر</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12 d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">✅ تنفيذ التحويل</button>
                                <a href="{{ route('fund.index') }}" class="btn btn-outline-secondary btn-lg">↩️ رجوع</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection