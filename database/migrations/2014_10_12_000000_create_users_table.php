<?php

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

        Schema::create('teams',function (Blueprint $table) {
            $table->increments('id');
            $table->string('slack_domain')->unique();
            $table->string('slack_id')->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id')->unsigned();
            $table->string('slack_id')->unique();
            $table->string('slack_name');
            $table->string('sex',1);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams');
            $table->index('team_id');
        });

        Schema::create('movements', function (Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('hash')->unique();//"Bent-over Barbell Row" stored as "bentoverbarbellrow"

        });

        Schema::create('lifts', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->integer('movement_id')->unsigned();
            $table->integer('grams')->unsigned();
            $table->integer('bodygrams')->unsigned();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('movement_id')->references('id')->on('movements');


            $table->index('user_id');
            $table->index('movement_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lifts');
        Schema::drop('movements');
        Schema::drop('users');
        Schema::drop('teams');
    }
}
