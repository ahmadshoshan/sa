@extends('layouts.master')

@section('title', 'كشف حساب - ' . $customer->name)

@section('content')
<div class="container-fluid py-4">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📊 كشف حساب العميل: {{ $customer->name }}</h2>
            <p class="text-muted mb-0">كود: {{ $customer->code }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('statements.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
            <a href="{{ route('pdf.customer-statement', $customer->id) }}" target="_blank" class="btn btn-pdf">📄 طباعة PDF</a>
        </div>
    </div>

    {{-- Flash Messages --}}
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

    {{-- ملخص العميل --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white;">
            <div class="row">
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">الكود</div>
                    <div style="font-size: 18px; font-weight: bold;">{{ $customer->code }}</div>
                </div>
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">الهاتف</div>
                    <div style="font-size: 18px;">{{ $customer->phone ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">الرصيد الافتتاحي</div>
                    <div style="font-size: 18px;">{{ number_format($customer->opening_balance, 2) }}</div>
                </div>
                <div class="col-md-3 text-end">
                    <div style="opacity: 0.9; font-size: 13px;">الرصيد الحالي</div>
                    <div style="font-size: 26px; font-weight: bold;">
                        {{ number_format($customer->current_balance, 2) }}
                    </div>
                    @if($customer->current_balance > 0)
                        <small style="opacity: 0.8;">(مدين لنا)</small>
                    @elseif($customer->current_balance < 0)
                        <small style="opacity: 0.8;">(دائن لنا)</small>
                    @else
                        <small style="opacity: 0.8;">(متوازن)</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- فلترة بالتاريخ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">🔍 بحث</button>
                    <a href="{{ route('statements.customer', $customer->id) }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    {{-- إحصائيات --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-danger">
                <div class="card-body text-center">
                    <div class="text-muted small">إجمالي المدين</div>
                    <div class="fs-4 fw-bold text-danger">{{ number_format($totalDebit ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="text-muted small">إجمالي الدائن</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($totalCredit ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <div class="text-muted small">عدد الحركات</div>
                    <div class="fs-4 fw-bold text-primary">{{ $transactions->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white;">
                <div class="card-body text-center">
                    <div class="small" style="opacity: 0.9;">الرصيد النهائي</div>
                    <div class="fs-4 fw-bold">{{ number_format($customer->current_balance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- جدول الحركات --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📋 الحركات التفصيلية</h5>
            <span class="badge bg-secondary">{{ $transactions->count() }} حركة</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th>المرجع</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trans)
                            <tr>
                                <td>{{ $trans['date'] }}</td>
                                <td>
                                    <span class="{{ $trans['class'] ?? '' }}">
                                        {{ $trans['icon'] ?? '' }} {{ $trans['type'] }}
                                    </span>
                                    @if(isset($trans['method']) && $trans['method'])
                                        <br><small class="text-muted">💳 {{ $trans['method'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $trans['reference'] }}</code>
                                    @if(isset($trans['notes']) && $trans['notes'])
                                        <br><small class="text-muted">{{ $trans['notes'] }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(($trans['debit'] ?? 0) > 0)
                                        <span class="text-danger fw-bold">{{ number_format($trans['debit'], 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(($trans['credit'] ?? 0) > 0)
                                        <span class="text-success fw-bold">{{ number_format($trans['credit'], 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    @if(($trans['balance'] ?? 0) > 0)
                                        <span class="text-danger">{{ number_format($trans['balance'], 2) }}</span>
                                    @elseif(($trans['balance'] ?? 0) < 0)
                                        <span class="text-success">{{ number_format($trans['balance'], 2) }}</span>
                                    @else
                                        <span class="text-muted">0.00</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    لا توجد حركات في هذه الفترة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-5">الإجمالي:</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($totalDebit ?? 0, 2) }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($totalCredit ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($customer->current_balance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection