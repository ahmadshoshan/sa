<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['partner_capitals', 'partner_withdrawals', 'payments'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'idempotency_key')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('idempotency_key')->nullable()->unique();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['partner_capitals', 'partner_withdrawals', 'payments'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'idempotency_key')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropUnique(['idempotency_key']);
                    $table->dropColumn('idempotency_key');
                });
            }
        }
    }
};
