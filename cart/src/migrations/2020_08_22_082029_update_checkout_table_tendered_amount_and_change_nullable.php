<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCheckoutTableTenderedAmountAndChangeNullable extends Migration
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
            $table->double('tendered_amount', 8, 2)->nullable()->change();
            $table->double('change', 8, 2)->nullable()->change();
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
