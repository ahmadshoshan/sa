<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('account_transactions')) {
            Schema::create('account_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts');
                $table->string('transaction_no');
                $table->string('type');
                $table->decimal('amount', 15, 2);
                $table->date('transaction_date');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['account_id', 'transaction_date']);
            });

            return;
        }

        Schema::table('account_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('account_transactions', 'account_id')) {
                $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            }

            if (! Schema::hasColumn('account_transactions', 'transaction_no')) {
                $table->string('transaction_no')->nullable();
            }

            if (! Schema::hasColumn('account_transactions', 'type')) {
                $table->string('type')->nullable();
            }

            if (! Schema::hasColumn('account_transactions', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('account_transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable();
            }

            if (! Schema::hasColumn('account_transactions', 'reference_type')) {
                $table->string('reference_type')->nullable();
            }

            if (! Schema::hasColumn('account_transactions', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable();
            }

            if (! Schema::hasColumn('account_transactions', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (! Schema::hasColumn('account_transactions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        if (
            Schema::hasColumn('account_transactions', 'account_id')
            && Schema::hasColumn('account_transactions', 'transaction_date')
            && ! Schema::hasIndex('account_transactions', ['account_id', 'transaction_date'])
        ) {
            Schema::table('account_transactions', function (Blueprint $table) {
                $table->index(['account_id', 'transaction_date']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('account_transactions')) {
            return;
        }

        Schema::table('account_transactions', function (Blueprint $table) {
            if (Schema::hasIndex('account_transactions', ['account_id', 'transaction_date'])) {
                $table->dropIndex(['account_id', 'transaction_date']);
            }

            foreach ([
                'account_id',
                'transaction_no',
                'type',
                'amount',
                'transaction_date',
                'reference_type',
                'reference_id',
                'notes',
                'user_id',
            ] as $column) {
                if (Schema::hasColumn('account_transactions', $column)) {
                    if (in_array($column, ['account_id', 'user_id'], true)) {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
