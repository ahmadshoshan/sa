@extends('layouts.master')

@section('title', 'المبيعات حسب الصنف')

@section('content')

    <h4 class="mb-4">المبيعات حسب الصنف</h4>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
                <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
                <div class="col-md-3"><button class="btn btn-primary">بحث</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>الصنف</th><th>الكمية</th><th>الإجمالي</th><th>الربح</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td>{{ number_format((float) $row->qty, 2) }}</td>
                                <td>{{ number_format((float) $row->total, 2) }}</td>
                                <td class="text-success">{{ number_format((float) $row->profit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">لا توجد بيانات.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection