@extends('layouts.master')

@section('title', 'إضافة سند')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">إضافة سند قبض أو صرف</h5>
        </div>

        <div class="card-body">

            @include('partials.errors')

            <form method="POST" action="{{ route('payments.store') }}" x-data="{ partyType: '{{ old('supplier_id') ? 'supplier' : 'customer' }}' }">
                @csrf

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">نوع السند</label>
                        <select name="type" class="form-select" required>
                            <option value="receipt" @selected(old('type') == 'receipt')>سند قبض</option>
                            <option value="payment" @selected(old('type') == 'payment')>سند صرف</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الطرف</label>
                        <select class="form-select" x-model="partyType">
                            <option value="customer">عميل</option>
                            <option value="supplier">مورد</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" x-show="partyType === 'customer'">
                        <label class="form-label">العميل</label>
                        <select name="customer_id" class="form-select" :disabled="partyType !== 'customer'">
                            <option value="">اختر العميل</option>

                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" x-show="partyType === 'supplier'">
                        <label class="form-label">المورد</label>
                        <select name="supplier_id" class="form-select" :disabled="partyType !== 'supplier'">
                            <option value="">اختر المورد</option>

                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="1" min="1" name="amount" value="{{ old('amount') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" @selected(old('payment_method') == 'cash')>نقدي</option>
                            <option value="card" @selected(old('payment_method') == 'card')>بطاقة</option>
                            <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>تحويل بنكي</option>
                            <option value="cheque" @selected(old('payment_method') == 'cheque')>شيك</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">التاريخ</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">البيان</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-success">حفظ السند</button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>

        </div>
    </div>

@endsection