@extends('layouts.master')

@section('title', 'الوحدات')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>الوحدات</h4>
        <a href="{{ route('units.create') }}" class="btn btn-primary">
            إضافة وحدة
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
                    @forelse($units as $unit)
                        <tr>
                            <td>{{ $unit->id }}</td>
                            <td>{{ $unit->code }}</td>
                            <td>{{ $unit->name }}</td>

                            <td>
                                @if($unit->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-warning">
                                    تعديل
                                </a>

                                <form action="{{ route('units.destroy', $unit) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الوحدة؟');">
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
                                لا توجد وحدات حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $units->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection