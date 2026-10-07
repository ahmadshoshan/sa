<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompanySetupSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $accounts = [
            ['code' => '1501', 'name' => 'الأصول الثابتة', 'type' => 'asset'],
            ['code' => '2301', 'name' => 'مصروفات مستحقة', 'type' => 'liability'],
            ['code' => '3101', 'name' => 'رأس مال الشركاء', 'type' => 'equity'],
            ['code' => '3102', 'name' => 'مسحوبات الشركاء', 'type' => 'equity'],
            ['code' => '3103', 'name' => 'أرباح مرحّلة', 'type' => 'equity'],
            ['code' => '3104', 'name' => 'احتياطي قانوني', 'type' => 'equity'],
            ['code' => '3201', 'name' => 'أرباح مستحقة للتوزيع', 'type' => 'liability'],
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

        $permissions = [
            'partners.manage',
            'capital.manage',
            'expenses.view',
            'expenses.create',
            'distributions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->givePermissionTo(['expenses.view', 'expenses.create', 'distributions.manage']);

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->givePermissionTo(['expenses.view']);
    }
}