@extends('layouts.master')

@section('title', 'تعديل مورد')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">تعديل بيانات المورد</h5>
        </div>

        <div class="card-body">
            @include('suppliers._form', ['supplier' => $supplier])
        </div>
    </div>

@endsection