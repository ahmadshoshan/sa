@extends('layouts.master')

@section('title', 'إضافة مستخدم')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">إضافة مستخدم جديد</h5>
        </div>

        <div class="card-body">

            @include('partials.errors')

            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">الدور</label>
                    <select name="role" class="form-select" required>
                        <option value="">اختر الدور</option>

                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role') == $role->name)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success">حفظ</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>

        </div>
    </div>

@endsection