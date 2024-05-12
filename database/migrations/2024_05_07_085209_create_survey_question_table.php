<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_question', function (Blueprint $table) {
            $table->id();
            $table->text('question')->nullable();
            $table->text('slug')->nullable();
            $table->longText('options')->nullable();
            $table->integer('type')->default(0)->comment('0: nhập câu trả lời, 1: 1 lựa chọn, 2: nhiều lựa chọn');
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
        Schema::dropIfExists('survey_question');
    }
}
