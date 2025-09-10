<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Lié à un user connecté (facultatif)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Infos client (pour guest ou override)
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email')->index();
            $table->string('customer_phone')->nullable();

            // Adresse livraison (simple, extensible ensuite)
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_postal_code');
            $table->string('shipping_country', 2)->default('BE');

            // Montants (toujours recalculés côté serveur)
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);

            // Statuts & paiements
            $table->string('status')->default('pending'); // pending, paid, preparing, shipped, delivered, canceled, refunded
            $table->string('payment_method')->nullable(); // stripe, paypal, virement...
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed, refunded
            $table->string('currency', 3)->default('EUR');

            // Métadonnées
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
