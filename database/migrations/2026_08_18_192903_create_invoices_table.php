<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
  Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_no')->unique();
    $table->string('type');
    $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
    $table->date('invoice_date');
    $table->date('due_date')->nullable();
    $table->decimal('subtotal', 15, 2)->default(0);
    $table->decimal('discount', 15, 2)->default(0);
    $table->decimal('tax', 15, 2)->default(0);
    $table->decimal('total', 15, 2)->default(0);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->decimal('remaining_amount', 15, 2)->default(0);
    $table->string('status')->default('draft');
    $table->text('notes')->nullable();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
