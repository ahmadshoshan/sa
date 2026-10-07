@extends('layouts.master')

@section('title', 'أعمار الديون')

@section('content')

    <h4 class="mb-4">أعمار ديون العملاء</h4>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>العميل</th><th>الرصيد المستحق</th><th>الحالة</th></tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ number_format((float) $customer->current_balance, 2) }}</td>
                                <td>
                                    @if($customer->current_balance > $customer->credit_limit && $customer->credit_limit > 0)
                                        <span class="badge bg-danger">تجاوز الحد</span>
                                    @else
                                        <span class="badge bg-success">طبيعي</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">لا توجد ديون. ✅</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection