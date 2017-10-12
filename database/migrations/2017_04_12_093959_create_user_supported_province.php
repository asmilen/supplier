<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSupportedProvince extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_supported_province', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supported_id');
            $table->integer('province_id');
            $table->integer('status');
            $table->timestamps();
        });
        Schema::table('product_supplier', function (Blueprint $table) {
            $table->integer('price_recommend')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_supported_province');
    }
}
