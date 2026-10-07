<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'original_invoice_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('original_invoice_id')
                    ->nullable()
                    ->constrained('invoices')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('expenses', 'idempotency_key')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('idempotency_key')->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expenses', 'idempotency_key')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropUnique(['idempotency_key']);
                $table->dropColumn('idempotency_key');
            });
        }

        if (Schema::hasColumn('invoices', 'original_invoice_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('original_invoice_id');
            });
        }
    }
};
