<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('components', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->after('brand');
            $table->unsignedBigInteger('category_id')->nullable()->after('type');

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn('brand_id');
            $table->dropColumn('category_id');
        });
    }
};
