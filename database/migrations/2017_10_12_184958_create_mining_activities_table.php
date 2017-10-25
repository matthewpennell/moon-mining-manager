<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiningActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('miner_id');
            $table->integer('refinery_id');
            $table->integer('type_id');
            $table->integer('quantity');
            $table->timestamps();
            $table->index('miner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mining_activities');
    }
}
