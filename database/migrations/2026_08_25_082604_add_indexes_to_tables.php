<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    if (!Schema::hasIndex('invoices', ['type', 'invoice_date'])) {
        Schema::table('invoices', function ($table) {
            $table->index(['type', 'invoice_date']);
        });
    }

    if (!Schema::hasIndex('invoices', ['customer_id', 'invoice_date'])) {
        Schema::table('invoices', function ($table) {
            $table->index(['customer_id', 'invoice_date']);
        });
    }

    if (!Schema::hasIndex('invoice_items', ['product_id', 'invoice_id'])) {
        Schema::table('invoice_items', function ($table) {
            $table->index(['product_id', 'invoice_id']);
        });
    }

    if (!Schema::hasIndex('journal_lines', ['account_id', 'journal_entry_id'])) {
        Schema::table('journal_lines', function ($table) {
            $table->index(['account_id', 'journal_entry_id']);
        });
    }

    if (!Schema::hasIndex('stock_movements', ['product_id', 'warehouse_id', 'created_at'])) {
        Schema::table('stock_movements', function ($table) {
            $table->index(['product_id', 'warehouse_id', 'created_at']);
        });
    }

    foreach (['question', 'intent', 'times_asked'] as $column) {
        if (!Schema::hasIndex('assistant_memory', [$column])) {
            Schema::table('assistant_memory', function ($table) use ($column) {
                $table->index($column);
            });
        }
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
