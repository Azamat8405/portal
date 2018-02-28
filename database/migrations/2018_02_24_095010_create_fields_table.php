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
        // здесь каждая строка - это столбец в таблице fields_x_values
        Schema::create('fields', function (Blueprint $table) {

            $table->increments('id')->unsigned();
            $table->integer('step_id')->unsigned();

            $table->string('title')->comment('Название поля(Название, скидка...)');
            $table->string('type')->comment('Тип поля в форме(В интерфейса. Строка, множественный выбор, галочка, выпадающий список)');
            $table->string('field_name')->comment('Реальное название столбца в таблице fields_x_values');

            $table->tinyInteger('always')->comment('Обязательно к заполнению');
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
