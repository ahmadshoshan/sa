<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'master-data.manage',
            'sales.view',
            'sales.create',
            'purchases.view',
            'purchases.create',
            'returns.view',
            'returns.create',
            'payments.view',
            'payments.create',
            'reports.view',
            'financial.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $storekeeper = Role::firstOrCreate(['name' => 'storekeeper', 'guard_name' => 'web']);

        // Admin gets all permissions
        $admin->syncPermissions(Permission::all());

        // Accountant: financial reports, journal, trial balance, balance sheet, income statement
        // Plus view sales, purchases, returns for context
        $accountant->syncPermissions([
            'sales.view',
            'purchases.view',
            'returns.view',
            'payments.view',
            'reports.view',
            'financial.view',
        ]);

        // Cashier: create and view sales, returns, payments
        $cashier->syncPermissions([
            'sales.view',
            'sales.create',
            'returns.view',
            'returns.create',
            'payments.view',
            'payments.create',
            'reports.view',
        ]);

        // Storekeeper: manage master data, purchases, returns
        $storekeeper->syncPermissions([
            'master-data.manage',
            'purchases.view',
            'purchases.create',
            'returns.view',
            'returns.create',
            'reports.view',
        ]);

        // Create or update admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
            ]
        );

        $adminUser->syncRoles(['admin']);
    }
}