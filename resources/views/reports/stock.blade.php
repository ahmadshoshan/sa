@extends('layouts.master')

@section('title', 'تقرير المخزون')

@section('content')

    <h4 class="mb-4">تقرير المخزون</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.stock') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               class="form-control"
                               placeholder="اسم الصنف / الكود / الباركود">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('reports.stock') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>الباركود</th>
                        <th>الصنف</th>
                        <th>التصنيف</th>
                        <th>الوحدة</th>
                        <th>الرصيد</th>
                        <th>الحد الأدنى</th>
                        <th>التكلفة</th>
                        <th>سعر البيع</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->barcode }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->unit?->name }}</td>
                            <td>
                                {{ number_format((float) ($product->stocks_sum_quantity ?? 0), 2) }}

                                @if(((float) ($product->stocks_sum_quantity ?? 0)) <= (float) $product->min_stock)
                                    <span class="badge bg-danger">منخفض</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $product->min_stock, 2) }}</td>
                            <td>{{ number_format((float) $product->cost_price, 2) }}</td>
                            <td>{{ number_format((float) $product->sale_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">لا توجد نتائج.</td>
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