<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditCheckoutItemsTableChangeSizeQtyProductAttributesId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('checkout_items', function (Blueprint $table) {
            $table->dropColumn('size');
            $table->dropColumn('color');
            $table->bigInteger('product_attribute_id')->after('payload_value');
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
