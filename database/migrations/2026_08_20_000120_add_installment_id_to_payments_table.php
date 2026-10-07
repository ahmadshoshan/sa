<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            // Payments table does not yet exist — skip adding installment_id.
            // This migration will not fail when migrations are run in different order
            // (some environments may create payments table later).
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'installment_id')) {
                $table->foreignId('installment_id')
                    ->nullable()
                    ->constrained('invoice_installments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('installment_id');
        });
    }
};