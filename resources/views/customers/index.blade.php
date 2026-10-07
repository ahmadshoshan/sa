@extends('layouts.master')

@section('title', 'العملاء')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>العملاء</h4>

        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            إضافة عميل
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الكود</th>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>الرصيد الحالي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->code }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ number_format((float) $customer->current_balance, 2) }}</td>

                            <td>
                                @if($customer->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">
                                    تعديل
                                </a>

                                <form action="{{ route('customers.destroy', $customer) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                لا يوجد عملاء حتى الآن.
                            </td>
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