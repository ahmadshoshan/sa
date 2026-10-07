@extends('layouts.master')

@section('title', 'إضافة عميل')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">إضافة عميل جديد</h5>
        </div>

        <div class="card-body">
            @include('customers._form', ['customer' => null])
        </div>
    </div>

@endsection