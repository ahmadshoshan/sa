@extends('layouts.master')

@section('title', 'حركة المخزون')

@section('content')

    <h4 class="mb-4">حركة المخزون</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.stock-movements') }}">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">الصنف</label>
                        <select name="product_id" class="form-select">
                            <option value="">كل الأصناف</option>

                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">المخزن</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">كل المخازن</option>

                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">نوع الحركة</label>
                        <select name="movement_type" class="form-select">
                            <option value="">الكل</option>
                            <option value="sale" @selected(request('movement_type') == 'sale')>بيع</option>
                            <option value="purchase" @selected(request('movement_type') == 'purchase')>شراء</option>
                            <option value="sale_return" @selected(request('movement_type') == 'sale_return')>مرتجع بيع</option>
                            <option value="purchase_return" @selected(request('movement_type') == 'purchase_return')>مرتجع شراء</option>
                            <option value="adjust_in" @selected(request('movement_type') == 'adjust_in')>إدخال يدوي</option>
                            <option value="adjust_out" @selected(request('movement_type') == 'adjust_out')>إخراج يدوي</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                </div>

                <div class="mt-3">
                    <button class="btn btn-primary">بحث</button>
                    <a href="{{ route('reports.stock-movements') }}" class="btn btn-secondary">إعادة تعيين</a>
                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الصنف</th>
                        <th>المخزن</th>
                        <th>نوع الحركة</th>
                        <th>الكمية</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $movement->product?->name }}</td>
                            <td>{{ $movement->warehouse?->name }}</td>
                            <td>{{ $movement->movement_type }}</td>
                            <td>{{ number_format((float) $movement->quantity, 2) }}</td>
                            <td>{{ $movement->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد نتائج.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $movements->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection