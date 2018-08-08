<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\TwitchStream;
use App\YoutubeStream;
use App\Viewership;
use App\Channel;
use App\Stream;

class ChannelsController extends Controller
{
	public function index(){

	}

    public function getChannel($channelName){
    	Log::error($channelName);
    	$channel = Channel::where('channel_name', $channelName)->first();
    	if($channel !== null){
			return view('pages.channel')->with('chan', $channel);
    	}
    	echo "hello";
    }
}
