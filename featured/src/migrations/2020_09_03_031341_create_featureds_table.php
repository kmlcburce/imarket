<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featureds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('quota')->nullable();
            $table->double('discount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('featureds');
    }
}
