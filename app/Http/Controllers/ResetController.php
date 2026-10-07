<?php

namespace App\Http\Controllers;

use App\Services\ResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ResetController extends Controller
{
    public function index()
    {
        return view('reset.index');
    }

    public function confirm(string $type)
    {
        if (!in_array($type, ['transactions', 'all', 'factory'])) {
            abort(404);
        }

        $titles = [
            'transactions' => 'تفريغ الحركات والمعاملات',
            'all' => 'تفريغ كامل مع الحفاظ على الإعدادات',
            'factory' => 'إعادة ضبط المصنع الكاملة',
        ];

        $descriptions = [
            'transactions' => 'سيتم حذف جميع الفواتير والمدفوعات والقيود المحاسبية والحركات المخزنية والمصروفات وتوزيعات الأرباح، وسيتم تصفير أرصدة العملاء والموردين والمخزون. سيتم الاحتفاظ بالعملاء والموردين والأصناف والشركاء والإعدادات.',
            'all' => 'سيتم حذف جميع البيانات بما فيها العملاء والموردين والأصناف والشركاء وجميع الحركات. سيتم الاحتفاظ بالمستخدمين والصلاحيات ودليل الحسابات والإعدادات فقط.',
            'factory' => 'سيتم حذف كل شيء تمامًا وإعادة زرع البيانات الافتراضية فقط. سيتم الاحتفاظ بالمستخدمين والصلاحيات فقط.',
        ];

        return view('reset.confirm', [
            'type' => $type,
            'title' => $titles[$type],
            'description' => $descriptions[$type],
        ]);
    }

    public function execute(Request $request, string $type, ResetService $resetService)
    {
        if (!in_array($type, ['transactions', 'all', 'factory'])) {
            abort(404);
        }

        $request->validate([
            'password' => ['required'],
            'confirm' => ['required', 'accepted'],
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'كلمة المرور غير صحيحة.');
        }

        try {
            // محاولة إنشاء نسخة احتياطية قبل التفريغ
            try {
                Artisan::call('backup:run');
            } catch (Throwable $e) {
                // لا نوقف التفريغ إذا فشل النسخ الاحتياطي
            }

            match ($type) {
                'transactions' => $resetService->resetTransactions(),
                'all' => $resetService->resetAllKeepSettings(),
                'factory' => $resetService->factoryReset(),
            };

            // مسح الكاش بعد التفريغ
            Artisan::call('optimize:clear');

            $messages = [
                'transactions' => 'تم تفريغ الحركات والمعاملات بنجاح.',
                'all' => 'تم التفريغ الكامل بنجاح مع الحفاظ على الإعدادات.',
                'factory' => 'تمت إعادة ضبط المصنع بنجاح.',
            ];

            return redirect()->route('reset.index')->with('success', $messages[$type]);
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التفريغ: ' . $e->getMessage());
        }
    }
}