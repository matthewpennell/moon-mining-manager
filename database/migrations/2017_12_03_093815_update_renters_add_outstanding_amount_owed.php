<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRentersAddOutstandingAmountOwed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('renters', function (Blueprint $table) {
            $table->decimal('amount_owed', 17, 2)->default(0)->after('monthly_rental_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('renters', function (Blueprint $table) {
            $table->dropColumn('amount_owed');
        });
    }
}
