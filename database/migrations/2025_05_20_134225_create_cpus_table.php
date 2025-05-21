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
    Schema::create('cpus', function (Blueprint $table) {
        $table->id();
        $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
        $table->string('socket');
        $table->unsignedTinyInteger('core_count');
        $table->unsignedTinyInteger('thread_count');
        $table->decimal('base_clock', 4, 2)->nullable();    // GHz
        $table->decimal('boost_clock', 4, 2)->nullable();   // GHz
        $table->unsignedSmallInteger('tdp')->nullable();    // Watts
        $table->string('integrated_graphics')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpus');
    }
};
