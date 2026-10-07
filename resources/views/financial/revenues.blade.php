@extends('layouts.master')

@section('title', 'تقرير الإيرادات')

@section('content')

    <h4 class="mb-4">تقرير الإيرادات</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('financial.revenues') }}">
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
                        <a href="{{ route('financial.revenues') }}" class="btn btn-secondary">إعادة تعيين</a>
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
                    <h4>{{ number_format((float) $total, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    @include('financial.partials.account_totals_table', [
        'rows' => $rows,
        'total' => $total,
        'from' => $from,
        'to' => $to,
    ])

@endsection