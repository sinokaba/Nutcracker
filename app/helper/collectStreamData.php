<?php

namespace App\helper;
require_once '../../vendor/autoload.php';
include("../Channel.php");
include("../TwitchStream.php");
include("../YoutubeStream.php");
include("../stream.php");

#ensure that the php script doesn't timeout as it is executing
set_time_limit(0);

//get hte top 50 streams from youtube and twitch
$numStreams = 50;
$allChannels = Channel::where('total_followers' > 0)->get();
//push the youtube/twitch channel objects to an array for processing
$streamsToTrack = array();
//this will hold the channel information of each youtube/twitch channel analyzed, since we can't get it if stream offline
$streamChanInfo = array();

$channelsFile = 'channels.txt';
$handleFile = fopen($channelsFile, 'a') or die('Cannot open file:  '.$channelsFile);

for($i = 0; $i < count($allChannels); $i++){
	fwrite($handleFile, $allChannels[$i]->channel_name . ' ' . $allChannels[$i]->channel_id . '\n');
	if($allChannels[$i]->platform == 0){ //twitch
	    $tw = new twitchStream($allChannels[$i]->channel_name);
		if(!$tw->offline){
			array_push($streamsToTrack, $tw);
		}	
	}
	else{
		$yt = new youtubeStream($allChannels[$i]->channel_id);
		if(!$yt->offline){
			array_push($streamsToTrack, $yt);
		}
	}
}
fclose($handleFile);

$twitch = new twitchStream(null);
$youtube = new youtubeStream(null);
$topTwitch = $twitch->getTopLivestreams($numStreams);
$topYoutube = $youtube->getTopLivestreams($numStreams);

for($i = 0; $i < $numStreams; $i++){
    $yt = new youtubeStream(null, $topYoutube[$i]['id']['videoId']);
    error_log('twitch adding ' . $topTwitch['streams'][$i]['channel']['name']);
    $tw = new twitchStream($topTwitch['streams'][$i]['channel']['name']);
    if(!$yt->offline){
        array_push($streamsToTrack, $yt);
    }
    if(!$tw->offline){
        array_push($streamsToTrack, $tw);
    }
}
$done = array();
while(count($streamsToTrack) > count($done)){
    for($i = 0; $i < count($streamsToTrack); $i++){
        $chan = $streamsToTrack[$i];
        error_log($chan->platform . ' ' . $chan->channelName);
        if(!in_array($chan->channelName, $done)){
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
                            $avgViewership = floor($chan->totalViewership/$chan->freq);
                            $this->storeStreamViewership($streamInfo, $avgViewership, $chan->peakViewership);                                    
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
    error_log('pausing');
    sleep(60);
}

function storeChannel($streamInfo){
    if(Channel::where('channel_id', $streamInfo['channelId'])->first() === null){
        $chan = new Channel();
        $chan->channel_name = $streamInfo['channel'];
        $chan->channel_id = $streamInfo['channelId'];
        $chan->platform = $streamInfo['platform'] == 'Twitch' ? 0 : 1;
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

function storeStreamViewership($streamInfo, $avgViewers, $peakViewers){
    if(Channel::where('channel_id', $streamInfo['channelId'])->first() !== null){
        $livestream = new Stream();
        $livestream->avg_viewers = $avgViewers;
        $livestream->peak_viewers = $peakViewers;
        $livestream->stream_start = date('Y-m-d H:i:s', $streamInfo['createdAt']); #stream creation timestamp
        $livestream->stream_end = date('Y-m-d H:i:s', time()); #stream end timestamp, get he current time now in utc
        $livestream->category = $streamInfo['cat'];
        $livestream->channel_id = $streamInfo['channelId']; #$foreign key referring to channels table
        $livestream->followers = $streamInfo['followers'];
        $livestream->total_views = $streamInfo['totalViews'];
        $livestream->chatters = $streamInfo['chatters'];
        $livestream->save();
        
    }
}

?>