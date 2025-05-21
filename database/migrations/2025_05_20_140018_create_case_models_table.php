<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('case_models', function (Blueprint $table) {
        $table->id();
        $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
        $table->string('form_factor');
        $table->unsignedSmallInteger('max_gpu_length')->nullable();
        $table->unsignedSmallInteger('max_cooler_height')->nullable();
        $table->string('psu_form_factor')->nullable();
        $table->unsignedTinyInteger('fan_mounts')->nullable();
        $table->timestamps();
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_models');
    }
};
