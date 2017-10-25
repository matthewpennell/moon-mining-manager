<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('password');
            $table->string('token', 255)->after('name');
            $table->string('avatar', 255)->after('name');
            $table->string('eve_id', 10)->after('id')->unique();
            $table->string('refresh_token', 255)->after('token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token');
            $table->dropColumn('avatar');
            $table->string('email', 255)->after('name');
            $table->unique('email');
            $table->string('password', 255)->after('email');
            $table->dropColumn('eve_id');
            $table->dropColumn('refresh_token');
        });
    }
}
