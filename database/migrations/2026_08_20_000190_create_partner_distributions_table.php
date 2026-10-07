<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('distribution_no')->unique();
            $table->string('period_type')->default('custom');
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('reserve_amount', 15, 2)->default(0);
            $table->decimal('distribute_amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_distributions');
    }
};