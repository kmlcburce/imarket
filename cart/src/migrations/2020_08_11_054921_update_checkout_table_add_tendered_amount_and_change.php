<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCheckoutTableAddTenderedAmountAndChange extends Migration
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
            $table->double('tendered_amount')->after('total');
            $table->double('change')->after('tendered_amount');
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
