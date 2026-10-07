<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetService
{
    /**
     * المستوى 1: تفريغ الحركات والمعاملات فقط
     * يحذف الفواتير والمدفوعات والقيود ويصفّر الأرصدة والمخزون
     * يحتفظ بالعملاء والموردين والأصناف والشركاء
     */
    public function resetTransactions(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // الجداول المرتبطة بالحركات
        $this->safeTruncate('invoice_installments');
        $this->safeTruncate('partner_distribution_items');
        $this->safeTruncate('partner_distributions');
        $this->safeTruncate('partner_capitals');
        $this->safeTruncate('partner_withdrawals');
        $this->safeTruncate('expenses');
        $this->safeTruncate('payments');
        $this->safeTruncate('invoice_items');
        $this->safeTruncate('invoices');
        $this->safeTruncate('stock_movements');
        $this->safeTruncate('stocks');
        $this->safeTruncate('journal_lines');
        $this->safeTruncate('journal_entries');
        $this->safeTruncate('period_closings');
        $this->safeTruncate('notifications');
        $this->safeTruncate('audit_logs');
        $this->safeTruncate('account_transactions');
        $this->safeTruncate('fund_transfers');
        $this->safeTruncate('invoice_item_returns');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // تصفير أرصدة العملاء والموردين
        Customer::query()->update(['current_balance' => DB::raw('opening_balance')]);
        Supplier::query()->update(['current_balance' => DB::raw('opening_balance')]);
    }

    /**
     * المستوى 2: تفريغ كامل مع الحفاظ على الإعدادات
     * يحذف كل شيء ما عدا المستخدمين والصلاحيات ودليل الحسابات والإعدادات
     */
    public function resetAllKeepSettings(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // الحركات أولًا
        $this->safeTruncate('invoice_installments');
        $this->safeTruncate('partner_distribution_items');
        $this->safeTruncate('partner_distributions');
        $this->safeTruncate('partner_capitals');
        $this->safeTruncate('partner_withdrawals');
        $this->safeTruncate('expenses');
        $this->safeTruncate('payments');
        $this->safeTruncate('invoice_items');
        $this->safeTruncate('invoices');
        $this->safeTruncate('stock_movements');
        $this->safeTruncate('stocks');
        $this->safeTruncate('journal_lines');
        $this->safeTruncate('journal_entries');
        $this->safeTruncate('period_closings');
        $this->safeTruncate('notifications');
        $this->safeTruncate('audit_logs');

        // البيانات الأساسية
        $this->safeTruncate('products');
        $this->safeTruncate('customers');
        $this->safeTruncate('suppliers');
        $this->safeTruncate('partners');
        $this->safeTruncate('expense_categories');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // إعادة زرع تصنيفات المصروفات الافتراضية
        $this->seedExpenseCategories();
    }

    /**
     * المستوى 3: إعادة ضبط المصنع الكاملة
     * يحذف كل شيء ويعيد زرع البيانات الافتراضية
     */
    public function factoryReset(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // الحركات
        $this->safeTruncate('invoice_installments');
        $this->safeTruncate('partner_distribution_items');
        $this->safeTruncate('partner_distributions');
        $this->safeTruncate('partner_capitals');
        $this->safeTruncate('partner_withdrawals');
        $this->safeTruncate('expenses');
        $this->safeTruncate('payments');
        $this->safeTruncate('invoice_items');
        $this->safeTruncate('invoices');
        $this->safeTruncate('stock_movements');
        $this->safeTruncate('stocks');
        $this->safeTruncate('journal_lines');
        $this->safeTruncate('journal_entries');
        $this->safeTruncate('period_closings');
        $this->safeTruncate('notifications');
        $this->safeTruncate('audit_logs');

        // البيانات الأساسية
        $this->safeTruncate('products');
        $this->safeTruncate('customers');
        $this->safeTruncate('suppliers');
        $this->safeTruncate('partners');
        $this->safeTruncate('expense_categories');
        $this->safeTruncate('categories');
        $this->safeTruncate('units');
        $this->safeTruncate('warehouses');

        // دليل الحسابات
        $this->safeTruncate('accounts');

        // الإعدادات
        $this->safeTruncate('settings');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // إعادة زرع البيانات الافتراضية
        $this->seedAccounts();
        $this->seedExpenseCategories();
        $this->seedSettings();
        $this->seedDefaultUnits();
        $this->seedDefaultWarehouses();
        $this->seedDefaultCategories();
    }

    private function safeTruncate(string $table): void
    {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
        }
    }

    private function seedAccounts(): void
    {
        $accounts = [
            ['code' => '1001', 'name' => 'الصندوق', 'type' => 'asset'],
            ['code' => '1002', 'name' => 'البنك', 'type' => 'asset'],
            ['code' => '1101', 'name' => 'العملاء', 'type' => 'asset'],
            ['code' => '1201', 'name' => 'المخزون', 'type' => 'asset'],
            ['code' => '1301', 'name' => 'ضريبة المشتريات', 'type' => 'asset'],
            ['code' => '1501', 'name' => 'الأصول الثابتة', 'type' => 'asset'],
            ['code' => '2101', 'name' => 'الموردون', 'type' => 'liability'],
            ['code' => '2201', 'name' => 'ضريبة المبيعات', 'type' => 'liability'],
            ['code' => '2301', 'name' => 'مصروفات مستحقة', 'type' => 'liability'],
            ['code' => '3101', 'name' => 'رأس مال الشركاء', 'type' => 'equity'],
            ['code' => '3102', 'name' => 'مسحوبات الشركاء', 'type' => 'equity'],
            ['code' => '3103', 'name' => 'أرباح مرحّلة', 'type' => 'equity'],
            ['code' => '3104', 'name' => 'احتياطي قانوني', 'type' => 'equity'],
            ['code' => '3201', 'name' => 'أرباح مستحقة للتوزيع', 'type' => 'liability'],
            ['code' => '4101', 'name' => 'المبيعات', 'type' => 'revenue'],
            ['code' => '4201', 'name' => 'إيرادات أخرى', 'type' => 'revenue'],
            ['code' => '5101', 'name' => 'تكلفة البضاعة المباعة', 'type' => 'expense'],
            ['code' => '5201', 'name' => 'مصروفات عامة', 'type' => 'expense'],
            ['code' => '5301', 'name' => 'المرتبات والأجور', 'type' => 'expense'],
            ['code' => '5302', 'name' => 'العمولات', 'type' => 'expense'],
            ['code' => '5303', 'name' => 'النقل والمواصلات', 'type' => 'expense'],
            ['code' => '5304', 'name' => 'الإيجار', 'type' => 'expense'],
            ['code' => '5305', 'name' => 'الكهرباء والمياه', 'type' => 'expense'],
            ['code' => '5306', 'name' => 'الاتصالات', 'type' => 'expense'],
            ['code' => '5307', 'name' => 'الصيانة', 'type' => 'expense'],
            ['code' => '5308', 'name' => 'التسويق والإعلان', 'type' => 'expense'],
            ['code' => '5309', 'name' => 'الرسوم الحكومية', 'type' => 'expense'],
            ['code' => '5310', 'name' => 'الاستهلاك والإطفاء', 'type' => 'expense'],
            ['code' => '5311', 'name' => 'التمويل والفوائد', 'type' => 'expense'],
            ['code' => '5312', 'name' => 'مصروفات أخرى', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }

    private function seedExpenseCategories(): void
    {
        $categories = [
            ['code' => 'SAL', 'name' => 'المرتبات والأجور', 'account_code' => '5301'],
            ['code' => 'COMM', 'name' => 'العمولات', 'account_code' => '5302'],
            ['code' => 'TRANS', 'name' => 'النقل والمواصلات', 'account_code' => '5303'],
            ['code' => 'RENT', 'name' => 'الإيجار', 'account_code' => '5304'],
            ['code' => 'UTIL', 'name' => 'الكهرباء والمياه', 'account_code' => '5305'],
            ['code' => 'TEL', 'name' => 'الاتصالات', 'account_code' => '5306'],
            ['code' => 'MAINT', 'name' => 'الصيانة', 'account_code' => '5307'],
            ['code' => 'MARK', 'name' => 'التسويق والإعلان', 'account_code' => '5308'],
            ['code' => 'GOV', 'name' => 'الرسوم الحكومية', 'account_code' => '5309'],
            ['code' => 'DEPR', 'name' => 'الاستهلاك والإطفاء', 'account_code' => '5310'],
            ['code' => 'FIN', 'name' => 'التمويل والفوائد', 'account_code' => '5311'],
            ['code' => 'OTHER', 'name' => 'مصروفات أخرى', 'account_code' => '5312'],
        ];

        foreach ($categories as $category) {
            $account = Account::where('code', $category['account_code'])->first();

            ExpenseCategory::firstOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'account_id' => $account?->id,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedSettings(): void
    {
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
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedDefaultUnits(): void
    {
        if (Schema::hasTable('units')) {
            DB::table('units')->insertOrIgnore([
                ['code' => 'PCS', 'name' => 'قطعة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'BOX', 'name' => 'صندوق', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'KG', 'name' => 'كيلو', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    private function seedDefaultWarehouses(): void
    {
        if (Schema::hasTable('warehouses')) {
            DB::table('warehouses')->insertOrIgnore([
                ['code' => 'MAIN', 'name' => 'المخزن الرئيسي', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    private function seedDefaultCategories(): void
    {
        if (Schema::hasTable('categories')) {
            DB::table('categories')->insertOrIgnore([
                ['code' => 'GENERAL', 'name' => 'تصنيف عام', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}