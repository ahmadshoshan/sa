<?php

namespace App\Providers;

use App\Console\Commands\BackupDatabase;
use App\Console\Commands\CleanBackups;
use App\Console\Commands\CleanLogs;
use App\Console\Commands\NotifyDueInstallments;
use App\Console\Commands\OptimizeDatabase;
use App\Console\Commands\ResetSystem;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->commands([
            BackupDatabase::class,
            NotifyDueInstallments::class,
            ResetSystem::class,
            CleanBackups::class,
            CleanLogs::class,
            OptimizeDatabase::class,
        ]);

        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);

        User::observe(AuditObserver::class);
        Customer::observe(AuditObserver::class);
        Supplier::observe(AuditObserver::class);
        Product::observe(AuditObserver::class);
        Invoice::observe(AuditObserver::class);
        Payment::observe(AuditObserver::class);

        foreach ([
            'accounting', 'financial', 'income', 'admin', 'pos', 'printing',
            'part1', 'part2', 'company', 'distributions', 'company_reports',
            'reset', 'dashboard', 'assistant', 'guide', 'advanced_reports', 'settings_accent', 'assistant_guide',
        ] as $routeFile) {
            if (file_exists(base_path("routes/{$routeFile}.php"))) {
                Route::middleware(['web', 'auth'])->group(base_path("routes/{$routeFile}.php"));
            }
        }
    }
}