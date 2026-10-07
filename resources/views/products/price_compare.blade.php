@extends('layouts.master')
@section('title', 'مقارنة الأسعار')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💰 مقارنة الأسعار حسب مستوى العميل</h2>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>الصنف</th>
                            <th class="text-end">💵 التكلفة</th>
                            <th class="text-end">🏭 المصنع</th>
                            <th class="text-end">🏪 الجملة</th>
                            <th class="text-end">💰 البيع</th>
                            <th class="text-end">هامش البيع</th>
                            <th class="text-end">هامش الجملة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $cost = (float) $product->cost_price;
                                $factory = (float) $product->factory_price;
                                $wholesale = (float) $product->wholesale_price;
                                $retail = (float) $product->sale_price;
                                $marginRetail = $cost > 0 ? (($retail - $cost) / $cost * 100) : 0;
                                $marginWholesale = $cost > 0 ? (($wholesale - $cost) / $cost * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    <br><small class="text-muted">{{ $product->code }}</small>
                                </td>
                                <td class="text-end text-secondary">{{ number_format($cost, 2) }}</td>
                                <td class="text-end text-warning">{{ number_format($factory, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($wholesale, 2) }}</td>
                                <td class="text-end text-primary fw-bold">{{ number_format($retail, 2) }}</td>
                                <td class="text-end {{ $marginRetail > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($marginRetail, 1) }}%
                                </td>
                                <td class="text-end {{ $marginWholesale > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($marginWholesale, 1) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection