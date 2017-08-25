<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogPostPriceToMgtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_price_to_mgt_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id');
            $table->integer('product_id');
            $table->text('detail');
            $table->text('post_data');
            $table->text('response');
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
        Schema::dropIfExists('post_price_to_mgt_logs');
    }
}
