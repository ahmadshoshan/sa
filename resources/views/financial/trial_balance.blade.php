@extends('layouts.master')

@section('title', 'ميزان المراجعة')

@section('content')

    <h4 class="mb-4">ميزان المراجعة</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('financial.trial-balance') }}">
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
                        <a href="{{ route('financial.trial-balance') }}" class="btn btn-secondary">إعادة تعيين</a>
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
                        <th>الحساب</th>
                        <th>الرصيد الافتتاحي</th>
                        <th>مدين الفترة</th>
                        <th>دائن الفترة</th>
                        <th>الرصيد الختامي</th>
                        <th>الطبيعة</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['account']->code }}</td>
                            <td>{{ $row['account']->name }}</td>

                            <td>
                                {{ number_format(abs($row['opening']), 2) }}
                                {{ $row['opening'] >= 0 ? 'مدين' : 'دائن' }}
                            </td>

                            <td>{{ number_format((float) $row['debit'], 2) }}</td>
                            <td>{{ number_format((float) $row['credit'], 2) }}</td>

                            <td>
                                {{ number_format(abs($row['closing']), 2) }}
                                {{ $row['closing'] >= 0 ? 'مدين' : 'دائن' }}
                            </td>

                            <td>
                                @if($row['closing'] >= 0)
                                    <span class="badge bg-success">مدين</span>
                                @else
                                    <span class="badge bg-danger">دائن</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                لا توجد قيود في الفترة المحددة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="2">الإجمالي</th>

                        <th>
                            {{ number_format((float) $totals['opening_debit'], 2) }} /
                            {{ number_format((float) $totals['opening_credit'], 2) }}
                        </th>

                        <th>{{ number_format((float) $totals['debit'], 2) }}</th>
                        <th>{{ number_format((float) $totals['credit'], 2) }}</th>

                        <th>
                            {{ number_format((float) $totals['closing_debit'], 2) }} /
                            {{ number_format((float) $totals['closing_credit'], 2) }}
                        </th>

                        <th>
                            @if(abs($totals['closing_debit'] - $totals['closing_credit']) < 0.01)
                                <span class="badge bg-success">متوازن</span>
                            @else
                                <span class="badge bg-danger">غير متوازن</span>
                            @endif
                        </th>
                    </tr>
                </tfoot>
            </table>

        </div>
    </div>

@endsection