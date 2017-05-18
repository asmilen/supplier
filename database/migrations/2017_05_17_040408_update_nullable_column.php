<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNullableColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
            $table->string('fax')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('contact_name')->nullable()->change();
            $table->string('contact_mobile')->nullable()->change();
            $table->string('contact_phone')->nullable()->change();
            $table->string('contact_email')->nullable()->change();

        });
        Schema::table('supplier_addresses', function (Blueprint $table) {
            $table->integer('province_id')->unsigned()->nullable()->change();
            $table->integer('district_id')->unsigned()->nullable()->change();
            $table->string('province_name')->nullable()->change();
            $table->string('district_name')->nullable()->change();
            $table->string('contact_name')->nullable()->change();
            $table->string('contact_mobile')->nullable()->change();
            $table->string('contact_phone')->nullable()->change();
            $table->string('contact_email')->nullable()->change();
        });
        Schema::table('supplier_bank_accounts', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->change();
            $table->string('bank_account')->nullable()->change();
            $table->string('bank_branch')->nullable()->change();
            $table->string('bank_province')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
