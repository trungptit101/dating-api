<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_package', function (Blueprint $table) {
            $table->text("gender")->nullable();
            $table->text("price_paypal")->nullable();
            $table->text("price_vnpay")->nullable();
            $table->dropColumn('unit');
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_package', function (Blueprint $table) {
        });
    }
}
