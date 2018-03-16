<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_types', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title')->comment('Название акции');
            $table->integer('dedlain')->comment('Дедлайн процесса');
            $table->string('description')->comment('Тип поцесса (Газета, ...)');

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
        Schema::dropIfExists('process_types');
    }
}
