// ============================================
// حماية من الضغط المزدوج على أزرار الحفظ
// ============================================

(function() {
    'use strict';

    // تعطيل زر الحفظ فور الضغط عليه
    function protectSubmitButton(form) {
        const submitButton = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
        if (!submitButton) return true;

        // إذا كان الزر معطلاً بالفعل، منع الإرسال
        if (submitButton.disabled) {
            return false;
        }

        // تعطيل الزر فوراً
        submitButton.disabled = true;
        
        // حفظ النص الأصلي
        const originalText = submitButton.innerHTML;
        submitButton.setAttribute('data-original-text', originalText);
        
        // تغيير النص إلى "جاري الحفظ..."
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" style="margin-left: 5px;"></span> جاري الحفظ...';
        
        // إضافة مفتاح فريد للعملية (Idempotency Key)
        let idempotencyInput = form.querySelector('input[name="_idempotency_key"]');
        if (!idempotencyInput) {
            idempotencyInput = document.createElement('input');
            idempotencyInput.type = 'hidden';
            idempotencyInput.name = '_idempotency_key';
            form.appendChild(idempotencyInput);
        }
        
        // توليد مفتاح فريد
        if (!idempotencyInput.value) {
            idempotencyInput.value = 'op_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        
        return true;
    }

    // إعادة تفعيل الزر بعد خطأ (مثلاً خطأ تحقق)
    function reenableSubmitButtons() {
        document.querySelectorAll('button[type="submit"][data-original-text], input[type="submit"][data-original-text]').forEach(function(btn) {
            btn.disabled = false;
            btn.innerHTML = btn.getAttribute('data-original-text');
            btn.removeAttribute('data-original-text');
        });
    }

    // ربط الحماية بكل النماذج في الصفحة
    function attachFormProtection() {
        document.querySelectorAll('form[method="POST"]').forEach(function(form) {
            // تخطي النماذج التي لها خاصية خاصة
            if (form.hasAttribute('data-no-protection')) return;
            
            form.addEventListener('submit', function(event) {
                // التحقق من صلاحية النموذج أولاً
                if (!form.checkValidity()) {
                    return true; // السماح بالعرض الافتراضي للأخطاء
                }
                
                if (!protectSubmitButton(form)) {
                    event.preventDefault();
                    return false;
                }
                
                // إعادة تفعيل الزر بعد 5 ثواني كحد أقصى (في حالة فشل الإرسال)
                setTimeout(function() {
                    reenableSubmitButtons();
                }, 5000);
            });
        });
    }

    // إعادة تفعيل الزر إذا كان هناك أخطاء في الصفحة
    function checkForErrors() {
        const hasErrors = document.querySelector('.alert-danger, .invalid-feedback, .is-invalid');
        if (hasErrors) {
            reenableSubmitButtons();
        }
    }

    // تشغيل الحماية عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            attachFormProtection();
            checkForErrors();
        });
    } else {
        attachFormProtection();
        checkForErrors();
    }

    // إعادة تفعيل الأزرار بعد استخدام زر الرجوع
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            reenableSubmitButtons();
        }
    });

    // إعادة تفعيل الأزرار عند العودة من صفحة أخرى
    window.addEventListener('popstate', function() {
        reenableSubmitButtons();
    });

    // توفير دوال عامة للاستخدام اليدوي
    window.FormProtection = {
        protect: protectSubmitButton,
        reenable: reenableSubmitButtons,
        attach: attachFormProtection
    };
})();