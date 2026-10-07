@extends('layouts.master')

@section('title', 'الأصناف')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>الأصناف</h4>

        <a href="{{ route('products.create') }}" class="btn btn-primary">
            إضافة صنف
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الكود</th>
                        <th>الباركود</th>
                        <th>اسم الصنف</th>
                        <th>التصنيف</th>
                        <th>الوحدة</th>
                        <th>سعر البيع</th>
                        <th>الرصيد</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->barcode }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->unit?->name }}</td>
                            <td>{{ number_format((float) $product->sale_price, 2) }}</td>
                            <td>{{ number_format((float) ($product->stocks_sum_quantity ?? 0), 2) }}</td>

                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">
                                    تعديل
                                </a>
                                    <a href="{{ route('product.card', $product->id) }}" class="btn btn-sm btn-outline-info" title="كارتة الصنف">🗂️</a>

                                <form action="{{ route('products.destroy', $product) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف؟');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                لا توجد أصناف حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection