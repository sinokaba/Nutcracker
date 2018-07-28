<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TwitchStream;
use App\YoutubeStream;
use App\Viewership;
use App\Channel;
use App\Stream;

class ApiCallsController extends Controller
{
    //returns current number of viewers as well as stats for the array of channels given
    public function getStats(Request $request){
		$channel = $request['channels'];
		$streamStats = $request['viewership'];
		$alreadyAdded = $request['added'];
		$res = array();
		$channelInfoArr = array();
		$viewersArr = array(strtotime(date('m/d/Y h:i:s a', time())));
		//loop through each channel detecting if its youtube or twitch and gather data respectively
		for($i = 0; $i < count($channel); $i++){
			if(stripos($channel[$i], 'www.youtube.com/') == null){
				$stream = new twitchStream($channel[$i]);
				$platform = 'Twitch';
			}
			else{
				if(stripos($channel[$i], '/channel/') !== false){
					//echo substr($channel[$i], stripos($channel[$i], '/channel/') + 9);
					$stream = new youtubeStream(substr($channel[$i], stripos($channel[$i], '/channel/') + 9));
				}
				else{
					//echo substr($channel[$i], stripos($channel[$i], 'watch?v=') + 8);
					$stream = new youtubeStream(null, substr($channel[$i], stripos($channel[$i], 'watch?v=') + 8));
				}			
				$platform = 'Youtube';
			}
			$viewers = $stream->getCurrentViewers();
			//if first time adding stream, or 60 minutes has passed then get updated info of channel
			if($alreadyAdded == null || !array_key_exists($channel[$i], $alreadyAdded) || $streamStats[$channel[$i]][1] % 60 == 0){
				$channelInfoArr[$channel[$i]] = $stream->getStreamInfo();
			}
			//add the average viewership data for the channel if the channel goes offline and its viewership has been tracked
			if($viewers == 0 && ($streamStats != null && array_key_exists($channel[$i], $streamStats)) && $streamStats[$channel[$i]][2]){
				$avgViewership = floor($streamStats[$channel[$i]][0]/$streamStats[$channel[$i]][1]);
				if(Viewership::where([['channel', $stream->getChannelName()], ['viewers', $avgViewership]])->first() === null){
					$this->storeViewership(
						$avgViewership, 
						$stream->getChannelName(), 
						$platform
					);
					$streamStats[$channel[$i]][3] = true;
				}
				$streamInfo = $stream->getStreamInfo();
				$this->storeChannel($streamInfo);
				$this->storeStreamViewership($streamInfo, $avgViewership, $streamStats[1]);
			}
			array_push($viewersArr, $viewers);
		}
		array_push($res, $viewersArr);
		array_push($res, $channelInfoArr);
		array_push($res, $streamStats);
		return json_encode($res, JSON_NUMERIC_CHECK);
    }

    /*
    //returns youtube channel name of the given youtube url
    public function getYoutubeName(Request $request){
    	$channel = $request['youtube_channel'];
		if(stripos($channel, '/channel/') !== false){
			$yt = new youtubeStream(substr($channel, stripos($channel, '/channel/') + 9), null, null, null);
		}
		else{
			$yt = new youtubeStream(null, substr($channel, stripos($channel, 'watch?v=') + 8), null, null);
		}
		return $yt->getChannelName();
    }
	*/

	public function storeChannel($streamInfo){
		if(Channel::where('channel_id', $streamInfo['channelId'])->first() === null){
			$chan = new Channel();
			$chan->channel_name = $streamInfo['channel'];
			$chan->channel_id = $streamInfo['channelId'];
			$chan->platform = $streamInfo['platform'];
			$chan->creation = $streamInfo['channelCreation'];
			$chan->followers = $streamInfo['followers'];
			$chan->total_views = $streamInfo['totalViews'];
			$chan->num_searched = 1;
			$chan->save();
		}
	}

	public function storeStreamViewership($streamInfo, $avgViewers, $peakViewers){
		if(Channel::where('channel_id', $streamInfo['channelId'])->first() !== null){
			$livestream = new Stream();
			$livestream->avg_viewers = $avgViewers;
			$livestream->peak_viewers = $peakViewers;
			$livestream->stream_start = $streamInfo['createdAt']; #stream creation timestamp
			$livestream->stream_end = time(); #stream end timestamp, get he current time now in utc
			$livestream->category = $streamInfo['cat'];
			
			$chan = Channel::find($streamInfo['channelId']);
			$chan->streams()->save($livestream);
			
			/*
			$livestream->channel_id = $streamInfo['channelId']; #$foreign key referring to channels table
			$livestream->save();
			*/
		}
	}
    //saves channel viewership data to the database
    public function storeViewership($numViewers, $channel, $platform){
    	$v = new Viewership();
    	$v->viewers = $numViewers;
    	$v->channel = $channel;
    	$v->platform = $platform;
    	$v->save();
    }

    //gets the top 30 streams from youtube and twitch combined, and returns their channel data
    public function getTopstreams(){
    	$twitch = new twitchStream(null);
    	$youtube = new youtubeStream(null);
    	$topTwitch = $twitch->getTopLivestreams();
    	$topYoutube = $youtube->getTopLivestreams();
    	//var_dump($twitch);
    	$topStreams = array();

		for($i = 0; $i < min(15, count($topYoutube)); $i++){
			$vid = $topYoutube[$i];
			$viewers = $youtube->getCurrentViewers($vid['id']['videoId']);
			$topStreams[$viewers] = array(
				'channelLink' => 'https://www.youtube.com/channel/'.$vid['snippet']['channelId'], 
				'vidId' => $vid['id']['videoId'],
				'title' => $vid['snippet']['title'],
				'creation' => date('m-d-Y H:i:sa', strtotime($vid['snippet']['publishedAt'])),
				'channel' => $vid['snippet']['channelTitle'],
				'cat' => 'Gaming',
				'logo' => $vid['snippet']['thumbnails']['medium']['url'],
				'viewers' => $viewers,
				'link' => 'https://gaming.youtube.com/watch?v='.$vid['id']['videoId'],
				'platform' => 'Youtube'
 			);
		}
		for($i = 0; $i < min(15, count($topTwitch['streams'])); $i++){
			$stream = $topTwitch['streams'][$i];
			$channelInfo = $stream['channel'];
			$game = $channelInfo['game'] === 'PLAYERUNKNOWN\'S BATTLEGROUNDS' ? 'PUBG' : $channelInfo['game'];
			$topStreams[$stream['viewers']] = array(
				'title' => $channelInfo['status'],			
				'channel' => $channelInfo['display_name'],
				'cat' => $game,
				'creation' => date('m-d-Y H:i:sa', strtotime($stream['created_at'])),
				'logo' => $channelInfo['logo'],
				'viewers' => $stream['viewers'],
				'link' => $channelInfo['url'],
				'platform' => 'Twitch',
				'channelLink' => 'https://twitch.tv/'.$channelInfo['name'].'/videos'
			);
		}
		krsort($topStreams);
		return view('trackViewership.topChannels')->with('data', $topStreams);
    }
}
