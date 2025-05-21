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
    Schema::create('storages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
        $table->string('type'); // SSD, HDD, NVMe
        $table->unsignedSmallInteger('capacity_gb');
        $table->string('interface');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storages');
    }
};
