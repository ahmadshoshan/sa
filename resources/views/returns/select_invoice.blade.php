@extends('layouts.master')
@section('title', $title)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $title }}</h2>
        <div>
            @if($type === 'sale')
                <a href="{{ route('returns.select-invoice', ['type' => 'purchase']) }}" class="btn btn-outline-primary">
                    🛒 إرجاع من فواتير الشراء
                </a>
                <a href="{{ route('sales-returns.create') }}" class="btn btn-outline-secondary">
                    ✏️ مرتجع يدوي
                </a>
            @else
                <a href="{{ route('returns.select-invoice', ['type' => 'sale']) }}" class="btn btn-outline-success">
                    💰 إرجاع من فواتير البيع
                </a>
                <a href="{{ route('purchase-returns.create') }}" class="btn btn-outline-secondary">
                    ✏️ مرتجع يدوي
                </a>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>{{ $type === 'sale' ? 'العميل' : 'المورد' }}</th>
                            <th class="text-end">عدد الأصناف</th>
                            <th class="text-end">الإجمالي</th>
                            <th class="text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td><code>{{ $invoice->invoice_no }}</code></td>
                                <td>{{ $invoice->invoice_date }}</td>
                                <td>
                                    <strong>{{ $type === 'sale' ? $invoice->customer?->name : $invoice->supplier?->name }}</strong>
                                </td>
                                <td class="text-end">{{ $invoice->items->count() }}</td>
                                <td class="text-end fw-bold">{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('returns.from-invoice', $invoice->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        🔄 اختيار للإرجاع
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    لا توجد فواتير يمكن إرجاعها
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