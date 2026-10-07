@extends('layouts.master')

@section('title', 'المخازن')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>المخازن</h4>
        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
            إضافة مخزن
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
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td>{{ $warehouse->id }}</td>
                            <td>{{ $warehouse->code }}</td>
                            <td>{{ $warehouse->name }}</td>

                            <td>
                                @if($warehouse->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-sm btn-warning">
                                    تعديل
                                </a>

                                <form action="{{ route('warehouses.destroy', $warehouse) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المخزن؟');">
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
                            <td colspan="5" class="text-center">
                                لا توجد مخازن حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $warehouses->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection