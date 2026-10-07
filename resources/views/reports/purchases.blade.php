@extends('layouts.master')

@section('title', 'تقرير المشتريات')

@section('content')

    <h4 class="mb-4">تقرير المشتريات</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.purchases') }}">
                <div class="row">

                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">المورد</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">كل الموردين</option>

                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('reports.purchases') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>عدد الفواتير</h6>
                    <h4>{{ $summary->invoices_count }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>الإجمالي</h6>
                    <h4>{{ number_format((float) $summary->total_sum, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>المدفوع</h6>
                    <h4>{{ number_format((float) $summary->paid_sum, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>المتبقي</h6>
                    <h4>{{ number_format((float) $summary->remaining_sum, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>المورد</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>عرض</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ $invoice->supplier?->name }}</td>
                            <td>{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->remaining_amount, 2) }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $invoice) }}" class="btn btn-sm btn-info">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد نتائج.</td>
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