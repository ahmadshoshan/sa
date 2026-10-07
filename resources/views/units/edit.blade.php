@extends('layouts.master')

@section('title', 'تعديل وحدة')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">تعديل الوحدة</h5>
        </div>

        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('units.update', $unit) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">الكود</label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $unit->code) }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">اسم الوحدة</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $unit->name) }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input"
                           id="is_active"
                           @checked($unit->is_active)>

                    <label class="form-check-label" for="is_active">
                        نشط
                    </label>
                </div>

                <button type="submit" class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>

        </div>
    </div>

@endsection