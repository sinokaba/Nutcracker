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
		$streamStats = $request['info'];
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
			if($alreadyAdded === null || !array_key_exists($channel[$i], $alreadyAdded) || $streamStats['views'][$channel[$i]][2] % 60 === 0){
				$channelInfoArr[$channel[$i]] = $stream->getStreamInfo();
				if($channelInfoArr[$channel[$i]]['channel'] !== null){
					$this->storeChannel($channelInfoArr[$channel[$i]]);
				}
			}
			//add the average viewership data for the channel if the channel goes offline and its viewership has been tracked
			if($viewers < 0 && $streamStats['views'] !== null && array_key_exists($channel[$i], $streamStats['views'])){
				$avgViewership = floor($streamStats['views'][$channel[$i]][0]/$streamStats['views'][$channel[$i]][2]);
				/*
				if(Viewership::where([['channel', $stream->getChannelName()], ['viewers', $avgViewership]])->first() === null){
					$this->storeViewership(
						$avgViewership, 
						$stream->getChannelName(), 
						$platform
					);
					$streamStats[$channel[$i]][3] = true;
				}
				*/
				#echo 'saving channel';
				$streamInfo = $stream->getStreamInfo();
				$this->storeStreamViewership($streamInfo, $avgViewership, $streamStats['views'][$channel[$i]][1]);
			}
			array_push($viewersArr, $viewers);
		}
		array_push($res, $viewersArr);
		array_push($res, $channelInfoArr);
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
			$chan->platform = $streamInfo['platform'] === 'Twitch' ? 0 : 1;
			$chan->creation = date_format(date_create($streamInfo['channelCreation']), 'Y-m-d H:i:s');
			$chan->followers = $streamInfo['followers'];
			$chan->total_views = $streamInfo['totalViews'];
			$chan->num_searched = 1;
			$chan->save();
		}
		else if(Channel::where('channel_id', $streamInfo['channelId'])->first()->total_views !== $streamInfo['totalViews']){
			$query = Channel::where('channel_id', $streamInfo['channelId'])->first();
			$query->followers = $streamInfo['followers'];
			$query->total_views = $streamInfo['totalViews'];
			$query->increment('num_searched');
		}
	}

	public function storeStreamViewership($streamInfo, $avgViewers, $peakViewers){
		if(Channel::where('channel_id', $streamInfo['channelId'])->first() !== null){
			$livestream = new Stream();
			$livestream->avg_viewers = $avgViewers;
			$livestream->peak_viewers = $peakViewers;
			$livestream->stream_start = date('Y-m-d H:i:s', $streamInfo['createdAt']); #stream creation timestamp
			$livestream->stream_end = date('Y-m-d H:i:s', time()); #stream end timestamp, get he current time now in utc
			$livestream->category = $streamInfo['cat'];
			/*
			$chan = Channel::find($streamInfo['channelId']);
			$chan->streams()->save($livestream);
			*/
			
			$livestream->channel_id = $streamInfo['channelId']; #$foreign key referring to channels table
			$livestream->save();
			
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

    public function collectTopStreamersData(){
    	set_time_limit(0);
		ob_start();
    	$twitch = new twitchStream(null);
    	$youtube = new youtubeStream(null);
    	$topTwitch = $twitch->getTopLivestreams(50);
    	$topYoutube = $youtube->getTopLivestreams(50);

    	$streamsToTrack = array();
    	$numStreamsToTrack = 50;
    	$streamChanInfo = array();
		for($i = 0; $i < $numStreamsToTrack; $i++){
			$yt = new youtubeStream(null, $topYoutube[$i]['id']['videoId']);
			$tw = new twitchStream($topTwitch['streams'][$i]['channel']['name']);
			array_push($streamsToTrack, $yt);
			array_push($streamsToTrack, $tw);
		}
		$done = array();
    	while(count($streamsToTrack) > count($done)){
			for($i = 0; $i < count($streamsToTrack); $i++){
				$chan = $streamsToTrack[$i];
				if($chan->s === null){
					$chan->s = time();
				}
				if(!in_array($chan->channelName, $done) && (time() - $chan->s) % 60 >= 10){
					$viewers = $chan->getCurrentViewers();
					echo($chan->channelName . $viewers);
					ob_flush();
					if($viewers >= 0){
						$chan->totalViewership += $viewers;
						if($viewers > $chan->peakViewership){
							$chan->peakViewership = $viewers;
						}
						$chan->freq += 1;
						if($chan->freq <= 1){
							$streamInfo = $chan->getStreamInfo();
							if($streamInfo['channel'] !== null){
								$this->storeChannel($streamInfo);
							}					
							$streamChanInfo[$chan->channelName] = $streamInfo;
						}
					}
					else{
						if($chan->freq > 0){
							$avgViewership = floor($chan->totalViewership/$chan->freq);
							$this->storeStreamViewership($streamChanInfo[$chan->channelName], $avgViewership, $chan->peakViewership);
						}
						array_push($done, $chan->channelName);
					}
				}
			}
    	}
    	ob_end_flush();
    	
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
