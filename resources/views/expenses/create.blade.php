@extends('layouts.master')

@section('title', 'إضافة مصروف')

@section('content')

    <div class="card">
        <div class="card-header">إضافة مصروف</div>

        <div class="card-body">
            @include('partials.errors')

            <!-- <form method="POST" action="{{ route('expenses.store') }}" x-data="{ amount: {{ old('amount', 0) }}, tax: {{ old('tax_amount', 0) }} }">
                @csrf
                <input type="hidden" name="_idempotency_key" value="{{ old('_idempotency_key', $idempotencyKey) }}">

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">التاريخ</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">اختر التصنيف</option>

                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">الشريك المرتبط</label>
                        <select name="partner_id" class="form-select">
                            <option value="">بدون شريك</option>

                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" @selected(old('partner_id') == $partner->id)>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">البيان</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" x-model.number="amount" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الضريبة</label>
                        <input type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', 0) }}" x-model.number="tax" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الإجمالي</label>
                        <input type="text" class="form-control" :value="((parseFloat(amount) || 0) + (parseFloat(tax) || 0)).toFixed(2)" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" @selected(old('payment_method', 'cash') == 'cash')>نقدي</option>
                            <option value="card" @selected(old('payment_method') == 'card')>انستا/كاش</option>
                            <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>تحويل بنكي</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button class="btn btn-success">حفظ المصروف</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">إلغاء</a>
            </form> -->
<form method="POST" action="{{ route('expenses.store') }}" x-data="{ amount: {{ old('amount', 0) }}, tax: {{ old('tax_amount', 0) }} }">
    @csrf

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">التاريخ</label>
            <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">التصنيف <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
                <option value="">اختر التصنيف</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5 mb-3">
            <label class="form-label">الشريك المرتبط</label>
            <select name="partner_id" class="form-select">
                <option value="">بدون شريك</option>
                @foreach($partners as $partner)
                    <option value="{{ $partner->id }}" @selected(old('partner_id') == $partner->id)>
                        {{ $partner->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">البيان</label>
            <input type="text" name="description" value="{{ old('description') }}" class="form-control">
        </div>

        {{-- ✅ التصحيح هنا: step="0.01" للسماح بالأرقام العشرية --}}
        <div class="col-md-3 mb-3">
            <label class="form-label">المبلغ <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="amount" x-model.number="amount" class="form-control" required>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">الضريبة</label>
            <input type="number" step="0.01" min="0" name="tax_amount" x-model.number="tax" class="form-control" value="0">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">الإجمالي</label>
            <input type="text" class="form-control bg-light" 
                   :value="((parseFloat(amount) || 0) + (parseFloat(tax) || 0)).toFixed(2)" 
                   readonly>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
            <select name="payment_method" class="form-select" required>
                <option value="cash" @selected(old('payment_method', 'cash') == 'cash')>نقدي</option>
                <option value="card" @selected(old('payment_method') == 'card')>انستا/كاش</option>
                <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>تحويل بنكي</option>
            </select>
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>

    {{-- ✅ التصحيح هنا: إضافة type="submit" بشكل صريح --}}
    <button type="submit" class="btn btn-success">
        <span x-show="false" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        حفظ المصروف
    </button>
    
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">إلغاء</a>
</form>
        </div>
    </div>

@endsection