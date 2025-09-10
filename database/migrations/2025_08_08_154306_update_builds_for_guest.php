<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('builds', function (Blueprint $table) {
            // Autorise la création sans utilisateur
            $table->foreignId('user_id')->nullable()->change();

            // Ajoute un code unique pour retrouver le build d’un invité
            if (!Schema::hasColumn('builds', 'build_code')) {
                $table->string('build_code', 20)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void {
        Schema::table('builds', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn('build_code');
        });
    }
};
