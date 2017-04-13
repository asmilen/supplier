<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegionIdUserSupportedProvine extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_supported_province', function (Blueprint $table) {
            $table->integer('region_id')->nullable();
        });
        Schema::table('provinces', function (Blueprint $table) {
            $table->integer('region_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_supported_province', function (Blueprint $table) {
            //
        });
    }
}
