@extends('layouts.master')

@section('title', 'تعديل مخزن')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">تعديل المخزن</h5>
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

            <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">الكود</label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $warehouse->code) }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">اسم المخزن</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $warehouse->name) }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input"
                           id="is_active"
                           @checked($warehouse->is_active)>

                    <label class="form-check-label" for="is_active">
                        نشط
                    </label>
                </div>

                <button type="submit" class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>

        </div>
    </div>

@endsection
