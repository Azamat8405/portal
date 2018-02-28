<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            // $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->comment('ФИО пользователя');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');

            $table->integer('user_group_id')->unsigned();

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
