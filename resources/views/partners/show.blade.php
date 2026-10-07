@extends('layouts.master')

@section('title', 'ملف الشريك')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">ملف الشريك: {{ $partner->name }}</h5>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('partners.capitals.create', $partner) }}" class="btn btn-success btn-sm">إضافة رأس مال</a>
                <a href="{{ route('partners.withdrawals.create', $partner) }}" class="btn btn-warning btn-sm">إضافة مسحوبات</a>
                <a href="{{ route('partners.edit', $partner) }}" class="btn btn-outline-primary btn-sm">تعديل</a>
                <a href="{{ route('partners.statement', $partner) }}" class="btn btn-outline-dark btn-sm">كشف حساب</a>
                <a href="{{ route('partners.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>الكود:</strong><br>{{ $partner->code }}
                </div>

                <div class="col-md-3">
                    <strong>الهاتف:</strong><br>{{ $partner->phone ?? '-' }}
                </div>

                <div class="col-md-3">
                    <strong>نسبة الأرباح:</strong><br>{{ number_format((float) $partner->profit_share, 2) }}%
                </div>

                <div class="col-md-3">
                    <strong>نسبة رأس المال:</strong><br>{{ number_format((float) $partner->capital_share, 2) }}%
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6>إجمالي رأس المال المدفوع</h6>
                    <h4>{{ number_format($totalCapital, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6>إجمالي المسحوبات</h6>
                    <h4>{{ number_format($totalWithdrawals, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6>صافي رأس المال بعد المسحوبات</h6>
                    <h4>{{ number_format($totalCapital - $totalWithdrawals, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">رأس المال</div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>الحالة</th>
                            <th>البيان</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($partner->capitals as $capital)
                            <tr>
                                <td>{{ $capital->contribution_date?->format('Y-m-d') }}</td>

                                <td>
                                    @if($capital->contribution_type === 'cash')
                                        نقدي
                                    @elseif($capital->contribution_type === 'inventory')
                                        بضاعة
                                    @elseif($capital->contribution_type === 'asset')
                                        أصل
                                    @else
                                        أخرى
                                    @endif
                                </td>

                                <td>{{ number_format((float) $capital->amount, 2) }}</td>
                                <td>{{ $capital->payment_method ?? '-' }}</td>

                                <td>
                                    @if($capital->status === 'paid')
                                        <span class="badge bg-success">مدفوع</span>
                                    @else
                                        <span class="badge bg-warning">معلق</span>
                                    @endif
                                </td>

                                <td>{{ $capital->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">لا توجد مساهمات رأس مال.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">المسحوبات</div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($partner->withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->withdrawal_date?->format('Y-m-d') }}</td>
                                <td>{{ number_format((float) $withdrawal->amount, 2) }}</td>
                                <td>{{ $withdrawal->payment_method }}</td>
                                <td>{{ $withdrawal->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">لا توجد مسحوبات.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection