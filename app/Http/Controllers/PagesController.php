<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TwitchStream;
use App\YoutubeStream;

//returns specified route
class PagesController extends Controller
{
    public function index(){
    	$twitch = new twitchStream(null);
    	$youtube = new youtubeStream(null);
    	$topTwitch = $twitch->getTopLivestreams();
    	$topYoutube = $youtube->getTopLivestreams();
    	$topStreams = array();
		for($i = 0; $i < 2; $i++){
			$vid = $topYoutube[$i];
			array_push($topStreams, $vid['snippet']['channelId']);
		}
		for($i = 0; $i < 2; $i++){
			$stream = $topTwitch['streams'][$i];
			array_push($topStreams, $topTwitch['streams'][$i]['channel']['name']);
		}
    	return view('pages.index')->with('topStreams', $topStreams);
    }

    public function about(){
    	return view('pages.about');
    }

    public function trackViewership(){
    	return view('pages.trackViewership');
    }
}
