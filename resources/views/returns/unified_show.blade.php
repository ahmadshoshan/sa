@extends('layouts.master')
@section('title', 'مرتجع ' . $return->invoice_no)
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            @if($return->type === 'sale_return')
                ↩️ مرتجع بيع: {{ $return->invoice_no }}
            @else
                ↪️ مرتجع شراء: {{ $return->invoice_no }}
            @endif
        </h2>
        <div>
            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
            <a href="{{ route('pdf.invoice', $return) }}" target="_blank" class="btn btn-pdf">📄 طباعة PDF</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- معلومات المرتجع --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, {{ $return->type === 'sale_return' ? '#198754 0%, #0d6efd' : '#ffc107 0%, #fd7e14' }} 100%); color: white;">
            <div class="row">
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">رقم المرتجع</div>
                    <div style="font-size: 20px; font-weight: bold;">{{ $return->invoice_no }}</div>
                </div>
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">{{ $return->type === 'sale_return' ? 'العميل' : 'المورد' }}</div>
                    <div style="font-size: 20px; font-weight: bold;">
                        {{ $return->type === 'sale_return' ? $return->customer?->name : $return->supplier?->name }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="opacity: 0.9; font-size: 13px;">التاريخ</div>
                    <div style="font-size: 20px;">{{ $return->invoice_date }}</div>
                </div>
                <div class="col-md-3 text-end">
                    <div style="opacity: 0.9; font-size: 13px;">الإجمالي</div>
                    <div style="font-size: 28px; font-weight: bold;">{{ number_format($return->total, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- الأصناف --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">📦 الأصناف المرتجعة ({{ $return->items->count() }})</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th>الكود</th>
                        <th class="text-end">الكمية</th>
                        <th class="text-end">السعر</th>
                        <th class="text-end">الخصم</th>
                        <th class="text-end">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($return->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item->product?->name ?? '-' }}</strong></td>
                            <td><code>{{ $item->product?->code ?? '-' }}</code></td>
                            <td class="text-end">{{ number_format($item->quantity, 3) }}</td>
                            <td class="text-end">{{ number_format($item->price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end fw-bold">الإجمالي:</td>
                        <td class="text-end fw-bold fs-5">{{ number_format($return->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ملاحظات --}}
    @if($return->notes)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6>📝 ملاحظات:</h6>
                <p class="mb-0">{{ $return->notes }}</p>
            </div>
        </div>
    @endif

    {{-- معلومات إضافية --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>المخزن:</strong> {{ $return->warehouse?->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>أنشأه:</strong> {{ $return->user?->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>تاريخ الإنشاء:</strong> {{ $return->created_at?->format('Y-m-d H:i') ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection