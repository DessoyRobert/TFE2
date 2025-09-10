<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::table('gpus', function (Blueprint $table) {
            $table->integer('length_mm')->default(250)->after('boost_clock'); 
        });
    }

    public function down()
    {
        Schema::table('gpus', function (Blueprint $table) {
            $table->dropColumn('length_mm');
        });
    }

};
