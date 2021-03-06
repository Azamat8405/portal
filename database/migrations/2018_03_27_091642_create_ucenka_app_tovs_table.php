<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUcenkaAppTovsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ucenka_app_tovs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nomenklatury_kod');
            $table->string('nomenklatury_title');
            $table->string('srok_godnosty');

            $table->integer('ucenka_reason_id')->unsigned();
            $table->integer('ostatok')->unsigned();

            $table->integer('ucenka_app_id')->unsigned();

            $table->integer('user_id')->unsigned()->nullable()->comment('Согласователь из users');
            $table->integer('agreement_date')->unsigned()->nullable();

            $table->string('refusal_comment')->nullable();

            $table->integer('skidka')->unsigned()->nullable();
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
        Schema::dropIfExists('ucenka_app_tovs');
    }
}
