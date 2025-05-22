<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('build_component', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('build_id');
            $table->unsignedBigInteger('component_id');
            $table->string('position')->nullable(); // ex: cpu, gpu, ram...

            $table->timestamps();

            $table->foreign('build_id')->references('id')->on('builds')->onDelete('cascade');
            $table->foreign('component_id')->references('id')->on('components')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('build_component');
    }
};
