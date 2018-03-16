<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrendsCategsLinksTable extends Migration
{
    /**
     * Run the migrations.
     * Один бренд может относиться к нескольким категориям.
     * @return void
     */
    public function up()
    {
        Schema::create('brends_categs_links', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('brend_id');
            $table->integer('categ_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brends_categs_links');
    }
}
