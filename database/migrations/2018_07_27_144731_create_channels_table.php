<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('channel_name');
            $table->string('channel_id')->unique();
            $table->integer('platform'); #0 = twitch, 1 = youtube
            $table->datetime('creation');
            $table->bigInteger('followers');
            $table->bigInteger('total_views');
            $table->integer('num_searched');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
}
