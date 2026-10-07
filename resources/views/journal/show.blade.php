@extends('layouts.master')

@section('title', 'عرض قيد يومية')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قيد يومية رقم: {{ $journalEntry->entry_no }}</h5>

            <a href="{{ route('journal.index') }}" class="btn btn-secondary btn-sm">
                رجوع
            </a>
        </div>

        <div class="card-body">
            <div class="row">

                <div class="col-md-3">
                    <strong>التاريخ:</strong><br>
                    {{ $journalEntry->entry_date?->format('Y-m-d') }}
                </div>

                <div class="col-md-6">
                    <strong>الوصف:</strong><br>
                    {{ $journalEntry->description }}
                </div>

                <div class="col-md-3">
                    <strong>المرجع:</strong><br>
                    {{ $journalEntry->ref_type }} / {{ $journalEntry->ref_id }}
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الحساب</th>
                        <th>الطرف</th>
                        <th>مدين</th>
                        <th>دائن</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($journalEntry->lines as $line)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $line->account?->code }} - {{ $line->account?->name }}
                            </td>

                            <td>
                                @if($line->customer)
                                    عميل: {{ $line->customer->name }}
                                @elseif($line->supplier)
                                    مورد: {{ $line->supplier->name }}
                                @elseif($line->product)
                                    صنف: {{ $line->product->name }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ number_format((float) $line->debit, 2) }}</td>
                            <td>{{ number_format((float) $line->credit, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="3">الإجمالي</th>
                        <th>{{ number_format((float) $journalEntry->lines->sum('debit'), 2) }}</th>
                        <th>{{ number_format((float) $journalEntry->lines->sum('credit'), 2) }}</th>
                    </tr>
                </tfoot>
            </table>

        </div>
    </div>

@endsection