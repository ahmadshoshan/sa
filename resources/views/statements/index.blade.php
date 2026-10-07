@extends('layouts.master')
@section('title', 'كشوف الحسابات')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">📄 كشوف حسابات العملاء والموردين</h2>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'customers' ? 'active' : '' }}" href="{{ route('statements.index', ['tab' => 'customers']) }}">👥 العملاء</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'suppliers' ? 'active' : '' }}" href="{{ route('statements.index', ['tab' => 'suppliers']) }}">🏭 الموردون</a>
        </li>
    </ul>

    @if($tab == 'customers')
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>الكود</th><th>الاسم</th><th>الهاتف</th>
                                <th class="text-end">المبيعات</th>
                                <th class="text-end">المرتجعات</th>
                                <th class="text-end">المقبوضات</th>
                                <th class="text-end">الرصيد الحالي</th>
                                <th class="text-center">كشف الحساب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $c)
                                <tr>
                                    <td><code>{{ $c->code }}</code></td>
                                    <td><strong>{{ $c->name }}</strong></td>
                                    <td>{{ $c->phone ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($c->sales_total, 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($c->returns_total, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($c->payments_total, 2) }}</td>
                                    <td class="text-end fw-bold {{ $c->current_balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($c->current_balance, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('statements.customer', $c->id) }}" class="btn btn-sm btn-outline-primary">📄 كشف حساب</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">لا يوجد عملاء</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>الكود</th><th>الاسم</th><th>الهاتف</th>
                                <th class="text-end">المشتريات</th>
                                <th class="text-end">المرتجعات</th>
                                <th class="text-end">المدفوعات</th>
                                <th class="text-end">الرصيد الحالي</th>
                                <th class="text-center">كشف الحساب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $s)
                                <tr>
                                    <td><code>{{ $s->code }}</code></td>
                                    <td><strong>{{ $s->name }}</strong></td>
                                    <td>{{ $s->phone ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($s->purchases_total, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($s->returns_total, 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($s->payments_total, 2) }}</td>
                                    <td class="text-end fw-bold {{ $s->current_balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($s->current_balance, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('statements.supplier', $s->id) }}" class="btn btn-sm btn-outline-primary">📄 كشف حساب</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">لا يوجد موردون</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection