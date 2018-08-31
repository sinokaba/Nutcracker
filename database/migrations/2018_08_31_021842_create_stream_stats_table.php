<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_stats', function (Blueprint $table) {
            $table->integer('avg_viewers');
            $table->integer('peak_viewers');
            $table->bigInteger('followers_growth');
            $table->bigInteger('total_views_growth');
            $table->integer('stream_id')->primary();
            $table->integer('stream_id')->references('id')->on('streams');
            $table->integer('chatters');
            $table->double('reception', 3, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stream_stats');
    }
}
