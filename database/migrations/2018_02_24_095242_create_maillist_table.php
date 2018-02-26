<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaillistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maillist', function (Blueprint $table) {

            $table->increments('id')->unsigned();
            $table->string('title');
            $table->text('body');

            $table->string('calendar', 11)->nullable()->comment('Календарь рассылки.Храним в формате crontab');
            $table->integer('one_time')->nullable()->comment('Отправляем только в указнное время');//->nullable()

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
        Schema::dropIfExists('maillist');
    }
}
