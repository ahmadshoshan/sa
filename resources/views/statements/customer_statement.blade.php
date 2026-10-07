@extends('layouts.master')
@section('title', 'كشف حساب - ' . $customer->name)
@section('content')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd; }
    }
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2 class="mb-0">📄 كشف حساب العميل: {{ $customer->name }}</h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary">🖨️ طباعة</button>
            <a href="{{ route('statements.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
        </div>
    </div>

    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('statements.customer', $customer->id) }}" class="row g-2">
                <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
                <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
                <div class="col-md-3"><button class="btn btn-outline-primary">🔍 تصفية</button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center">
            <h6 class="text-muted">إجمالي عليه (مدين)</h6>
            <h4 class="mb-0 text-danger">{{ number_format($totals['debit'], 2) }}</h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center">
            <h6 class="text-muted">إجمالي له (دائن)</h6>
            <h4 class="mb-0 text-success">{{ number_format($totals['credit'], 2) }}</h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center">
            <h6 class="text-muted">الرصيد الحالي</h6>
            <h4 class="mb-0 {{ $customer->current_balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($customer->current_balance, 2) }}</h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center">
            <h6 class="text-muted">رصيد الكشف</h6>
            <h4 class="mb-0">{{ number_format($totals['balance'], 2) }}</h4>
        </div></div></div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-white"><h5 class="mb-0"> تفاصيل المعاملات</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th><th>البيان</th><th>المرجع</th>
                            <th class="text-end">مدين (عليه)</th>
                            <th class="text-end">دائن (له)</th>
                            <th class="text-end">الرصيد الجاري</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($display as $t)
                            <tr>
                                <td>{{ $t['date'] }}</td>
                                <td>
                                    @if($t['type']=='sale') <span class="badge bg-primary">فاتورة بيع</span>
                                    @elseif($t['type']=='sale_return') <span class="badge bg-info">مرتجع بيع</span>
                                    @elseif($t['type']=='receipt') <span class="badge bg-success">سند قبض</span>
                                    @else <span class="badge bg-secondary">رصيد افتتاحي</span> @endif
                                    {{ $t['label'] }}
                                </td>
                                <td><code>{{ $t['ref'] }}</code></td>
                                <td class="text-end text-danger">{{ $t['debit'] > 0 ? number_format($t['debit'], 2) : '-' }}</td>
                                <td class="text-end text-success">{{ $t['credit'] > 0 ? number_format($t['credit'], 2) : '-' }}</td>
                                <td class="text-end fw-bold">{{ number_format($t['running'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">لا توجد معاملات في هذه الفترة</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="3">الإجمالي</td>
                            <td class="text-end text-danger">{{ number_format($totals['debit'], 2) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['credit'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['balance'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection