<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatReceivedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('received_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('purchase_order_item_id')->unsigned();
            $table->integer('quantity');
            $table->integer('status')->default(0);
            $table->dateTime('received_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('received_items');
    }
}
