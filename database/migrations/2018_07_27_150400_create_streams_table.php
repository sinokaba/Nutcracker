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
            $table->increments('stream_id');
            $table->string('title');
            $table->string('channel_id');
            $table->foreign('channel_id')->references('channel_id')->on('channels');
            $table->timestamp('stream_start')->nullable();
            $table->timestamp('stream_end')->nullable();
            $table->string('category');
            $table->string('subcategory');
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
