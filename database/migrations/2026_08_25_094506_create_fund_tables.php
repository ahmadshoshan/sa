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
        }

        if (!Schema::hasTable('account_transactions')) {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('fund_transfers');
    }
};