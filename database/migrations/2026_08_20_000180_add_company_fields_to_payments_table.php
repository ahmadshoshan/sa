<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'expense_id')) {
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            }

            if (!Schema::hasColumn('payments', 'account_id')) {
                $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'expense_id')) {
                $table->dropConstrainedForeignId('expense_id');
            }

            if (Schema::hasColumn('payments', 'account_id')) {
                $table->dropConstrainedForeignId('account_id');
            }
        });
    }
};