<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Schema;

/**
 * Trait لمنع تكرار العمليات (Idempotency)
 * 
 * يضمن عدم تكرار نفس العملية مرتين حتى لو تم الضغط على زر الحفظ عدة مرات
 */
trait PreventsDuplicateSubmission
{
    /**
     * البحث عن عملية موجودة بنفس المفتاح
     */
    public static function findByIdempotencyKey(?string $key)
    {
        if (empty($key)) return null;
        return static::where('idempotency_key', $key)->first();
    }

    /**
     * التحقق من أن العملية جديدة (لم تُنفذ مسبقاً)
     */
    public static function isDuplicateOperation(?string $key): bool
    {
        if (empty($key)) return false;
        return static::where('idempotency_key', $key)->exists();
    }

    /**
     * توليد مفتاح فريد للعملية
     */
    public static function generateIdempotencyKey(): string
    {
        return uniqid('op_', true) . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Boot method لإضافة الحقل تلقائياً عند الإنشاء
     */
    public static function bootPreventsDuplicateSubmission(): void
    {
        static::creating(function ($model) {
            // إذا لم يتم تعيين المفتاح، نولده تلقائياً
            if (empty($model->idempotency_key)) {
                // التحقق من وجود العمود في الجدول
                try {
                    if (Schema::hasColumn($model->getTable(), 'idempotency_key')) {
                        $model->idempotency_key = static::generateIdempotencyKey();
                    }
                } catch (\Throwable $e) {
                    // إذا فشل التحقق، نتجاهل الخطأ
                }
            }
        });
    }
}