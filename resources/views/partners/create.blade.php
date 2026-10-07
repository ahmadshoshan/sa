@extends('layouts.master')

@section('title', 'إضافة شريك')

@section('content')

    <div class="card">
        <div class="card-header">إضافة شريك جديد</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('partners.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الكود</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" required>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">رقم الهوية</label>
                        <input type="text" name="id_number" value="{{ old('id_number') }}" class="form-control">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">نسبة الأرباح %</label>
                        <input type="number" step="0.0001" min="0" max="100" name="profit_share" value="{{ old('profit_share', 0) }}" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">نسبة رأس المال %</label>
                        <input type="number" step="0.0001" min="0" max="100" name="capital_share" value="{{ old('capital_share', 0) }}" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">العنوان</label>
                        <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">الحالة</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked((bool) old('is_active', true))>
                            <label class="form-check-label">نشط</label>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success">حفظ</button>
                <a href="{{ route('partners.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection