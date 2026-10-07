@extends('layouts.master')

@section('title', 'دفتر الأستاذ')

@section('content')

    <h4 class="mb-4">دفتر الأستاذ / كشف حساب</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('financial.ledger') }}">
                <div class="row">

                    <div class="col-md-4">
                        <label class="form-label">الحساب</label>
                        <select name="account_id" class="form-select">
                            <option value="">اختر الحساب</option>

                            @foreach($accounts as $accountItem)
                                <option value="{{ $accountItem->id }}" @selected(request('account_id') == $accountItem->id)>
                                    {{ $accountItem->code }} - {{ $accountItem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from" value="{{ $from }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to" value="{{ $to }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('financial.ledger') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    @if($account)

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>الرصيد الافتتاحي</h6>
                        <h4>{{ number_format((float) $opening, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>إجمالي المدين</h6>
                        <h4>{{ number_format((float) $totalDebit, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>إجمالي الدائن</h6>
                        <h4>{{ number_format((float) $totalCredit, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>الرصيد الختامي</h6>
                        <h4>{{ number_format((float) $closing, 2) }}</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-header">
                حركة الحساب: {{ $account->code }} - {{ $account->name }}
            </div>

            <div class="card-body">

                @php
                    $balance = (float) $opening;
                @endphp

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم القيد</th>
                            <th>الوصف</th>
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>الرصيد</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($lines as $line)
                            @php
                                $balance += (float) $line->debit - (float) $line->credit;
                            @endphp

                            <tr>
                                <td>{{ $line->entry?->entry_date?->format('Y-m-d') }}</td>
                                <td>{{ $line->entry?->entry_no }}</td>
                                <td>{{ $line->entry?->description }}/
                                    @if( $line->supplier)
                                        {{ $line->supplier->name }}
                                    @elseif($line->supplier)
                                        {{ $line->supplier->name }}
                                    @else
                                        -
                                    @endif
                                    
                                </td>
                                <td>{{ number_format((float) $line->debit, 2) }}</td>
                                <td>{{ number_format((float) $line->credit, 2) }}</td>
                                <td>{{ number_format((float) $balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    لا توجد حركات على هذا الحساب في الفترة المحددة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    @else

        <div class="alert alert-info">
            اختر حسابًا من القائمة لعرض دفتر الأستاذ الخاص به.
        </div>

    @endif

@endsection