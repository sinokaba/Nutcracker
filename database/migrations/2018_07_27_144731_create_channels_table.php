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
            $table->timestamps();
            $table->string('name');
            $table->bigInteger('total_views');
            $table->bigInteger('followers');
            $table->string('channel_id')->primary();
            $table->integer('platform_id');
            $table->integer('platform_id')->references('platform_id')->on('platforms'); #0 = twitch, 1 = youtube
            $table->datetime('creation');
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
