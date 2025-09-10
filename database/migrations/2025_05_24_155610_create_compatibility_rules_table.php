<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('compatibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_type_a_id')->constrained('component_types')->onDelete('cascade');
            $table->foreignId('component_type_b_id')->constrained('component_types')->onDelete('cascade');
            $table->string('rule_type'); // 'hard' ou 'soft'
            $table->string('field_a')->nullable();
            $table->string('field_b')->nullable(); 
            $table->string('operator')->nullable(); // '=', '!=', '<', etc.
            $table->text('description')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compatibility_rules');
    }
};
