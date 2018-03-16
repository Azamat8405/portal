<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStepsTable extends Migration
{
    /**
     * Run the migrations.
     * Таблица Этапов(). Каждая акция (таблица actions) имеет в своем составе этапы
     * @return void
     */
    public function up()
    {
        Schema::create('steps', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('process_id')->unsigned();
            $table->string('title')->comment('Название этапа процесса(узла бизнес процесса)');

            $table->text('conditions')->comment('Условия перехода на текущий этап');

            $table->string('from_ids')->comment('ID того(ех) шага(ов) из которых процесс приходит в текущий');
            $table->string('to_ids')->comment('ID того(ех) шага(ов) в который(ые) процесс уходит, после выполнения текущего');

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
        Schema::dropIfExists('steps');
    }
}
