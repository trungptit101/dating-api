<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer("userId")->nullable();
            $table->integer("packageId")->nullable();
            $table->double("price")->default(0);
            $table->double("code")->nullable();
            $table->text("unit")->nullable();
            $table->text("months")->nullable();
            $table
                ->tinyInteger('payment_status')
                ->nullable()
                ->comment(
                    'Status Payment: 1: inProgress, 2: Cancel, 3: Complete'
                );
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
        Schema::dropIfExists('orders');
    }
}
