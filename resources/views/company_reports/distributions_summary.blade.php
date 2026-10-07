@extends('layouts.master')

@section('title', 'تقرير توزيعات الأرباح')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>تقرير توزيعات الأرباح</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>رقم التوزيع</th>
                            <th>الفترة من</th>
                            <th>الفترة إلى</th>
                            <th>صافي الربح</th>
                            <th>الاحتياطي</th>
                            <th>مبلغ التوزيع</th>
                            <th>إجمالي الأنصبة</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>عرض</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($distributions as $distribution)
                            @php
                                $finalSum = (float) ($distribution->final_sum ?? 0);
                                $paidSum = (float) ($distribution->paid_sum ?? 0);
                                $remainingSum = $finalSum - $paidSum;
                            @endphp

                            <tr>
                                <td>{{ $distribution->distribution_no }}</td>
                                <td>{{ $distribution->period_from?->format('Y-m-d') }}</td>
                                <td>{{ $distribution->period_to?->format('Y-m-d') }}</td>
                                <td>{{ number_format((float) $distribution->net_profit, 2) }}</td>
                                <td>{{ number_format((float) $distribution->reserve_amount, 2) }}</td>
                                <td>{{ number_format((float) $distribution->distribute_amount, 2) }}</td>
                                <td>{{ number_format($finalSum, 2) }}</td>
                                <td>{{ number_format($paidSum, 2) }}</td>
                                <td>{{ number_format($remainingSum, 2) }}</td>

                                <td>
                                    @if($distribution->status === 'posted')
                                        <span class="badge bg-success">معتمد</span>
                                    @else
                                        <span class="badge bg-warning">مسودة</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('distributions.show', $distribution) }}" class="btn btn-sm btn-info">
                                        عرض
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">لا توجد توزيعات أرباح.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $distributions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection