<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRamTypeToMotherboardsTable extends Migration
{
    public function up()
    {
        Schema::table('motherboards', function (Blueprint $table) {
            $table->string('ram_type')->default('DDR4')->after('chipset'); 
        });
    }

    public function down()
    {
        Schema::table('motherboards', function (Blueprint $table) {
            $table->dropColumn('ram_type');
        });
    }
}
