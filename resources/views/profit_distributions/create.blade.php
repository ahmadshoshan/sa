@extends('layouts.master')

@section('title', 'إنشاء توزيع أرباح')

@section('content')

    <div class="card">
        <div class="card-header">إنشاء توزيع أرباح على الشركاء</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('distributions.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الفترة من</label>
                        <input type="date" name="period_from" value="{{ old('period_from', date('Y-m-01')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الفترة إلى</label>
                        <input type="date" name="period_to" value="{{ old('period_to', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الاحتياطي</label>
                        <input type="number" step="1" name="reserve_amount" value="{{ old('reserve_amount', 0) }}" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">مبلغ التوزيع</label>
                        <input type="number" step="1" name="distribute_amount" value="{{ old('distribute_amount') }}" class="form-control">
                        <small class="text-muted">اتركه فارغًا ليتم حساب المبلغ القابل للتوزيع تلقائيًا.</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button class="btn btn-success">إنشاء المسودة</button>
                <a href="{{ route('distributions.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection