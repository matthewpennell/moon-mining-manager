<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMinerActivityLogColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('miners', function (Blueprint $table) {
            $table->dropColumn('activity_log');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('miners', function (Blueprint $table) {
            $table->text('activity_log')->after('amount_owed')->nullable();
        });
    }
}
