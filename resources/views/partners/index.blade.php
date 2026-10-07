@extends('layouts.master')

@section('title', 'الشركاء')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>الشركاء</h4>

        <a href="{{ route('partners.create') }}" class="btn btn-primary">إضافة شريك</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الهاتف</th>
                            <th>نسبة الأرباح %</th>
                            <th>رأس المال المدفوع</th>
                            <th>المسحوبات</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td>{{ $partner->code }}</td>
                                <td>{{ $partner->name }}</td>
                                <td>{{ $partner->phone }}</td>
                                <td>{{ number_format((float) $partner->profit_share, 2) }}</td>
                                <td>{{ number_format((float) ($partner->paid_capitals_sum_amount ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($partner->withdrawals_sum_amount ?? 0), 2) }}</td>

                                <td>
                                    <a href="{{ route('partners.show', $partner) }}" class="btn btn-sm btn-info">عرض</a>
                                    <a href="{{ route('partners.statement', $partner) }}" class="btn btn-sm btn-outline-dark">كشف حساب</a>
                                    <a href="{{ route('partners.edit', $partner) }}" class="btn btn-sm btn-warning">تعديل</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">لا يوجد شركاء.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $partners->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection