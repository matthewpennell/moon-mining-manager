<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRentalPaymentsAndInvoicesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_invoices', function (Blueprint $table) {
            $table->bigInteger('refinery_id')->after('renter_id');
        });
        Schema::table('rental_payments', function (Blueprint $table) {
            $table->bigInteger('refinery_id')->after('renter_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rental_invoices', function (Blueprint $table) {
            $table->dropColumn('refinery_id');
        });
        Schema::table('rental_payments', function (Blueprint $table) {
            $table->dropColumn('refinery_id');
        });
    }
}
