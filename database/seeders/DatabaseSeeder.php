<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Account;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // مستخدم مدير
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
            ]
        );

        // تصنيف عام
        Category::firstOrCreate(
            ['code' => 'GENERAL'],
            [
                'name' => 'تصنيف عام',
                'is_active' => true,
            ]
        );

        // وحدة قطعة
        Unit::firstOrCreate(
            ['code' => 'PCS'],
            [
                'name' => 'قطعة',
                'is_active' => true,
            ]
        );

        // مخزن رئيسي
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'المخزن الرئيسي',
                'is_active' => true,
            ]
        );

        // عميل تجريبي
        Customer::firstOrCreate(
            ['code' => 'C001'],
            [
                'name' => 'عميل نقدي',
                'phone' => '0000000000',
                'email' => null,
                'address' => null,
                'tax_number' => null,
                'opening_balance' => 0,
                'current_balance' => 0,
                'credit_limit' => 0,
                'price_level' => 'retail',
                'notes' => null,
                'is_active' => true,
            ]
        );

        // مورد تجريبي
        Supplier::firstOrCreate(
            ['code' => 'S001'],
            [
                'name' => 'مورد عام',
                'phone' => '0000000000',
                'email' => null,
                'address' => null,
                'tax_number' => null,
                'opening_balance' => 0,
                'current_balance' => 0,
                'notes' => null,
                'is_active' => true,
            ]
        );

        // صنف تجريبي
        $product = Product::firstOrCreate(
            ['code' => 'P001'],
            [
                'barcode' => '6280000000001',
                'name' => 'صنف تجريبي',
                'category_id' => Category::where('code', 'GENERAL')->first()->id,
                'unit_id' => Unit::where('code', 'PCS')->first()->id,
                'cost_price' => 0,
                'sale_price' => 0,
                'wholesale_price' => 0,
                'tax_rate' => 0,
                'min_stock' => 0,
                'image' => null,
                'description' => null,
                'is_active' => true,
            ]
        );

        // رصيد مخزون للصنف التجريبي
        Stock::firstOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
            ],
            [
                'quantity' => 0,
            ]
        );

        // دليل الحسابات الأساسي
        $accounts = [
            [
                'code' => '1001',
                'name' => 'الصندوق',
                'type' => 'asset',
            ],
            [
                'code' => '1002',
                'name' => 'البنك',
                'type' => 'asset',
            ],
            [
                'code' => '1101',
                'name' => 'العملاء',
                'type' => 'asset',
            ],
            [
                'code' => '1201',
                'name' => 'المخزون',
                'type' => 'asset',
            ],
            [
                'code' => '1301',
                'name' => 'ضريبة المشتريات',
                'type' => 'asset',
            ],
            [
                'code' => '2101',
                'name' => 'الموردون',
                'type' => 'liability',
            ],
            [
                'code' => '2201',
                'name' => 'ضريبة المبيعات',
                'type' => 'liability',
            ],
            [
                'code' => '4101',
                'name' => 'المبيعات',
                'type' => 'revenue',
            ],
            [
                'code' => '4201',
                'name' => 'إيرادات أخرى',
                'type' => 'revenue',
            ],
            [
                'code' => '5101',
                'name' => 'تكلفة البضاعة المباعة',
                'type' => 'expense',
            ],
            [
                'code' => '5201',
                'name' => 'مصروفات عامة',
                'type' => 'expense',
            ],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(
                ['code' => $account['code']],
                $account
            );
        }

        // إعدادات عامة
        $settings = [
            'company_name' => 'شركة المبيعات',
            'currency' => 'SAR',
            'default_tax_rate' => '0',
            'invoice_prefix' => 'INV',
            'purchase_prefix' => 'PUR',
            'sale_return_prefix' => 'SRT',
            'purchase_return_prefix' => 'PRT',
            'payment_receipt_prefix' => 'REC',
            'payment_order_prefix' => 'PAY',
            'allow_negative_stock' => '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}