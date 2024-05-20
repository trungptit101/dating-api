<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvatarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText("avatar")->nullable();
            $table->text("favorite")->nullable();
            $table->text("weight")->nullable();
            $table->text("height")->nullable();
            $table->text("skin_color")->nullable();
            $table->text("blood_group")->nullable();
            $table->text("eye_color")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
