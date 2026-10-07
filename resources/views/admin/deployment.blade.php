@extends('layouts.master')

@section('title', 'دليل النشر')

@section('content')

    <h4 class="mb-4">🚀 دليل النشر والاستضافة</h4>

    <div class="card">
        <div class="card-body">
            <p class="lead">للحصول على دليل النشر الكامل والاحترافي، يرجى مراجعة ملف <code class="bg-light p-1 rounded">DEPLOYMENT.md</code> الموجود في جذر المشروع.</p>
            
            <hr>

            <h5>يتضمن الدليل:</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">✅ متطلبات السيرفر (PHP, MySQL, Extensions)</li>
                <li class="list-group-item">✅ خطوات التثبيت وقاعدة البيانات</li>
                <li class="list-group-item">✅ إعدادات ملف <code>.env</code> للإنتاج</li>
                <li class="list-group-item">✅ إعدادات الخادم (Nginx / Apache)</li>
                <li class="list-group-item">✅ إعدادات المهام المجدولة (Cron Jobs)</li>
                <li class="list-group-item">✅ قائمة التحقق قبل الإطلاق</li>
            </ul>

            <div class="alert alert-info mt-4 mb-0">
                <strong>💡 نصيحة:</strong> تأكد دائمًا من إنشاء نسخة احتياطية قبل تحديث النظام على بيئة الإنتاج.
            </div>
        </div>
    </div>

@endsection