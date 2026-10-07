@include('partials.errors')

<form method="POST"
      action="{{ $supplier ? route('suppliers.update', $supplier) : route('suppliers.store') }}">

    @csrf

    @if($supplier)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-4 mb-3">
            <label class="form-label">الكود</label>
            <input type="text"
                   name="code"
                   value="{{ old('code', $supplier->code ?? '') }}"
                   class="form-control"
                   required>
        </div>

        <div class="col-md-8 mb-3">
            <label class="form-label">اسم المورد</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $supplier->name ?? '') }}"
                   class="form-control"
                   required>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">الهاتف</label>
            <input type="text"
                   name="phone"
                   value="{{ old('phone', $supplier->phone ?? '') }}"
                   class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $supplier->email ?? '') }}"
                   class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">الرقم الضريبي</label>
            <input type="text"
                   name="tax_number"
                   value="{{ old('tax_number', $supplier->tax_number ?? '') }}"
                   class="form-control">
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">العنوان</label>
            <textarea name="address" class="form-control">{{ old('address', $supplier->address ?? '') }}</textarea>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">الرصيد الافتتاحي</label>
            <input type="number"
                   step="1"
                   name="opening_balance"
                   value="{{ old('opening_balance', $supplier->opening_balance ?? 0) }}"
                   class="form-control">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label d-block">الحالة</label>

            <div class="form-check">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       class="form-check-input"
                       @checked(old('is_active', $supplier->is_active ?? true))>

                <label class="form-check-label">نشط</label>
            </div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $supplier->notes ?? '') }}</textarea>
        </div>

    </div>

    <button type="submit" class="btn btn-success">حفظ</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">إلغاء</a>

</form>