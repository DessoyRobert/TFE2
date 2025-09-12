<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('method', 50)->default('bank_transfer'); // virement
            $table->string('status', 32)->default('pending'); // pending|paid|failed|refunded (MVP: pending/paid)
            $table->string('transaction_id', 191)->nullable(); // vide en offline
            $table->jsonb('meta')->nullable(); // IBAN, BIC, référence, etc.
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->index(['order_id']);
            $table->index(['status']);
            $table->index(['method']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
