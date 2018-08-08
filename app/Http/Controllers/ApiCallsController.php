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
    public function index(){
    	return view('livestreams.index');
	}
    //returns current number of viewers as well as stats for the array of channels given
    public function getStats(Request $request){
    	//get the data sent by the ajax call from the front end
		$channels = $request['channels'];
		$channelsList = $request['chanList'];
		$res = array(strtotime(date('m/d/Y h:i:s a', time())));
		//loop through each channel detecting if its youtube or twitch and gather data respectively
		for($i = 0; $i < count($channelsList); $i++){
			//all youtube urls will have youtube.com, else the url given is twitch
			//this is further checked by the regex expression on the front end, that checks for valid twitch and youtube urls
			if($channels[$channelsList[$i]]["status"] == 1 || $channels[$channelsList[$i]]['numChecked'] % 60 == 0){
				if(stripos($channelsList[$i], 'www.youtube.com/') == null){
					$stream = new twitchStream($channelsList[$i]);
					$platform = 'Twitch';
				}
				else{
					//check the youtube url given for key strings to determine if it specifies a channel or video
					if(stripos($channelsList[$i], '/channel/') !== false){
						$stream = new youtubeStream(substr($channelsList[$i], stripos($channelsList[$i], '/channel/') + 9));
					}
					else{
						$stream = new youtubeStream(null, substr($channelsList[$i], stripos($channelsList[$i], 'watch?v=') + 8));
					}			
					$platform = 'Youtube';
				}
				$viewers = $stream->getCurrentViewers();
				if($viewers >= 0){
					$channels[$channelsList[$i]]['viewersHist'][0] += $viewers; //total views
					$channels[$channelsList[$i]]['viewersHist'][1] = $viewers; //current viewers
					if($viewers > $channels[$channelsList[$i]]['viewersHist'][2]){
						$channels[$channelsList[$i]]['viewersHist'][2] = $viewers; //peak viewership
					}
					$channels[$channelsList[$i]]['viewersHist'][3] += 1; //num data count for viewership
					//if first time adding stream, or 60 minutes has passed then get updated info of channel
					if($channels[$channelsList[$i]]['addedToDB'] == 0){
						$channels[$channelsList[$i]]['channelInfo'] = $stream->getStreamInfo();
						if($channels[$channelsList[$i]]['channelInfo'] !== null){
							$this->storeChannel($channels[$channelsList[$i]]['channelInfo']);
						}
						$channels[$channelsList[$i]]['addedToDB'] = 1;					
					}

				}
				else{
					$channels[$channelsList[$i]]['viewersHist'][1] = 0;
					$channels[$channelsList[$i]]['status'] = 0;
					//add the average viewership data for the channel if the channel goes offline and its viewership has been tracked
					if($channels[$channelsList[$i]]['channelInfo'] !== null && $channels[$channelsList[$i]]['addedToDB'] == 1){
						if($channels[$channelsList[$i]]['viewersHist'][2] > 0){
							$avgViewership = floor($channels[$channelsList[$i]]['viewersHist'][0]/$channels[$channelsList[$i]]['viewersHist'][3]);
							$this->storeStreamViewership($channels[$channelsList[$i]]['channelInfo'], $avgViewership, $channels[$channelsList[$i]]['viewersHist'][2]);
							$channels[$channelsList[$i]]['addedToDB'] = 2;
						}
					}
				}
			}
			$channels[$channelsList[$i]]['numChecked'] += 1;
		}
		array_push($res, $channels);
		//encode the multidimensional associated array to json with the numeric_check option to ensure that numbers don't get converted
		//to strings
		return json_encode($res, JSON_NUMERIC_CHECK);
    }

    public function viewChannel(Request $request){
		return redirect()->to('channel/'.$request->input('search-term'));
    }

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
    	set_time_limit(0); #ensure that the php script doesn't timeout as it is executing

    	//get hte top 50 streams from youtube and twitch
    	$numStreams = 50;
    	$twitch = new twitchStream(null);
    	$youtube = new youtubeStream(null);
    	$topTwitch = $twitch->getTopLivestreams($numStreams);
    	$topYoutube = $youtube->getTopLivestreams($numStreams);

    	//push the youtube/twitch channel objects to an array for processing
    	$streamsToTrack = array();
    	//this will hold the channel information of each youtube/twitch channel analyzed, since we can't get it if stream offline
    	$streamChanInfo = array();
		for($i = 0; $i < $numStreams; $i++){
			$yt = new youtubeStream(null, $topYoutube[$i]['id']['videoId']);
			$tw = new twitchStream($topTwitch['streams'][$i]['channel']['name']);
			if(!$yt->offline){
				array_push($streamsToTrack, $yt);
			}
			if(!$yt->offline){
				array_push($streamsToTrack, $tw);
			}
		}
		$done = array();
    	while(count($streamsToTrack) > count($done)){
			for($i = 0; $i < count($streamsToTrack); $i++){
				$chan = $streamsToTrack[$i];
				//a timestamp is stored in each channel to ensure that each channel is checked once every 60 iterations or so
				if($chan->s === null){
					$chan->s = time();
				}
				if(!in_array($chan->channelName, $done) && (time() - $chan->s) % 60 >= 10){
					$viewers = $chan->getCurrentViewers();
					if($viewers >= 0){
						$chan->totalViewership += $viewers;
						if($viewers > $chan->peakViewership){
							$chan->peakViewership = $viewers;
						}
						$chan->freq += 1;
						if($chan->freq <= 1){
							$streamInfo = $chan->getStreamInfo();
							if($streamInfo['channel'] !== null){
								//if followers/subs is low then that means that channel is most likely viewbotted or streaming illegal content
								//it will not be added to the database
								if($streamInfo['followers'] <= 100){
									array_push($done, $chan->channelName);
								}
								else{
									$this->storeChannel($streamInfo);
								}
							}					
							$streamChanInfo[$chan->channelName] = $streamInfo;
						}
					}
					else{
						//channels that have recently gone offline and have been checked at least once will be added to the db
						if($chan->freq > 0){
							$avgViewership = floor($chan->totalViewership/$chan->freq);
							$this->storeStreamViewership($streamChanInfo[$chan->channelName], $avgViewership, $chan->peakViewership);
						}
						array_push($done, $chan->channelName);
					}
				}
			}
    	}
    }

    //gets the top streams from youtube and twitch combined, and returns their channel data
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
		return view('livestreams.topChannels')->with('data', $topStreams);
    }
}
