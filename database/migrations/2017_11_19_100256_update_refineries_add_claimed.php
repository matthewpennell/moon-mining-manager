<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRefineriesAddClaimed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refineries', function (Blueprint $table) {
            $table->integer('claimed_by')->after('natural_decay_time')->nullable();
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
            $table->dropColumn('claimed_by');
        });
    }
}
