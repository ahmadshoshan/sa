@include('partials.errors')

<form method="POST"
      action="{{ $customer ? route('customers.update', $customer) : route('customers.store') }}">

    @csrf

    @if($customer)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-4 mb-3">
            <label class="form-label">الكود</label>
            <input type="text"
                   name="code"
                   value="{{ old('code', $customer->code ?? '') }}"
                   class="form-control"
                   required>
        </div>

        <div class="col-md-8 mb-3">
            <label class="form-label">اسم العميل</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $customer->name ?? '') }}"
                   class="form-control"
                   required>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">الهاتف</label>
            <input type="text"
                   name="phone"
                   value="{{ old('phone', $customer->phone ?? '') }}"
                   class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $customer->email ?? '') }}"
                   class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">الرقم الضريبي</label>
            <input type="text"
                   name="tax_number"
                   value="{{ old('tax_number', $customer->tax_number ?? '') }}"
                   class="form-control">
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">العنوان</label>
            <textarea name="address" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">الرصيد الافتتاحي</label>
            <input type="number"
                   step="1"
                   name="opening_balance"
                   value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}"
                   class="form-control">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">حد الائتمان</label>
            <input type="number"
                   step="1"
                   name="credit_limit"
                   value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}"
                   class="form-control">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">مستوى السعر</label>
            <select name="price_level" class="form-select">
                <option value="retail" @selected(old('price_level', $customer->price_level ?? 'retail') == 'retail')>
                    قطاعي
                </option>

                <option value="wholesale" @selected(old('price_level', $customer->price_level ?? 'retail') == 'wholesale')>
                    جملة
                </option>

                <option value="special" @selected(old('price_level', $customer->price_level ?? 'retail') == 'special')>
                    خاص
                </option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label d-block">الحالة</label>

            <div class="form-check">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       class="form-check-input"
                       @checked(old('is_active', $customer->is_active ?? true))>

                <label class="form-check-label">نشط</label>
            </div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea>
        </div>

    </div>

    <button type="submit" class="btn btn-success">حفظ</button>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">إلغاء</a>

</form>