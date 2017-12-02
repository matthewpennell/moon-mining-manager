<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRefineriesTableAddCustomTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refineries', function (Blueprint $table) {
            $table->time('custom_detonation_time')->nullable()->after('claimed_by_secondary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refineries', function (Blueprint $table) {
            $table->dropColumn('custom_detonation_time');
        });
    }
}
