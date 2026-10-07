@extends('layouts.master')

@section('title', 'المصروفات')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>المصروفات</h4>

        @if(auth()->user()->can('expenses.create'))
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">إضافة مصروف</a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>رقم المصروف</th>
                            <th>التاريخ</th>
                            <th>التصنيف</th>
                            <th>البيان</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>الحالة</th>
                            <th>عرض</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_no }}</td>
                                <td>{{ $expense->expense_date?->format('Y-m-d') }}</td>
                                <td>{{ $expense->category?->name }}</td>
                                <td>{{ $expense->description }}</td>
                                <td>{{ number_format((float) $expense->total, 2) }}</td>
                                <td>{{ number_format((float) $expense->paid_amount, 2) }}</td>

                                <td>
                                    @if($expense->payment_status === 'paid')
                                        <span class="badge bg-success">مدفوع</span>
                                    @elseif($expense->payment_status === 'partial')
                                        <span class="badge bg-warning">جزئي</span>
                                    @else
                                        <span class="badge bg-secondary">غير مدفوع</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-info">عرض</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد مصروفات.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $expenses->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection