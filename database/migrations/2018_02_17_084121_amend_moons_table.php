<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmendMoonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('moons', function (Blueprint $table) {
            $table->renameColumn('mineral_1', 'mineral_1_type_id');
            $table->renameColumn('mineral_2', 'mineral_2_type_id');
            $table->renameColumn('mineral_3', 'mineral_3_type_id');
            $table->renameColumn('mineral_4', 'mineral_4_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('moons', function (Blueprint $table) {
            //
        });
    }
}
