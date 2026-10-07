<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fund_transfers')) {
            Schema::create('fund_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_no')->unique();
                $table->foreignId('from_account_id')->constrained('accounts');
                $table->foreignId('to_account_id')->constrained('accounts');
                $table->decimal('amount', 15, 2);
                $table->date('transfer_date');
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            return;
        }

        Schema::table('fund_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('fund_transfers', 'transfer_no')) {
                $table->string('transfer_no')->unique();
            }

            if (!Schema::hasColumn('fund_transfers', 'from_account_id')) {
                $table->foreignId('from_account_id')->constrained('accounts');
            }

            if (!Schema::hasColumn('fund_transfers', 'to_account_id')) {
                $table->foreignId('to_account_id')->constrained('accounts');
            }

            if (!Schema::hasColumn('fund_transfers', 'amount')) {
                $table->decimal('amount', 15, 2);
            }

            if (!Schema::hasColumn('fund_transfers', 'transfer_date')) {
                $table->date('transfer_date');
            }

            if (!Schema::hasColumn('fund_transfers', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (!Schema::hasColumn('fund_transfers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fund_transfers', function (Blueprint $table) {
            foreach (['from_account_id', 'to_account_id', 'user_id'] as $column) {
                if (Schema::hasColumn('fund_transfers', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['transfer_no', 'amount', 'transfer_date', 'notes'] as $column) {
                if (Schema::hasColumn('fund_transfers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
