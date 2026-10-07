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
  Schema::create('stock_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
    $table->string('movement_type');
    $table->decimal('quantity', 15, 3);
    $table->string('ref_type')->nullable();
    $table->unsignedBigInteger('ref_id')->nullable();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->text('notes')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
