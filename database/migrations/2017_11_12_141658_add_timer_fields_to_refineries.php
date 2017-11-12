<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimerFieldsToRefineries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refineries', function (Blueprint $table) {
            $table->timestampTz('natural_decay_time')->nullable()->after('observer_id');
            $table->timestampTz('chunk_arrival_time')->nullable()->after('observer_id');
            $table->timestampTz('extraction_start_time')->nullable()->after('observer_id');
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
            $table->dropColumn('natural_decay_time');
            $table->dropColumn('chunk_arrival_time');
            $table->dropColumn('extraction_start_time');
        });
    }
}
