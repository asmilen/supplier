<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CteateMarginRegionSupplierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('margin_region_supplier', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('margin')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->integer('region_id')->default(0);
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
        Schema::dropIfExists('margin_region_supplier');
    }
}
