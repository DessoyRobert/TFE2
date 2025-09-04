<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            if (!Schema::hasColumn('builds', 'total_price')) {
                $table->decimal('total_price', 10, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('builds', 'component_count')) {
                $table->unsignedInteger('component_count')->default(0)->after('total_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            if (Schema::hasColumn('builds', 'component_count')) {
                $table->dropColumn('component_count');
            }
            if (Schema::hasColumn('builds', 'total_price')) {
                $table->dropColumn('total_price');
            }
        });
    }
};
