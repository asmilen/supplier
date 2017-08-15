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
            $table->string('user_id');
            $table->string('product_id');
            $table->string('detail', 255);
            $table->string('post_data', 255);
            $table->string('response', 255);
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
