@extends('layouts.master')

@section('title', 'لوحة التقارير')

@section('content')

    <h4 class="mb-4">لوحة التقارير</h4>

    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6>مبيعات اليوم</h6>
                    <h4>{{ number_format((float) $todaySales, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6>مبيعات الشهر</h6>
                    <h4>{{ number_format((float) $monthSales, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6>عدد فواتير البيع</h6>
                    <h4>{{ $salesCount }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h6>الأصناف</h6>
                    <h4>{{ $productsCount }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6>العملاء</h6>
                    <h4>{{ $customersCount }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6>الموردون</h6>
                    <h4>{{ $suppliersCount }}</h4>
                </div>
            </div>
        </div>

    </div>

    <div class="card mb-4">
        <div class="card-header">
            أصناف وصلت الحد الأدنى
        </div>

        <div class="card-body">

            @if($lowStockProducts->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الصنف</th>
                            <th>الرصيد</th>
                            <th>الحد الأدنى</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format((float) ($product->stocks_sum_quantity ?? 0), 2) }}</td>
                                <td>{{ number_format((float) $product->min_stock, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center mb-0">لا توجد أصناف تحت الحد الأدنى حاليًا.</p>
            @endif

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            آخر فواتير البيع
        </div>

        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>التاريخ</th>
                        <th>الإجمالي</th>
                        <th>عرض</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($latestSales as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->customer?->name }}</td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>
                                <a href="{{ route('sales.show', $invoice) }}" class="btn btn-sm btn-info">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">لا توجد فواتير بيع.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

@endsection