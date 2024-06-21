<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfoInternetBankingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internet_banking', function (Blueprint $table) {
            $table->text("account_name")->nullable();
            $table->text("account_number")->nullable();
            $table->text("bank")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_banking', function (Blueprint $table) {
            //
        });
    }
}
