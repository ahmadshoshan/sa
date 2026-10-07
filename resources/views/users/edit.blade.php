@extends('layouts.master')

@section('title', 'تعديل مستخدم')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">تعديل المستخدم</h5>
        </div>

        <div class="card-body">

            @include('partials.errors')

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-control">
                    <small class="text-muted">اتركها فارغة إذا لا تريد تغيير كلمة المرور.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">الدور</label>
                    <select name="role" class="form-select" required>
                        <option value="">اختر الدور</option>

                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role', $user->getRoleNames()->first()) == $role->name)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>

        </div>
    </div>

@endsection