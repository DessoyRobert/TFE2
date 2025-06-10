<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
            $table->integer('wattage');
            $table->string('certification', 50);
            $table->boolean('modular');
            $table->string('form_factor', 20)->nullable(); // optionnel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psus');
    }
};
