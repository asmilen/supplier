<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id')->index();
            $table->unsignedInteger('manufacturer_id')->index();
            $table->tinyInteger('type')->default(0); // 0: simple / 1: configurable
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('source_url')->nullable();
            $table->string('old_sku')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
