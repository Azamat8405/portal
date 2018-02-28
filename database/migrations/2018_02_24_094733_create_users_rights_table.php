<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_rights', function (Blueprint $table) {
            $table->increments('id')->unsigned();//->primary('id')
            $table->integer('user_id')->unsigned();

            $table->text('actions_types_rights');//action_id|read,edit,create,delete
            $table->text('steps_rights');//actions_steps_id|read,edit,create,delete
            $table->text('fields_rights');//actions_fields_id|read,edit,create,delete

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
        Schema::dropIfExists('users_rights');
    }
}
