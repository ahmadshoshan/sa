@extends('layouts.master')

@section('title', 'مرتجعات البيع')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>مرتجعات البيع</h4>

        <a href="{{ route('sales-returns.create') }}" class="btn btn-primary">
            إنشاء مرتجع بيع
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم المرتجع</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>المسترد</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->id }}</td>
                            <td>{{ $invoice->invoice_no }}<br>{{ $invoice->notes }}</td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ $invoice->customer?->name }}</td>
                            <td>{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->remaining_amount, 2) }}</td>

                            <td>
                                @if($invoice->status === 'posted')
                                    <span class="badge bg-success">مرحّل</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('sales-returns.show', $invoice) }}" class="btn btn-sm btn-info">
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                لا توجد مرتجعات بيع حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $invoices->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection