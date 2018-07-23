<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TwitchStream;
use App\YoutubeStream;
use App\Viewership;

class ApiCallsController extends Controller
{
    public function index(){

    }

    //returns current number of viewers for the array of youtube and twitch channels given
    public function getStats(Request $request){
		$channel = $request['channels'];
		$numDataPoints = $request['numDataRec'];
		$alreadyAdded = $request['added'];
		$estTimezone = 'America/New_York';
		date_default_timezone_set($estTimezone);
		$res = array();
		$channelInfoArr = array();
		$viewersArr = array(date('m/d/Y h:i:s a',  time()));
		for($i = 0; $i < count($channel); $i++){
			//echo stripos($channel[$i], 'www.youtube.com/');
			if(stripos($channel[$i], 'www.youtube.com/') == null){
				$stream = new twitchStream($channel[$i], null);
				$platform = 'Twitch';
			}
			else{
				if(stripos($channel[$i], '/channel/') !== false){
					//echo substr($channel[$i], stripos($channel[$i], '/channel/') + 9);
					$stream = new youtubeStream(substr($channel[$i], stripos($channel[$i], '/channel/') + 9), null, null, null);
				}
				else{
					//echo substr($channel[$i], stripos($channel[$i], 'watch?v=') + 8);
					$stream = new youtubeStream(null, substr($channel[$i], stripos($channel[$i], 'watch?v=') + 8), null, null);
				}			
				$platform = 'Youtube';
			}
			$viewers = $stream->getCurrentViewers();
			if($viewers == null){
				array_push($viewersArr, 0);
			}
			else{
				array_push($viewersArr, $viewers);
			}
			//save viewership data to database
			/*
			if($numDataPoints[$i] % 6 == 0){
				$this->storeViewership($viewers, $stream->getChannelName(), $platform);
			}
			*/
			if($alreadyAdded == null || !array_key_exists($channel[$i], $alreadyAdded) || $numDataPoints[$i] % 60 == 0){
				$channelInfoArr[$channel[$i]] = $stream->getStreamInfo();
			}
		}
		array_push($res, $viewersArr);
		array_push($res, $channelInfoArr);
		return json_encode($res);
    }

    //returns channel name of the given youtube url
    public function getYoutubeInfo(Request $request){
    	$channel = $request['youtube_channel'];
    	//echo $channel;
		if(stripos($channel, '/channel/') !== false){
			//echo substr($channel, stripos($channel, '/channel/') + 9);
			$yt = new youtubeStream(substr($channel, stripos($channel, '/channel/') + 9), null, null, null);
		}
		else{
			//echo substr($channel, stripos($channel, 'watch?v=') + 8);
			$yt = new youtubeStream(null, substr($channel, stripos($channel, 'watch?v=') + 8), null, null);
		}
		return $yt->getChannelName();
    }

    //saves viewership numbers of a channel at a point in time in the viewerships database
    public function storeViewership($numViewers, $channel, $platform){
    	$v = new Viewership();
    	$v->viewers = $numViewers;
    	$v->channel = $channel;
    	$v->platform = $platform;
    	$v->save();
    }
}
