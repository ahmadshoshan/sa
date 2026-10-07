@extends('layouts.master')

@section('title', 'النسخ الاحتياطي')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>النسخ الاحتياطي</h4>

        <form method="POST" action="{{ route('backups.run') }}">
            @csrf

            <button type="submit" class="btn btn-primary">
                إنشاء نسخة احتياطية الآن
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>اسم الملف</th>
                        <th>التاريخ</th>
                        <th>الحجم</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($files as $file)
                        <tr>
                            <td>{{ $file['name'] }}</td>
                            <td>{{ $file['date'] }}</td>
                            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>

                            <td>
                                <a href="{{ route('backups.download', $file['name']) }}" class="btn btn-sm btn-success">
                                    تحميل
                                </a>

                                <form action="{{ route('backups.destroy', $file['name']) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه النسخة؟');">
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
                            <td colspan="4" class="text-center">
                                لا توجد نسخ احتياطية حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

@endsection