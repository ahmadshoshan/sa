@extends('layouts.master')
@section('title', 'إدارة الأموال')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">💰 إدارة الأموال والنقدية</h2>
        <a href="{{ route('fund.transfer-form') }}" class="btn btn-primary">
            🔄 تحويل نقدي
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white h-100 shadow">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">💵 الصندوق النقدي</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($balances['cash'], 2) }}</h3>
                    <small>حساب 1001</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100 shadow">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">🏦 البنك</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($balances['bank'], 2) }}</h3>
                    <small>حساب 1002</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100 shadow">
                <div class="card-body">
                    <h6 class="mb-1 opacity-75">💳 الكاش / الإنستا</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($balances['insta'], 2) }}</h3>
                    <small>حساب 1003</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100 shadow">
                <div class="card-body">
                    <h6 class="mb-1">📋 الآجل لنا (ذمم)</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($balances['receivables'], 2) }}</h3>
                    <small>حساب 1101</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-danger shadow">
                <div class="card-body">
                    <h6 class="text-danger mb-1">🔴 الآجل علينا (موردين)</h6>
                    <h3 class="mb-0 text-danger fw-bold">{{ number_format($balances['payables'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success shadow">
                <div class="card-body">
                    <h6 class="text-success mb-1">💰 إجمالي النقدية</h6>
                    <h3 class="mb-0 text-success fw-bold">{{ number_format($balances['cash'] + $balances['bank'] + $balances['insta'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary shadow">
                <div class="card-body">
                    <h6 class="text-primary mb-1">📈 صافي المركز النقدي</h6>
                    <h3 class="mb-0 text-primary fw-bold">{{ number_format(($balances['cash'] + $balances['bank'] + $balances['insta'] + $balances['receivables']) - $balances['payables'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow">
        <div class="card-header bg-white"><h5 class="mb-0">📊 الحسابات المالية</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th><th>اسم الحساب</th><th>النوع</th>
                            <th class="text-end">الرصيد</th><th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                            @php $balance = app(\App\Services\FundService::class)->getAccountBalance($account->id); @endphp
                            <tr>
                                <td><code>{{ $account->code }}</code></td>
                                <td><strong>{{ $account->name }}</strong></td>
                                <td>
                                    @switch($account->type)
                                        @case('asset') <span class="badge bg-success">أصول</span> @break
                                        @case('liability') <span class="badge bg-danger">خصوم</span> @break
                                        @case('equity') <span class="badge bg-primary">حقوق ملكية</span> @break
                                        @case('revenue') <span class="badge bg-info">إيرادات</span> @break
                                        @case('expense') <span class="badge bg-warning text-dark">مصروفات</span> @break
                                        @default <span class="badge bg-secondary">{{ $account->type }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($balance, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('fund.account-details', $account->id) }}" class="btn btn-sm btn-outline-primary">👁️ التفاصيل</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🔄 آخر التحويلات النقدية</h5>
            <a href="{{ route('fund.transfer-form') }}" class="btn btn-sm btn-primary">➕ تحويل جديد</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>رقم العملية</th><th>التاريخ</th><th>من</th><th>إلى</th>
                            <th class="text-end">المبلغ</th><th>المستخدم</th><th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td><code>{{ $transfer->transfer_no }}</code></td>
                                <td>{{ $transfer->transfer_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-danger">{{ $transfer->fromAccount->name }}</span></td>
                                <td><span class="badge bg-success">{{ $transfer->toAccount->name }}</span></td>
                                <td class="text-end fw-bold">{{ number_format($transfer->amount, 2) }}</td>
                                <td>{{ $transfer->user?->name ?? '-' }}</td>
                                <td>{{ $transfer->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">لا توجد تحويلات بعد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection