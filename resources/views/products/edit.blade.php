@extends('layouts.master')

@section('title', 'تعديل صنف')

@section('content')

    <div class="card">
        <div class="card-header">تعديل الصنف</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الكود</label>
                        <input type="text" name="code" value="{{ old('code', $product->code) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الباركود</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم الصنف</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الوحدة</label>
                        <select name="unit_id" class="form-select" required>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id) == $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">سعر التكلفة</label>
                        <input type="number" step="1" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">سعر البيع</label>
                        <input type="number" step="1" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">سعر الجملة</label>
                        <input type="number" step="1" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الضريبة %</label>
                        <input type="number" step="1" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الحد الأدنى</label>
                        <input type="number" step="1" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">🖼️ صورة الصنف</label>

                        @if($product->image)
                            <div class="mb-2">
                                <img src="{{ asset($product->image) }}" style="width:80px; height:80px; object-fit:cover; border-radius:10px;">
                            </div>
                        @endif

                        <input type="file" name="image" accept="image/*" class="form-control">
                        <small class="text-muted">اتركه فارغًا للإبقاء على الصورة الحالية.</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">الحالة</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($product->is_active)>
                            <label class="form-check-label">نشط</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection