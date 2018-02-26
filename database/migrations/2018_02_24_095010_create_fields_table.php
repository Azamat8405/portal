<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {

            $table->increments('id')->unsigned();//->primary('id')
            $table->string('title');
            $table->string('type');// Строка, множественный выбор, галочка

            $table->integer('step_id')->unsigned();
            $table->tinyInteger('always')->comment('Обязательно к заполнению');// Строка, множественный выбор, галочка
            $table->tinyInteger('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fields');
    }
}
