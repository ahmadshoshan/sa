@extends('layouts.master')
@section('title', 'المرتجعات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>🔄 المرتجعات</h2>
            <p class="text-muted mb-0">جميع مرتجعات البيع والشراء</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('returns.create-sale') }}" class="btn btn-success">
                ↩️ مرتجع بيع جديد
            </a>
            <a href="{{ route('returns.create-purchase') }}" class="btn btn-warning">
                ↪️ مرتجع شراء جديد
            </a>
            <a href="{{ route('returns.select-invoice') }}" class="btn btn-info">
                📄 إرجاع من فاتورة
            </a>
        </div>
    </div>

    {{-- إحصائيات --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">إجمالي المرتجعات</div>
                    <div class="fs-3 fw-bold">{{ $stats['total_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="text-muted small">مرتجعات البيع</div>
                    <div class="fs-3 fw-bold text-success">{{ $stats['sale_returns_count'] }}</div>
                    <div class="small text-success">{{ number_format($stats['sale_returns_total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body text-center">
                    <div class="text-muted small">مرتجعات الشراء</div>
                    <div class="fs-3 fw-bold text-warning">{{ $stats['purchase_returns_count'] }}</div>
                    <div class="small text-warning">{{ number_format($stats['purchase_returns_total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100" style="background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white;">
                <div class="card-body text-center">
                    <div class="small" style="opacity: 0.9;">إجمالي قيمة المرتجعات</div>
                    <div class="fs-3 fw-bold">{{ number_format($stats['sale_returns_total'] + $stats['purchase_returns_total'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- الفلاتر --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">النوع</label>
                    <select name="type" class="form-select">
                        <option value="">الكل</option>
                        <option value="sale_return" {{ $type === 'sale_return' ? 'selected' : '' }}>مرتجعات البيع</option>
                        <option value="purchase_return" {{ $type === 'purchase_return' ? 'selected' : '' }}>مرتجعات الشراء</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="رقم المرتجع أو اسم..." class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">🔍 بحث</button>
                    <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary w-100 mt-2">إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول المرتجعات --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم المرتجع</th>
                            <th>النوع</th>
                            <th>التاريخ</th>
                            <th>{{ $type === 'purchase_return' ? 'المورد' : 'العميل/المورد' }}</th>
                            <th>المخزن</th>
                            <th class="text-end">عدد الأصناف</th>
                            <th class="text-end">الإجمالي</th>
                            <th>أنشأه</th>
                            <th class="text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td><code>{{ $return->invoice_no }}</code></td>
                                <td>
                                    @if($return->type === 'sale_return')
                                        <span class="badge bg-success">↩️ مرتجع بيع</span>
                                    @else
                                        <span class="badge bg-warning text-dark">↪️ مرتجع شراء</span>
                                    @endif
                                </td>
                                <td>{{ $return->invoice_date }}</td>
                                <td>
                                    @if($return->type === 'sale_return')
                                        👤 {{ $return->customer?->name ?? '-' }}
                                    @else
                                        🏭 {{ $return->supplier?->name ?? '-' }}
                                    @endif
                                </td>
                                <td>{{ $return->warehouse?->name ?? '-' }}</td>
                                <td class="text-end">{{ $return->items->count() }}</td>
                                <td class="text-end fw-bold">{{ number_format($return->total, 2) }}</td>
                                <td>{{ $return->user?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('returns.show', $return) }}" class="btn btn-sm btn-outline-primary">
                                        👁️ عرض
                                    </a>
                                    <a href="{{ route('pdf.invoice', $return) }}" target="_blank" class="btn btn-sm btn-pdf">
                                        📄 PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    لا توجد مرتجعات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($returns->hasPages())
            <div class="card-footer">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection