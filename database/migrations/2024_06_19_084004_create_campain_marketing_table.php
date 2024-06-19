<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampainMarketingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campain_marketing', function (Blueprint $table) {
            $table->id();
            $table->text("gender")->nullable();
            $table->text("start")->nullable();
            $table->integer("discount")->nullable();
            $table->text("end")->nullable();
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
        Schema::dropIfExists('campain_marketing');
    }
}
