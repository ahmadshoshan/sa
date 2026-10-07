@extends('layouts.master')
@section('title', 'تحصيل من الموردين')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>💰 تحصيل من الموردين (نحن دائنون لهم)</h2>
            <p class="text-muted mb-0">الموردون الذين لهم رصيد دائن لنا (مرتجعات شراء غير محصلة)</p>
        </div>
        <a href="{{ route('payables.index') }}" class="btn btn-outline-danger">
            📊 دفع للموردين
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th>
                            <th>اسم المورد</th>
                            <th>الهاتف</th>
                            <th class="text-end">المستحق لنا</th>
                            <th class="text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td><code>{{ $supplier->code }}</code></td>
                                <td><strong>{{ $supplier->name }}</strong></td>
                                <td>{{ $supplier->phone ?? '-' }}</td>
                                <td class="text-end text-success fw-bold fs-5">
                                    {{ number_format(abs($supplier->current_balance), 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('collections.collect-from-supplier', $supplier->id) }}" 
                                       class="btn btn-sm btn-success">
                                        💳 تحصيل مستحقاتنا
                                    </a>
                                    <a href="{{ route('statements.supplier', $supplier->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        📊 كشف حساب
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    ✅ لا توجد مستحقات لنا لدى الموردين حالياً
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