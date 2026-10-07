@extends('layouts.master')

@section('title', 'تعديل عميل')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">تعديل بيانات العميل</h5>
        </div>

        <div class="card-body">
            @include('customers._form', ['customer' => $customer])
        </div>
    </div>

@endsection