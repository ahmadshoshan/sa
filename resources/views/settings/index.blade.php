@extends('layouts.master')

@section('title', 'الإعدادات')

@section('content')

    <h4 class="mb-4">إعدادات النظام</h4>

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                <h6 class="mb-3">بيانات الشركة</h6>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">اسم الشركة</label>
                        <input type="text"
                               name="company_name"
                               value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">هاتف الشركة</label>
                        <input type="text"
                               name="company_phone"
                               value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الرقم الضريبي</label>
                        <input type="text"
                               name="tax_number"
                               value="{{ old('tax_number', $settings['tax_number'] ?? '') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">عنوان الشركة</label>
                        <textarea name="company_address" class="form-control">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">نص أسفل الفاتورة</label>
                        <textarea name="invoice_footer" class="form-control">{{ old('invoice_footer', $settings['invoice_footer'] ?? 'شكرًا لتعاملكم معنا') }}</textarea>
                    </div>

                </div>

                <hr>

                <h6 class="mb-3">إعدادات عامة</h6>

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">العملة</label>
                        <input type="text"
                               name="currency"
                               value="{{ old('currency', $settings['currency'] ?? 'SAR') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">نسبة الضريبة الافتراضية %</label>
                        <input type="number"
                               step="1"
                               name="default_tax_rate"
                               value="{{ old('default_tax_rate', $settings['default_tax_rate'] ?? 0) }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">بادئة فاتورة البيع</label>
                        <input type="text"
                               name="invoice_prefix"
                               value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">بادئة فاتورة الشراء</label>
                        <input type="text"
                               name="purchase_prefix"
                               value="{{ old('purchase_prefix', $settings['purchase_prefix'] ?? 'PUR') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">بادئة مرتجع البيع</label>
                        <input type="text"
                               name="sale_return_prefix"
                               value="{{ old('sale_return_prefix', $settings['sale_return_prefix'] ?? 'SRT') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">بادئة مرتجع الشراء</label>
                        <input type="text"
                               name="purchase_return_prefix"
                               value="{{ old('purchase_return_prefix', $settings['purchase_return_prefix'] ?? 'PRT') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">بادئة سند القبض</label>
                        <input type="text"
                               name="payment_receipt_prefix"
                               value="{{ old('payment_receipt_prefix', $settings['payment_receipt_prefix'] ?? 'REC') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">بادئة سند الصرف</label>
                        <input type="text"
                               name="payment_order_prefix"
                               value="{{ old('payment_order_prefix', $settings['payment_order_prefix'] ?? 'PAY') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block">السماح بالبيع بالسالب</label>

                        <input type="hidden" name="allow_negative_stock" value="0">

                        <div class="form-check">
                            <input type="checkbox"
                                   name="allow_negative_stock"
                                   value="1"
                                   class="form-check-input"
                                   @checked(old('allow_negative_stock', $settings['allow_negative_stock'] ?? '0') == '1')>

                            <label class="form-check-label">تفعيل</label>
                        </div>
                    </div>

                </div>

                
                <hr>

                <h6 class="mb-3">🤖 المساعد الذكي (اختياري)</h6>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">مزود الذكاء الاصطناعي</label>
                        <select name="ai_provider" class="form-select">
                            <option value="none" @selected(($settings['ai_provider'] ?? 'none') == 'none')>محلي (بدون إنترنت)</option>
                            <option value="openai" @selected(($settings['ai_provider'] ?? '') == 'openai')>OpenAI</option>
                            <option value="gemini" @selected(($settings['ai_provider'] ?? '') == 'gemini')>Gemini</option>
                            <option value="groq" @selected(($settings['ai_provider'] ?? '') == 'groq')>Groq (مجاني وسريع)</option>
                        </select>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">مفتاح API</label>
                        <input type="text" name="ai_api_key" value="{{ $settings['ai_api_key'] ?? '' }}" class="form-control" placeholder="اتركه فارغًا لاستخدام المساعد المحلي">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">حفظ الإعدادات</button>
            </form>

        </div>
    </div>



    <div class="card mt-4">
        <div class="card-header">🎨 المظهر والثيمات</div>

        <div class="card-body">
            <p class="text-muted small">اختر لون الثيم الرئيسي للنظام. يُحفظ تلقائياً ويظهر للجميع.</p>

            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" style="width:70px;height:44px;" onclick="saveAccent('')">أزرق</button>
                <button type="button" class="btn" style="width:70px;height:44px;background:#16a34a;color:#fff;" onclick="saveAccent('green')">أخضر</button>
                <button type="button" class="btn" style="width:70px;height:44px;background:#8b5cf6;color:#fff;" onclick="saveAccent('purple')">بنفسجي</button>
                <button type="button" class="btn" style="width:70px;height:44px;background:#dc2626;color:#fff;" onclick="saveAccent('red')">أحمر</button>
                <button type="button" class="btn" style="width:70px;height:44px;background:#ea580c;color:#fff;" onclick="saveAccent('orange')">برتقالي</button>
                <button type="button" class="btn" style="width:70px;height:44px;background:#0d9488;color:#fff;" onclick="saveAccent('teal')">تركوازي</button>
            </div>
        </div>
    </div>

    <script>
        function saveAccent(name) {
            fetch("{{ route('settings.accent') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ accent: name }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ تم حفظ الثيم بنجاح');
                    location.reload();
                }
            });
        }
    </script>

@endsection