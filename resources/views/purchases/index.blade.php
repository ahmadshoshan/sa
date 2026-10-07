@extends('layouts.master')

@section('title', 'فواتير الشراء')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>فواتير الشراء</h4>

        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            إنشاء فاتورة شراء
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>المورد</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                           <tr
                         @if($invoice->status === 'returned')
                           style="mix-blend-mode: difference;"     
                              @endif
                      >
                            <td>{{ $invoice->id }}</td>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ $invoice->supplier?->name }}</td>
                            <td>{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->remaining_amount, 2) }}</td>

                            <td>
                                @if($invoice->status === 'posted')
                                    <span class="badge bg-success">مرحّلة</span>
                                @elseif($invoice->status === 'draft')
                                    <span class="badge bg-warning">مسودة</span>
                                @elseif($invoice->status === 'cancelled')
                                    <span class="badge bg-danger">ملغاة</span>
                                    
                                @elseif($invoice->status === 'returned')
                                    <span class="badge bg-danger">مرتجع</span>
                                @else

                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('purchases.show', $invoice) }}" class="btn btn-sm btn-info">
                                    عرض
                                </a>
                                
  @if($invoice->status !== 'returned')
                              
                                 <a href="{{ route('returns.create-from-invoice', $invoice->id) }}" class="btn btn-sm btn-warning" title="إرتجاع الفاتورة">
                                     🔄 إرتجاع
                                    </a>
                                    @endif


                             
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                لا توجد فواتير شراء حتى الآن.
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