<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $keys = [
            'company_name',
            'company_phone',
            'company_address',
            'tax_number',
            'invoice_footer',
            'currency',
            'default_tax_rate',
            'invoice_prefix',
            'purchase_prefix',
            'sale_return_prefix',
            'purchase_return_prefix',
            'payment_receipt_prefix',
            'payment_order_prefix',
            'ai_provider',
            'ai_api_key',
        ];

        foreach ($keys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key)]
            );
        }

        Setting::updateOrCreate(
            ['key' => 'allow_negative_stock'],
            ['value' => $request->boolean('allow_negative_stock') ? '1' : '0']
        );

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }


    public function saveAccent(Request $request)
    {
        $accent = $request->input('accent', '');

        Setting::updateOrCreate(
            ['key' => 'theme_accent'],
            ['value' => $accent]
        );

        return response()->json(['success' => true]);
    }
}