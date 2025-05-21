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
    Schema::create('gpus', function (Blueprint $table) {
        $table->id();
        $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
        $table->string('chipset');
        $table->unsignedSmallInteger('vram'); // en Go
        $table->unsignedSmallInteger('base_clock')->nullable(); // MHz
        $table->unsignedSmallInteger('boost_clock')->nullable(); // MHz
        $table->unsignedSmallInteger('tdp')->nullable(); // Watts
        $table->unsignedSmallInteger('length_mm')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gpus');
    }
};
