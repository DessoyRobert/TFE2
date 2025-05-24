<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('component_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // cpu, gpu, ram, etc.
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('component_types');
    }
};
