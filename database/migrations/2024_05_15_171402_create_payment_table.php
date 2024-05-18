<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->integer("userId")->nullable();
            $table->text("fistName")->nullable();
            $table->text("lastName")->nullable();
            $table->text("address")->nullable();
            $table->text("country")->nullable();
            $table->text("state")->nullable();
            $table->text("city")->nullable();
            $table->text("postCode")->nullable();
            $table->text("cardNumber")->nullable();
            $table->text("securityCode")->nullable();
            $table->text("expirationDate")->nullable();
            $table->text("expirationYear")->nullable();
            $table->boolean("isComplete")->default(false);
            $table->integer("packageId")->nullable();
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
        Schema::dropIfExists('payment');
    }
}
