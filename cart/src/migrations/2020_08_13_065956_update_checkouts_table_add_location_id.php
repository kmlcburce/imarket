<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCheckoutsTableAddLocationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('checkouts', function (Blueprint $table) {
            $table->string('currency')->after('payment_status');
            $table->double('shipping_fee')->after('currency');
            $table->bigInteger('location_id')->after('shipping_fee');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
