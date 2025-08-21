<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Polymorphe vers Build OU Component (purchasable)
            $table->morphs('purchasable'); // purchasable_type, purchasable_id

            $table->unsignedInteger('quantity')->default(1);

            // Prix figé au moment de la commande
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);

            $table->json('snapshot')->nullable(); // nom, image, specs “gelées”

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_items');
    }
};
