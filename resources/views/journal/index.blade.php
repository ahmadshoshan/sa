@extends('layouts.master')

@section('title', 'قيود اليومية')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>قيود اليومية</h4>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم القيد</th>
                        <th>التاريخ</th>
                        <th>الوصف</th>
                        <th>المرجع</th>
                        <th>عدد الأطراف</th>
                        <th>عرض</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>{{ $entry->entry_no }}</td>
                            <td>{{ $entry->entry_date?->format('Y-m-d') }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ $entry->ref_type }} / {{ $entry->ref_id }}</td>
                            <td>{{ $entry->lines_count }}</td>

                            <td>
                                <a href="{{ route('journal.show', $entry) }}" class="btn btn-sm btn-info">
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                لا توجد قيود حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $entries->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

@endsection