<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If payments table does not exist, create it with core columns to ensure stability.
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_no')->unique();
                $table->string('type');
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('payment_method')->default('cash');
                $table->date('payment_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });

            // Nothing more to do on fresh creation.
            return;
        }

        // Ensure expected optional columns exist and add foreign keys when possible.
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'installment_id')) {
                if (Schema::hasTable('invoice_installments')) {
                    $table->foreignId('installment_id')->nullable()->constrained('invoice_installments')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('installment_id')->nullable();
                }
            }

            if (!Schema::hasColumn('payments', 'expense_id')) {
                if (Schema::hasTable('expenses')) {
                    $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('expense_id')->nullable();
                }
            }

            if (!Schema::hasColumn('payments', 'account_id')) {
                if (Schema::hasTable('accounts')) {
                    $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('account_id')->nullable();
                }
            }

            if (!Schema::hasColumn('payments', 'distribution_item_id')) {
                if (Schema::hasTable('partner_distribution_items')) {
                    $table->foreignId('distribution_item_id')->nullable()->constrained('partner_distribution_items')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('distribution_item_id')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'installment_id')) {
                // dropConstrainedForeignId is available in newer Laravel; guard for safety
                try {
                    $table->dropConstrainedForeignId('installment_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('installment_id');
                }
            }

            if (Schema::hasColumn('payments', 'expense_id')) {
                try {
                    $table->dropConstrainedForeignId('expense_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('expense_id');
                }
            }

            if (Schema::hasColumn('payments', 'account_id')) {
                try {
                    $table->dropConstrainedForeignId('account_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('account_id');
                }
            }

            if (Schema::hasColumn('payments', 'distribution_item_id')) {
                try {
                    $table->dropConstrainedForeignId('distribution_item_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('distribution_item_id');
                }
            }
        });
    }
};
