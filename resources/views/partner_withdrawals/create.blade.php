@extends('layouts.master')

@section('title', 'إضافة مسحوبات')

@section('content')

    <div class="card">
        <div class="card-header">إضافة مسحوبات للشريك: {{ $partner->name }}</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('partners.withdrawals.store', $partner) }}">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">التاريخ</label>
                        <input type="date" name="withdrawal_date" value="{{ old('withdrawal_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="1" name="amount" value="{{ old('amount') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">نقدي</option>
                            <option value="card">بطاقة</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="cheque">شيك</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success" id="save-withdrawal-btn">حفظ</button>
                <a href="{{ route('partners.show', $partner) }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection