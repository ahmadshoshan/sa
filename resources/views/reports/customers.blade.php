@extends('layouts.master')

@section('title', 'أرصدة العملاء')

@section('content')

    <h4 class="mb-4">أرصدة العملاء</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.customers') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               class="form-control"
                               placeholder="اسم العميل / الكود / الهاتف">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">بحث</button>
                        <a href="{{ route('reports.customers') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>الرصيد الافتتاحي</th>
                        <th>الرصيد الحالي</th>
                        <th>الحالة</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->code }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ number_format((float) $customer->opening_balance, 2) }}</td>
                            <td>{{ number_format((float) $customer->current_balance, 2) }}</td>

                            <td>
                                @if($customer->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد نتائج.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection