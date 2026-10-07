@extends('layouts.master')

@section('title', 'تأكيد ' . $title)

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">⚠️ تأكيد: {{ $title }}</h5>
                </div>

                <div class="card-body">

                    <div class="alert alert-danger">
                        {{ $description }}
                    </div>

                    <div class="alert alert-warning">
                        <strong>ملاحظة:</strong> سيتم محاولة إنشاء نسخة احتياطية تلقائيًا قبل التفريغ.
                        لكن يُنصح بشدة بإنشاء نسخة احتياطية يدويًا والتأكد منها قبل المتابعة.
                    </div>

                    @include('partials.errors')

                    <form method="POST" action="{{ route('reset.execute', $type) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">أدخل كلمة المرور للتأكيد</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="confirm" value="1" class="form-check-input" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                أؤكد أنني أريد تفريغ النظام وأتحمل مسؤولية هذه العملية
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                نعم، قم بالتفريغ الآن
                            </button>

                            <a href="{{ route('reset.index') }}" class="btn btn-secondary">
                                إلغاء والرجوع
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

@endsection