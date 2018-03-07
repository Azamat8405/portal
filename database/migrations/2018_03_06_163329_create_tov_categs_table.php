<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTovCategsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * Таблица это разделы товаров. Берем из [Imported_Data].[dbo].[AstHrhy].
     * Там разделы хранятся прямо у товара. Мы берем себе только разделы, чтобы быстрееработать с разделами
     * Время от времени нужно обновлять списокв текущей таблице из [Imported_Data].[dbo].[AstHrhy]
     */
    public function up()
    {
        Schema::create('tov_categs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('lvl1');
            $table->string('lvl2');
            $table->string('lvl3');
            $table->string('lvl4');
            $table->text('ids')->nullable();

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
        Schema::dropIfExists('tov_categs');
    }
}
