<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('avg_viewers');
            $table->integer('peak_viewers');
            $table->bigInteger('followers');
            $table->bigInteger('total_views');
            $table->integer('chatters');
            $table->string('channel_id');
            $table->foreign('channel_id')->references('channel_id')->on('channels');
            $table->timestamp('stream_start')->nullable();
            $table->timestamp('stream_end')->nullable();
            $table->string('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streams');
    }
}
