@extends('layouts.master')

@section('title', 'قائمة الدخل')

@section('content')

    <h4 class="mb-4">قائمة الدخل</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('financial.income-statement') }}">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from" value="{{ $from }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to" value="{{ $to }}" class="form-control">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('financial.income-statement') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6>إجمالي الإيرادات</h6>
                    <h4>{{ number_format((float) $totalRevenue, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6>إجمالي المصروفات</h6>
                    <h4>{{ number_format((float) $totalExpense, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card {{ $netProfit >= 0 ? 'text-white bg-primary' : 'text-white bg-dark' }}">
                <div class="card-body">
                    <h6>صافي الربح / الخسارة</h6>
                    <h4>{{ number_format((float) $netProfit, 2) }}</h4>
                </div>
            </div>
        </div>

    </div>

    <h5 class="mb-3">الإيرادات</h5>

    @include('financial.partials.account_totals_table', [
        'rows' => $revenueRows,
        'total' => $totalRevenue,
        'from' => $from,
        'to' => $to,
    ])

    <div class="mt-4"></div>

    <h5 class="mb-3">المصروفات</h5>

    @include('financial.partials.account_totals_table', [
        'rows' => $expenseRows,
        'total' => $totalExpense,
        'from' => $from,
        'to' => $to,
    ])

@endsection