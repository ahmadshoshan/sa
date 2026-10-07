@extends('layouts.master')

@section('title', 'تصنيفات المصروفات')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>تصنيفات المصروفات</h4>

        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">إضافة تصنيف</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الحساب المحاسبي</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->code }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->account?->code }} - {{ $category->account?->name }}</td>

                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-sm btn-warning">تعديل</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">لا توجد تصنيفات.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection