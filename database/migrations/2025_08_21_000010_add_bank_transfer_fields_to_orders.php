<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('orders', function (Blueprint $t) {
      $t->string('bank_reference')->nullable()->index(); // ex: JT-20250821-000123
      $t->timestampTz('payment_deadline')->nullable();
      $t->timestampTz('payment_received_at')->nullable();
    });
  }
  public function down(): void {
    Schema::table('orders', function (Blueprint $t) {
      $t->dropColumn(['bank_reference','payment_deadline','payment_received_at']);
    });
  }
};