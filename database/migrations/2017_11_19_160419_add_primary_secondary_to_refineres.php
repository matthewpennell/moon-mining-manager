<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrimarySecondaryToRefineres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refineries', function (Blueprint $table) {
            $table->integer('claimed_by_secondary')->after('claimed_by')->nullable();
            $table->renameColumn('claimed_by', 'claimed_by_primary');
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
            $table->dropColumn('claimed_by_secondary');
            $table->renameColumn('claimed_by_primary', 'claimed_by');
        });
    }
}
