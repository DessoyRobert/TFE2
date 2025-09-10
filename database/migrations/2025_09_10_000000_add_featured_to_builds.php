<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->index();
            $table->tinyInteger('featured_rank')->nullable()->index(); // 1..3
        });
    }

    public function down(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->dropColumn(['is_featured','featured_rank']);
        });
    }
};
