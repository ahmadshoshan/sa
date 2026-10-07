@extends('layouts.master')

@section('title', 'إعادة تعيين النظام')

@section('content')

    <h4 class="mb-4">إعادة تعيين النظام</h4>

    <div class="alert alert-danger">
        <strong>⚠️ تحذير مهم:</strong>
        عمليات التفريغ لا يمكن التراجع عنها. تأكد من إنشاء نسخة احتياطية قبل المتابعة.
    </div>

    <div class="row">

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">المستوى 1: تفريغ الحركات</h5>
                </div>

                <div class="card-body">
                    <p>حذف جميع المعاملات والحركات مع الاحتفاظ بالبيانات الأساسية.</p>

                    <ul class="small">
                        <li>حذف الفواتير والمدفوعات</li>
                        <li>حذف القيود المحاسبية</li>
                        <li>حذف الحركات المخزنية</li>
                        <li>حذف المصروفات</li>
                        <li>حذف توزيعات الأرباح</li>
                        <li>تصفير أرصدة العملاء والموردين</li>
                        <li>تصفير المخزون</li>
                    </ul>

                    <p class="small text-success">
                        ✅ يحتفظ بالعملاء والموردين والأصناف والشركاء والإعدادات
                    </p>

                    <a href="{{ route('reset.confirm', 'transactions') }}" class="btn btn-warning w-100">
                        متابعة
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">المستوى 2: تفريغ كامل</h5>
                </div>

                <div class="card-body">
                    <p>حذف جميع البيانات مع الاحتفاظ بالإعدادات والهيكل الأساسي.</p>

                    <ul class="small">
                        <li>كل ما في المستوى 1</li>
                        <li>حذف العملاء</li>
                        <li>حذف الموردين</li>
                        <li>حذف الأصناف</li>
                        <li>حذف الشركاء</li>
                    </ul>

                    <p class="small text-success">
                        ✅ يحتفظ بالمستخدمين والصلاحيات ودليل الحسابات والإعدادات
                    </p>

                    <a href="{{ route('reset.confirm', 'all') }}" class="btn btn-danger w-100">
                        متابعة
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">المستوى 3: إعادة ضبط المصنع</h5>
                </div>

                <div class="card-body">
                    <p>حذف كل شيء تمامًا وإعادة النظام لحالته الأولى.</p>

                    <ul class="small">
                        <li>كل ما في المستوى 2</li>
                        <li>حذف دليل الحسابات وإعادة زرعه</li>
                        <li>حذف الإعدادات وإعادة زرعها</li>
                        <li>حذف الوحدات والمخازن والتصنيفات</li>
                    </ul>

                    <p class="small text-success">
                        ✅ يحتفظ بالمستخدمين والصلاحيات فقط
                    </p>

                    <a href="{{ route('reset.confirm', 'factory') }}" class="btn btn-dark w-100">
                        متابعة
                    </a>
                </div>
            </div>
        </div>

    </div>

@endsection