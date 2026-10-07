@extends('layouts.master')

@section('title', 'عرض توزيع الأرباح')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">توزيع الأرباح رقم: {{ $distribution->distribution_no }}</h5>

            <div class="d-flex gap-2 flex-wrap">
                @if($distribution->status === 'draft')
                    <form method="POST" action="{{ route('distributions.approve', $distribution) }}"
                          onsubmit="return confirm('هل أنت متأكد من اعتماد هذا التوزيع؟');">
                        @csrf
                        <button class="btn btn-success btn-sm">اعتماد وترحيل</button>
                    </form>
                @endif

                <a href="{{ route('distributions.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>الفترة:</strong><br>
                    {{ $distribution->period_from?->format('Y-m-d') }} إلى {{ $distribution->period_to?->format('Y-m-d') }}
                </div>

                <div class="col-md-3">
                    <strong>صافي الربح:</strong><br>
                    {{ number_format((float) $distribution->net_profit, 2) }}
                </div>

                <div class="col-md-3">
                    <strong>الاحتياطي:</strong><br>
                    {{ number_format((float) $distribution->reserve_amount, 2) }}
                </div>

                <div class="col-md-3">
                    <strong>مبلغ التوزيع:</strong><br>
                    {{ number_format((float) $distribution->distribute_amount, 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">أنصبة الشركاء</div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>الشريك</th>
                            <th>النسبة %</th>
                            <th>النصيب</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>السداد</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($distribution->items as $item)
                            <tr>
                                <td>{{ $item->partner?->name }}</td>
                                <td>{{ number_format((float) $item->profit_share, 2) }}</td>
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

                                <td style="min-width: 260px;">
                                    @if($distribution->status === 'posted' && (float) $item->remaining_amount > 0)
                                        <form method="POST" action="{{ route('distributions.items.pay', $item) }}" class="row g-2">
                                            @csrf

                                            <div class="col-4">
                                                <input type="number"
                                                       name="amount"
                                                       step="1"
                                                       min="0.01"
                                                       max="{{ $item->remaining_amount }}"
                                                       value="{{ $item->remaining_amount }}"
                                                       class="form-control form-control-sm"
                                                       required>
                                            </div>

                                            <div class="col-4">
                                                <select name="payment_method" class="form-select form-select-sm" required>
                                                    <option value="cash">نقدي</option>
                                                    <option value="card">بطاقة</option>
                                                    <option value="bank_transfer">تحويل</option>
                                                    <option value="cheque">شيك</option>
                                                </select>
                                            </div>

                                            <div class="col-4">
                                                <button class="btn btn-success btn-sm w-100">دفع</button>
                                            </div>

                                            <div class="col-12">
                                                <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection