@extends('layouts.master')

@section('title', 'دليل المساعد الذكي')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🤖 دليل المساعد الذكي</h4>
        <button class="btn btn-primary" onclick="window.open('{{ url('/') }}', '_self')">العودة للنظام</button>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">💡 ما هو المساعد الذكي؟</h5>
        </div>
        <div class="card-body">
            <p>المساعد الذكي هو روبوت محاسبي يفهم اللغة العربية (الفصحى واللهجة المصرية) وينفذ أوامرك مباشرة في النظام.</p>
            <p><strong>لفتح المساعد:</strong> اضغط على زر 🤖 في أسفل يسار أي صفحة.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">📊 الاستعلامات المالية والمحاسبية</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>اكتب</th><th>النتيجة</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>ما مبيعات اليوم؟</code></td><td>إجمالي مبيعات اليوم</td></tr>
                        <tr><td><code>ما مبيعات الشهر؟</code></td><td>إجمالي مبيعات الشهر الحالي</td></tr>
                        <tr><td><code>ما مبيعات السنة؟</code></td><td>إجمالي مبيعات السنة</td></tr>
                        <tr><td><code>ما صافي الربح؟</code></td><td>صافي ربح الشهر الحالي</td></tr>
                        <tr><td><code>ما رصيد علاء؟</code></td><td>رصيد العميل المحدد</td></tr>
                        <tr><td><code>ما رصيد مورد عام؟</code></td><td>رصيد المورد المحدد</td></tr>
                        <tr><td><code>ما أرصدة العملاء؟</code></td><td>إجمالي أرصدة كل العملاء</td></tr>
                        <tr><td><code>ما رصيد حساب الصندوق؟</code></td><td>رصيد الحساب المحاسبي</td></tr>
                        <tr><td><code>ما قائمة الدخل؟</code></td><td>الإيرادات والمصروفات وصافي الربح</td></tr>
                        <tr><td><code>ما ميزان المراجعة؟</code></td><td>أرصدة جميع الحسابات</td></tr>
                        <tr><td><code>ما المركز المالي؟</code></td><td>الأصول والخصوم وحقوق الملكية</td></tr>
                        <tr><td><code>ما آخر القيود؟</code></td><td>آخر 10 قيود محاسبية</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">📦 إدارة الأصناف</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>اكتب</th><th>النتيجة</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>ما تفاصيل اسمنت الصفوه؟</code></td><td>كل معلومات الصنف: الأسعار، المخزون، المبيعات، المشتريات، الربح</td></tr>
                        <tr><td><code>ما مخزون حديد 5 لينيه؟</code></td><td>كمية المخزون في كل مخزن</td></tr>
                        <tr><td><code>ما مبيعات رمل؟</code></td><td>إجمالي مبيعات الصنف المحدد</td></tr>
                        <tr><td><code>ما مشتريات اسمنت؟</code></td><td>إجمالي مشتريات الصنف</td></tr>
                        <tr><td><code>أضف صنف جديد</code></td><td>يبدأ معك خطوة بخطوة لإضافة الصنف</td></tr>
                        <tr><td><code>ما الأصناف المنخفضة؟</code></td><td>الأصناف التي وصلت الحد الأدنى</td></tr>
                        <tr><td><code>ما أفضل الأصناف مبيعاً؟</code></td><td>ترتيب الأصناف حسب المبيعات</td></tr>
                        <tr><td><code>ما أسوأ الأصناف مبيعاً؟</code></td><td>الأصناف الأقل مبيعاً</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">✏️ تعديل الأصناف (بمرونة كاملة!)</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <strong>✨ الميزة الأقوى:</strong> المساعد يفهم كل طرق التعبير عن التعديل!
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>اكتب</th><th>النتيجة</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>عدل سعر اسمنت الصفوه إلى 6000</code></td><td>يغير السعر مباشرة</td></tr>
                        <tr><td><code>غير سعر الحديد</code></td><td>يسألك عن القيمة الجديدة</td></tr>
                        <tr><td><code>زود سعر اسمنت الصفوه 100</code></td><td>يزيد السعر بمقدار 100</td></tr>
                        <tr><td><code>نقص سعر الرمل 50</code></td><td>ينقص السعر بمقدار 50</td></tr>
                        <tr><td><code>ارفع سعر الحديد 200</code></td><td>يزيد السعر بمقدار 200</td></tr>
                        <tr><td><code>وطي سعر الاسمنت 100</code></td><td>ينقص السعر بمقدار 100</td></tr>
                        <tr><td><code>غلي الحديد</code></td><td>يسألك عن مقدار الزيادة</td></tr>
                        <tr><td><code>رخص الاسمنت</code></td><td>يسألك عن مقدار النقص</td></tr>
                        <tr><td><code>ضاعف سعر الرمل</code></td><td>يضاعف السعر</td></tr>
                        <tr><td><code>خلي سعر الاسمنت 7000</code></td><td>يحدد السعر بـ 7000</td></tr>
                        <tr><td><code>عدل اسم صنف 1 إلى اسمنت ممتاز</code></td><td>يغير الاسم</td></tr>
                        <tr><td><code>عدل سعر تكلفة الحديد إلى 45000</code></td><td>يغير سعر التكلفة</td></tr>
                        <tr><td><code>عدل سعر الجملة للاسمنت إلى 5000</code></td><td>يغير سعر الجملة</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                <strong>💡 نصيحة:</strong> لو كتبت «عدل سعر الاسمنت» بدون تحديد القيمة، المساعد هيسألك عن القيمة الجديدة.
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">🗑️ الحذف الآمن</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>اكتب</th><th>النتيجة</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>احذف صنف 1</code></td><td>يطلب تأكيد ثم يحذف</td></tr>
                        <tr><td><code>احذف عميل محمد</code></td><td>يطلب تأكيد ثم يحذف</td></tr>
                        <tr><td><code>احذف مورد عام</code></td><td>يطلب تأكيد ثم يحذف</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-warning mt-3">
                <strong>⚠️ حماية ذكية:</strong> المساعد لن يحذف أي صنف أو عميل أو مورد مرتبط بفواتير أو لديه رصيد، حفاظاً على سلامة النظام المحاسبي.
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">👥 إدارة العملاء والموردين</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>اكتب</th><th>النتيجة</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>ما كشف حساب علاء؟</code></td><td>كشف حساب كامل للعميل</td></tr>
                        <tr><td><code>أضف عميل جديد</code></td><td>يبدأ معك خطوة بخطوة</td></tr>
                        <tr><td><code>أضف عميل اسمه أحمد</code></td><td>يضيف العميل مباشرة</td></tr>
                        <tr><td><code>أضف مورد جديد</code></td><td>يبدأ معك خطوة بخطوة</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">🎙️ التحكم الصوتي</h5>
        </div>
        <div class="card-body">
            <p>اضغط على زر الميكروفون 🎙️ في نافذة المساعد وتحدث بالعربية.</p>
            <p><strong>أمثلة صوتية:</strong></p>
            <ul>
                <li>«ما مبيعات اليوم»</li>
                <li>«زود سعر الاسمنت 100»</li>
                <li>«ما رصيد علاء»</li>
                <li>«اقترح عليّ»</li>
            </ul>
            <div class="alert alert-info">
                <strong>ملاحظة:</strong> التحكم الصوتي يعمل في متصفح Google Chrome و Microsoft Edge. تأكد من السماح بالوصول للميكروفون.
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🧠 التعلم الذكي</h5>
        </div>
        <div class="card-body">
            <p>المساعد يتعلم من أسئلتك!</p>
            <ul>
                <li>يحفظ الأسئلة المتكررة ويجيب عليها فوراً</li>
                <li>يفهم الأسئلة المشابهة حتى لو كانت بصياغة مختلفة</li>
                <li>يقترح عليك أوامر بناءً على استخدامك</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">💡 نصائح للحصول على أفضل النتائج</h5>
        </div>
        <div class="card-body">
            <ol>
                <li><strong>كن محدداً:</strong> اكتب اسم الصنف أو العميل كاملاً</li>
                <li><strong>استخدم الأرقام:</strong> «زود السعر 100» أفضل من «زود السعر شوية»</li>
                <li><strong>جرّب الصياغات المختلفة:</strong> المساعد يفهم الفصحى واللهجة</li>
                <li><strong>استخدم الاقتراحات:</strong> الأزرار السريعة تساعدك تبدأ</li>
                <li><strong>اسأل «مساعدة»:</strong> لعرض كل الأوامر المتاحة</li>
            </ol>
        </div>
    </div>

@endsection