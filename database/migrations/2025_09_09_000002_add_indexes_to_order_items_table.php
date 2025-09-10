<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('order_items', function (Blueprint $table) {
            try { $table->index('order_id', 'order_items_order_id_idx'); } catch (\Throwable $e) {}
            try { $table->index(['purchasable_type', 'purchasable_id'], 'order_items_purchasable_idx'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void {
        Schema::table('order_items', function (Blueprint $table) {
            try { $table->dropIndex('order_items_order_id_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('order_items_purchasable_idx'); } catch (\Throwable $e) {}
        });
    }
};
