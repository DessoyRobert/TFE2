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
        Schema::table('gpus', function (Blueprint $table) {
            $table->integer('recommended_wattage')->nullable()->after('tdp');
        });
    }

    public function down(): void
    {
        Schema::table('gpus', function (Blueprint $table) {
            $table->dropColumn('recommended_wattage');
        });
    }

};
