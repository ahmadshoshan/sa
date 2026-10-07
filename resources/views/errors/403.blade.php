<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>غير مصرح</title>

    <link href="{{ asset('assets/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body py-5">
                    <h1 class="display-1 text-danger">403</h1>
                    <h4 class="mb-3">عذرًا، ليس لديك صلاحية للوصول إلى هذه الصفحة.</h4>
                    <p class="text-muted mb-4">إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع مدير النظام.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        العودة إلى الرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>