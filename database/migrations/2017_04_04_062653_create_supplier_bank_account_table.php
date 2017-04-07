<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierBankAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supplier_id');
            $table->string('bank_name');
            $table->string('bank_account');
            $table->string('bank_branch')->nullable();
            $table->string('bank_province');
            $table->integer('status')->default(0);
            $table->integer('is_default')->default(0);
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
        Schema::dropIfExists('supplier_bank_accounts');
    }
}
