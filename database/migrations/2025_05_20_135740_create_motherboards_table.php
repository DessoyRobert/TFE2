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
    Schema::create('motherboards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
        $table->string('socket');
        $table->string('chipset');
        $table->string('form_factor');
        $table->unsignedTinyInteger('ram_slots');
        $table->unsignedSmallInteger('max_ram');
        $table->unsignedTinyInteger('pcie_slots');
        $table->unsignedTinyInteger('m2_slots')->nullable();
        $table->unsignedTinyInteger('sata_ports')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motherboards');
    }
};
