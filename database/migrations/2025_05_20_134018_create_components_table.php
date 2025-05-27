<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('component_type_id')->constrained('component_types');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('img_url')->nullable();
            $table->text('description')->nullable();
            $table->year('release_year')->nullable();
            $table->string('ean')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
