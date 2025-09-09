<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'idempotency_key')) {
                $table->string('idempotency_key', 80)->nullable()->unique();
            }
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'idempotency_key')) {
                try { $table->dropUnique(['idempotency_key']); } catch (\Throwable $e) {}
                $table->dropColumn('idempotency_key');
            }
        });
    }
};
