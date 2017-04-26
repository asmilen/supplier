<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarginRegionCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('margin_region_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('margin')->default(0);
            $table->integer('region_id')->default(0);
            $table->integer('category_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('margin_region_category');
    }
}
