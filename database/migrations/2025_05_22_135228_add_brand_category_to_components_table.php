<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('components', function (Blueprint $table) {
            // Ajoute les foreign keys si elles n’existent pas déjà
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            // (optionnel) Supprime les anciens champs si besoin
            // $table->dropColumn('brand');
            // $table->dropColumn('type');
        });
    }
    public function down(): void {
        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn('brand_id');
            $table->dropColumn('category_id');

            // (optionnel) Remets les anciens champs si besoin
            // $table->string('brand')->nullable();
            // $table->string('type')->nullable();
        });
    }
};
