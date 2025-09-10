<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Mettre tous les NULL à false avant d’ajouter la contrainte
        DB::table('builds')
            ->whereNull('is_public')
            ->update(['is_public' => false]);

        Schema::table('builds', function (Blueprint $table) {
            $table->boolean('is_public')
                ->default(false)
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('builds', function (Blueprint $table) {
            $table->boolean('is_public')
                ->nullable()
                ->default(null)
                ->change();
        });
    }
};
