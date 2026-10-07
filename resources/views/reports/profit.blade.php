@extends('layouts.master')

@section('title', 'تقرير الأرباح')

@section('content')

    <h4 class="mb-4">تقرير الأرباح</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.profit') }}">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('reports.profit') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6>إجمالي المبيعات</h6>
                    <h4>{{ number_format((float) $salesRevenue, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6>مرتجعات المبيعات</h6>
                    <h4>{{ number_format((float) $salesReturnRevenue, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6>صافي المبيعات</h6>
                    <h4>{{ number_format((float) $netRevenue, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6>صافي التكلفة</h6>
                    <h4>{{ number_format((float) $netCost, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6>صافي الربح</h6>
                    <h4>{{ number_format((float) $netProfit, 2) }}</h4>
                </div>
            </div>
        </div>

    </div>

@endsection