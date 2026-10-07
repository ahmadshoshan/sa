@extends('layouts.master')

@section('title', 'تعديل تصنيف مصروفات')

@section('content')

    <div class="card">
        <div class="card-header">تعديل تصنيف مصروفات</div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST" action="{{ route('expense-categories.update', $expenseCategory) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الكود</label>
                        <input type="text" name="code" value="{{ old('code', $expenseCategory->code) }}" class="form-control" required>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" value="{{ old('name', $expenseCategory->name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الحساب المحاسبي</label>
                        <select name="account_id" class="form-select">
                            <option value="">اختر الحساب</option>

                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id', $expenseCategory->account_id) == $account->id)>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">الحالة</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($expenseCategory->is_active)>
                            <label class="form-check-label">نشط</label>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>

@endsection