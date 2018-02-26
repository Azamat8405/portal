<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValuesDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('values_detail', function (Blueprint $table) {
            $table->increments('id')->unsigned();//->primary('id')

            $table->integer('field_value_id')->unsigned();
            $table->integer('shop_id')->unsigned();
            $table->string('value');

            //TODO внешний ключ ?????

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
        Schema::dropIfExists('values_detail');
    }
}
