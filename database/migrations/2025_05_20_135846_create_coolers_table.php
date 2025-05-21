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
    Schema::create('coolers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
        $table->string('type'); // air/liquid
        $table->unsignedTinyInteger('fan_count');
        $table->string('compatible_sockets');
        $table->unsignedSmallInteger('max_tdp')->nullable();
        $table->unsignedSmallInteger('height_mm')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coolers');
    }
};
