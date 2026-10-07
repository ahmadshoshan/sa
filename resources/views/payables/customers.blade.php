@extends('layouts.master')
@section('title', 'مستحقات العملاء')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>💰 مستحقات العملاء (نحن مدينون لهم)</h2>
            <p class="text-muted mb-0">العملاء الذين لهم رصيد دائن (مرتجعات غير محصلة أو مدفوعات زائدة)</p>
        </div>
        <a href="{{ route('collections.index') }}" class="btn btn-outline-primary">
            📊 تحصيل من العملاء
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
                            <th>اسم العميل</th>
                            <th>الهاتف</th>
                            <th class="text-end">المستحق له</th>
                            <th class="text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td><code>{{ $customer->code }}</code></td>
                                <td><strong>{{ $customer->name }}</strong></td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold fs-5">
                                    {{ number_format(abs($customer->current_balance), 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('payables.pay-customer', $customer->id) }}" 
                                       class="btn btn-sm btn-danger">
                                        💳 دفع مستحقاته
                                    </a>
                                    <a href="{{ route('statements.customer', $customer->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        📊 كشف حساب
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    ✅ لا توجد مستحقات للعملاء حالياً
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