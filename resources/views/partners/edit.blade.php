@extends('layouts.master')

@section('title', 'تعديل شريك')

@section('content')

    <div class="card">
        <div class="card-header">تعديل الشريك</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('partners.update', $partner) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الكود</label>
                        <input type="text" name="code" value="{{ old('code', $partner->code) }}" class="form-control" required>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" value="{{ old('name', $partner->name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $partner->phone) }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', $partner->email) }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">رقم الهوية</label>
                        <input type="text" name="id_number" value="{{ old('id_number', $partner->id_number) }}" class="form-control">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">نسبة الأرباح %</label>
                        <input type="number" step="1" name="profit_share" value="{{ old('profit_share', $partner->profit_share) }}" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">نسبة رأس المال %</label>
                        <input type="number" step="1" name="capital_share" value="{{ old('capital_share', $partner->capital_share) }}" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">العنوان</label>
                        <textarea name="address" class="form-control">{{ old('address', $partner->address) }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes', $partner->notes) }}</textarea>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">الحالة</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($partner->is_active)>
                            <label class="form-check-label">نشط</label>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('partners.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection