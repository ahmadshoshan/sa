@extends('layouts.master')
@section('title', 'تحصيل المستحقات')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">💰 تحصيل المستحقات من العملاء</h2>
<div class="mt-2"><a href="{{ route('collections.suppliers') }}" class="btn btn-success">💰 تحصيل من الموردين (نحن دائنون لهم)</a></div>

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

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="opacity-75">إجمالي المستحقات</h6>
                    <h3 class="mb-0">{{ number_format($totalReceivables, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>عدد الفواتير المستحقة</h6>
                    <h3 class="mb-0">{{ $receivableInvoices->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>متوسط المستحق</h6>
                    <h3 class="mb-0">{{ $receivableInvoices->count() > 0 ? number_format($totalReceivables / $receivableInvoices->count(), 2) : 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">📋 فواتير البيع المستحقة</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>العميل</th>
                            <th class="text-end">الإجمالي</th>
                            <th class="text-end">المدفوع</th>
                            <th class="text-end text-danger">المتبقي</th>
                            <th class="text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivableInvoices as $invoice)
                            <tr>
                                <td><code>{{ $invoice->invoice_no }}</code></td>
                                <td>{{ $invoice->invoice_date }}</td>
                                <td><strong>{{ $invoice->customer?->name ?? '-' }}</strong></td>
                                <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($invoice->remaining_amount, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('collections.collect-form', $invoice->id) }}" class="btn btn-sm btn-success">💰 تحصيل</a>
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
</div>
@endsection