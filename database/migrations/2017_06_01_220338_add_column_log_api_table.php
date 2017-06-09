<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLogApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_api', function (Blueprint $table) {
            $table->text('device')->nullable();
            $table->text('url')->nullable();
            $table->text('method')->nullable();
            $table->text('ip_address')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_api', function (Blueprint $table) {
            //
        });
    }
}
