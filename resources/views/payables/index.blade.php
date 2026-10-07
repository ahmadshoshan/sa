@extends('layouts.master')
@section('title', 'دفع المستحقات')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">💳 دفع المستحقات</h2>
<div class="mt-2"><a href="{{ route('payables.customers') }}" class="btn btn-info">💰 مستحقات العملاء (نحن مدينون لهم)</a></div>

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

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="opacity-75">إجمالي المستحقات</h6>
                    <h3 class="mb-0">{{ number_format($totalExpenses + $totalInvoices, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>مصروفات غير مدفوعة</h6>
                    <h3 class="mb-0">{{ number_format($totalExpenses, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>فواتير مشتريات مستحقة</h6>
                    <h3 class="mb-0">{{ number_format($totalInvoices, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>دفع مرتب جديد</h6>
                    <a href="{{ route('payables.salary-form') }}" class="btn btn-light btn-sm mt-2">➕ دفع مرتب</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'expenses' ? 'active' : '' }}" href="{{ route('payables.index', ['tab' => 'expenses']) }}">
                💸 المصروفات ({{ $payableExpenses->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'invoices' ? 'active' : '' }}" href="{{ route('payables.index', ['tab' => 'invoices']) }}">
                🛒 فواتير المشتريات ({{ $payableInvoices->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('payables.salary-form') }}">
                👷 دفع مرتب
            </a>
        </li>
    </ul>

    {{-- Tab: Expenses --}}
    @if($tab == 'expenses')
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">💸 المصروفات غير المدفوعة</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم المصروف</th>
                                <th>التاريخ</th>
                                <th>التصنيف</th>
                                <th>الوصف</th>
                                <th class="text-end">الإجمالي</th>
                                <th class="text-end">المدفوع</th>
                                <th class="text-end text-danger">المتبقي</th>
                                <th class="text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payableExpenses as $expense)
                                <tr>
                                    <td><code>{{ $expense->expense_no }}</code></td>
                                    <td>{{ $expense->expense_date }}</td>
                                    <td><span class="badge bg-secondary">{{ $expense->category?->name ?? '-' }}</span></td>
                                    <td>{{ $expense->description ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($expense->total, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($expense->paid_amount, 2) }}</td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($expense->remaining_amount, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payables.pay-expense-form', $expense->id) }}" class="btn btn-sm btn-primary">💳 دفع</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">لا توجد مصروفات غير مدفوعة ✅</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab: Invoices --}}
    @if($tab == 'invoices')
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">🛒 فواتير المشتريات المستحقة</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>المورد</th>
                                <th class="text-end">الإجمالي</th>
                                <th class="text-end">المدفوع</th>
                                <th class="text-end text-danger">المتبقي</th>
                                <th class="text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payableInvoices as $invoice)
                                <tr>
                                    <td><code>{{ $invoice->invoice_no }}</code></td>
                                    <td>{{ $invoice->invoice_date }}</td>
                                    <td><strong>{{ $invoice->supplier?->name ?? '-' }}</strong></td>
                                    <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($invoice->remaining_amount, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payables.pay-invoice-form', $invoice->id) }}" class="btn btn-sm btn-primary">💳 دفع</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد فواتير مستحقة ✅</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection