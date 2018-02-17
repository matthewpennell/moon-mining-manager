<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id');
            $table->integer('solar_system_id');
            $table->tinyInteger('planet');
            $table->tinyInteger('moon');
            $table->integer('mineral_1');
            $table->decimal('mineral_1_percent', 4, 2);
            $table->integer('mineral_2');
            $table->decimal('mineral_2_percent', 4, 2);
            $table->integer('mineral_3')->nullable();
            $table->decimal('mineral_3_percent', 4, 2)->nullable();
            $table->integer('mineral_4')->nullable();
            $table->decimal('mineral_4_percent', 4, 2)->nullable();
            $table->decimal('monthly_rental_fee', 17, 2);
            $table->integer('renter_id')->nullable();
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
        Schema::dropIfExists('moons');
    }
}
