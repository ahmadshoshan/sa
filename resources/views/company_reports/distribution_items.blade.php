@extends('layouts.master')

@section('title', 'تقرير أنصبة الشركاء')

@section('content')

    <h4 class="mb-4">تقرير أنصبة الشركاء</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.distributions-items') }}">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">الشريك</label>
                        <select name="partner_id" class="form-select">
                            <option value="">كل الشركاء</option>

                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" @selected(request('partner_id') == $partner->id)>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" @selected(request('status') == 'pending')>غير مدفوع</option>
                            <option value="partial" @selected(request('status') == 'partial')>جزئي</option>
                            <option value="paid" @selected(request('status') == 'paid')>مدفوع</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الفترة من</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الفترة إلى</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('reports.distributions-items') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>رقم التوزيع</th>
                            <th>الشريك</th>
                            <th>الفترة</th>
                            <th>النصيب</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->distribution?->distribution_no }}</td>
                                <td>{{ $item->partner?->name }}</td>

                                <td>
                                    {{ $item->distribution?->period_from?->format('Y-m-d') }}
                                    -
                                    {{ $item->distribution?->period_to?->format('Y-m-d') }}
                                </td>

                                <td>{{ number_format((float) $item->final_amount, 2) }}</td>
                                <td>{{ number_format((float) $item->paid_amount, 2) }}</td>
                                <td>{{ number_format((float) $item->remaining_amount, 2) }}</td>

                                <td>
                                    @if($item->status === 'paid')
                                        <span class="badge bg-success">مدفوع</span>
                                    @elseif($item->status === 'partial')
                                        <span class="badge bg-warning">جزئي</span>
                                    @else
                                        <span class="badge bg-secondary">غير مدفوع</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">لا توجد نتائج.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection