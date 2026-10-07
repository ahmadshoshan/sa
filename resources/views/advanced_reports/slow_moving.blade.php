@extends('layouts.master')

@section('title', 'الأصناف البطيئة')

@section('content')

    <h4 class="mb-4">الأصناف البطيئة (لم تُبع خلال 30 يوم)</h4>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>الكود</th><th>الصنف</th><th>المخزون</th><th>قيمة المخزون</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->code }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ number_format((float) ($row->stocks_sum_quantity ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($row->stocks_sum_quantity ?? 0) * (float) $row->cost_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">لا توجد أصناف بطيئة. ✅</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection