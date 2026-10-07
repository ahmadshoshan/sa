@extends('layouts.master')
@section('title', 'كشف حساب - ' . $supplier->name)
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 كشف حساب المورد: {{ $supplier->name }}</h2>
        <div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
            <a href="{{ route('pdf.supplier-statement', $supplier->id) }}" target="_blank" class="btn btn-pdf">
                📄 طباعة PDF
            </a>
        </div>
    </div>

    {{-- ملخص المورد --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white;">
            <div class="row">
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">الكود</div>
                    <div style="font-size: 18px; font-weight: bold;">{{ $supplier->code }}</div>
                </div>
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">الهاتف</div>
                    <div style="font-size: 18px;">{{ $supplier->phone ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">الرصيد الافتتاحي</div>
                    <div style="font-size: 18px;">{{ number_format($supplier->opening_balance, 2) }}</div>
                </div>
                <div class="col-md-3 text-end">
                    <div style="opacity: 0.9; font-size: 13px;">الرصيد الحالي</div>
                    <div style="font-size: 24px; font-weight: bold;">
                        {{ number_format($supplier->current_balance, 2) }}
                        @if($supplier->current_balance > 0)
                            <small style="opacity: 0.8;">(مدين لنا عليه)</small>
                        @elseif($supplier->current_balance < 0)
                            <small style="opacity: 0.8;">(دائن لنا)</small>
                        @endif
                    </div>
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
                    <a href="{{ route('statements.supplier', $supplier->id) }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول الحركات --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th>المرجع</th>
                            <th class="text-end">مدين (علينا)</th>
                            <th class="text-end">دائن (لنا)</th>
                            <th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trans)
                            <tr>
                                <td>{{ $trans['date'] }}</td>
                                <td>
                                    <span class="{{ $trans['class'] }}">
                                        {{ $trans['icon'] }} {{ $trans['type'] }}
                                    </span>
                                    @if(isset($trans['method']))
                                        <br><small class="text-muted">{{ $trans['method'] }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $trans['reference'] }}</code></td>
                                <td class="text-end {{ $trans['debit'] > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $trans['debit'] > 0 ? number_format($trans['debit'], 2) : '-' }}
                                </td>
                                <td class="text-end {{ $trans['credit'] > 0 ? 'text-success fw-bold' : '' }}">
                                    {{ $trans['credit'] > 0 ? number_format($trans['credit'], 2) : '-' }}
                                </td>
                                <td class="text-end fw-bold {{ $trans['balance'] > 0 ? 'text-danger' : ($trans['balance'] < 0 ? 'text-success' : '') }}">
                                    {{ number_format($trans['balance'], 2) }}
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
                </table>
            </div>
        </div>
    </div>
</div>
@endsection