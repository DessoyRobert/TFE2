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
    Schema::create('components', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('brand');
        $table->enum('type', [
            'cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'case'
        ]);
        $table->decimal('price', 10, 2)->nullable();
        $table->string('img_url')->nullable();
        $table->text('description')->nullable();
        $table->year('release_year')->nullable();
        $table->string('ean')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
