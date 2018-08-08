<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Channel;

class ChannelsController extends Controller
{
	public function index(){

	}

    public function show(Request $request){
    	$channelName = Request::input('search');
    	$channel =  Channel::where('channel_name', $channelName)->get();
    	if($channel !== null){
			return view('pages.channel')->with('chan', $channel);
    	}
    	echo "hello";
    }
}
