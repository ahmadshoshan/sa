@extends('layouts.master')

@section('title', 'تقرير الشركاء')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>تقرير الشركاء</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الشريك</th>
                            <th>نسبة الأرباح %</th>
                            <th>رأس المال</th>
                            <th>المسحوبات</th>
                            <th>الأرباح المستحقة</th>
                            <th>الأرباح المدفوعة</th>
                            <th>الأرباح المتبقية</th>
                            <th>صافي الحقوق</th>
                            <th>كشف حساب</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td>{{ $partner->code }}</td>
                                <td>{{ $partner->name }}</td>
                                <td>{{ number_format((float) $partner->profit_share, 2) }}</td>
                                <td>{{ number_format((float) ($partner->capital_sum_amount ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($partner->withdrawals_sum_amount ?? 0), 2) }}</td>
                                <td>{{ number_format((float) $partner->allocated_profit, 2) }}</td>
                                <td>{{ number_format((float) $partner->paid_profit, 2) }}</td>
                                <td>{{ number_format((float) $partner->remaining_profit, 2) }}</td>
                                <td>{{ number_format((float) $partner->net_equity, 2) }}</td>

                                <td>
                                    <a href="{{ route('partners.statement', $partner) }}" class="btn btn-sm btn-outline-dark">
                                        كشف حساب
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">لا يوجد شركاء.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection