<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('step_values', function (Blueprint $table){
            $table->increments('id')->unsigned();

            $table->integer('shop_id')->unsigned();
            $table->integer('action_id')->unsigned();

            $table->string('kod_dis')->comment('код ДиС Ном. Номер');
            $table->string('articule_sk')->comment('Артикул ШК это артикул по базе поставщика');

            $table->string('on_invoice');
            $table->string('on_invoice_start')->comment('Дата начала предоставления скидки он инвойс');
            $table->string('on_invoice_end')->comment('Дата окончания предоставления скидки он инвойс');

            $table->string('off_invoice');
            $table->string('skidka_itogo');

            $table->string('old_zakup_price');
            $table->string('new_zakup_price');

            $table->string('old_roznica_price');
            $table->string('new_roznica_price');

            $table->text('description')->comment('подписи, слоганы, расшифровки и пояснения, которые Вы хотели бы видеть к своим товарам.');

            $table->text('metka')->comment('Хит, Новинка, Суперцена, Выгода 0000  рублей...');
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
        Schema::dropIfExists('fields_values');
    }
}
