@extends('layouts.master')

@section('title', 'إضافة مورد')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">إضافة مورد جديد</h5>
        </div>

        <div class="card-body">
            @include('suppliers._form', ['supplier' => null])
        </div>
    </div>

@endsection