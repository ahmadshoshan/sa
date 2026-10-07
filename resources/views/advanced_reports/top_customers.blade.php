@extends('layouts.master')

@section('title', 'أفضل العملاء')

@section('content')

    <h4 class="mb-4">أفضل العملاء</h4>

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
                        <tr><th>#</th><th>العميل</th><th>عدد الفواتير</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->customer?->name }}</td>
                                <td>{{ $row->count }}</td>
                                <td>{{ number_format((float) $row->total, 2) }}</td>
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