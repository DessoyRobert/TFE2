<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id');
            $table->string('url');
            $table->timestamps();

            $table->foreign('component_id')->references('id')->on('components')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('images');
    }
};
