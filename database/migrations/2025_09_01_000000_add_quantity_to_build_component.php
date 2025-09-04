<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('build_component', 'quantity')) {
            Schema::table('build_component', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(1)->after('component_id');
            });
        }
        if (!Schema::hasColumn('build_component', 'price_at_addition')) {
            Schema::table('build_component', function (Blueprint $table) {
                $table->decimal('price_at_addition', 10, 2)->nullable()->after('quantity');
            });
        }
    }

    public function down(): void
    {
        Schema::table('build_component', function (Blueprint $table) {
            if (Schema::hasColumn('build_component', 'price_at_addition')) {
                $table->dropColumn('price_at_addition');
            }
            if (Schema::hasColumn('build_component', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
