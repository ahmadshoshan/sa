@extends('layouts.master')

@section('title', 'سجل العمليات')

@section('content')

    <h4 class="mb-4">سجل العمليات</h4>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>المستخدم</th>
                        <th>العملية</th>
                        <th>الجدول</th>
                        <th>رقم السجل</th>
                        <th>IP</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? 'نظام' }}</td>

                            <td>
                                @if($log->action === 'created')
                                    <span class="badge bg-success">إضافة</span>
                                @elseif($log->action === 'updated')
                                    <span class="badge bg-warning">تعديل</span>
                                @elseif($log->action === 'deleted')
                                    <span class="badge bg-danger">حذف</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->action }}</span>
                                @endif
                            </td>

                            <td>{{ $log->table_name }}</td>
                            <td>{{ $log->record_id }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ json_encode($log->new_values) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                لا توجد عمليات مسجلة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection