@extends('layouts.master')

@section('title', 'إضافة صنف')

@section('content')

    <div class="card">
        <div class="card-header">إضافة صنف جديد</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الكود</label>
                        <input type="text" value="{{ $identifiers['code'] }}" class="form-control" readonly>
                        <small class="text-muted">يُنشأ الكود تلقائياً عند حفظ الصنف.</small>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الباركود</label>
                        <input type="text" value="{{ $identifiers['barcode'] }}" class="form-control" readonly>
                        <small class="text-muted">باركود EAN-13 يُنشأ تلقائياً عند الحفظ.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم الصنف</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">اختر</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الوحدة</label>
                        <select name="unit_id" class="form-select" required>
                            <option value="">اختر</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">سعر التكلفة</label>
                        <input type="number" step="1" name="cost_price" value="{{ old('cost_price', 0) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">سعر البيع</label>
                        <input type="number" step="1" name="sale_price" value="{{ old('sale_price', 0) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">سعر الجملة</label>
                        <input type="number" step="1" name="wholesale_price" value="{{ old('wholesale_price', 0) }}" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الضريبة %</label>
                        <input type="number" step="1" name="tax_rate" value="{{ old('tax_rate', 0) }}" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الحد الأدنى</label>
                        <input type="number" step="1" name="min_stock" value="{{ old('min_stock', 0) }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">🖼️ صورة الصنف</label>
                        <input type="file" name="image" accept="image/*" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">الحالة</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                            <label class="form-check-label">نشط</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">حفظ</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection