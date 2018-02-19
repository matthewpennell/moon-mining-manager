<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreviousMonthFeeToMoons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('moons', function (Blueprint $table) {
            $table->decimal('previous_monthly_rental_fee', 17, 2)->after('monthly_rental_fee');
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
            $table->dropColumn('previous_monthly_rental_fee');
        });
    }
}
