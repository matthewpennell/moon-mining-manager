<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefineriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refineries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('observer_id');
            $table->string('observer_type', 100);
            $table->string('name', 255)->nullable();
            $table->integer('solar_system_id')->nullable();
            $table->decimal('income', 17, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refineries');
    }
}
