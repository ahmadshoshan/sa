@extends('layouts.master')

@section('title', 'مرتجعات الشراء')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>مرتجعات الشراء</h4>

        <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary">
            إنشاء مرتجع شراء
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
                        <th>المورد</th>
                        <th>الإجمالي</th>
                        <th>المستلم</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->id }}</td>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ $invoice->supplier?->name }}</td>
                            <td>{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->remaining_amount, 2) }}</td>

                            <td>
                                <span class="badge bg-success">مرحّل</span>
                            </td>

                            <td>
                                <a href="{{ route('purchase-returns.show', $invoice) }}" class="btn btn-sm btn-info">
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                لا توجد مرتجعات شراء حتى الآن.
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