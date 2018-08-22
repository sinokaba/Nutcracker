<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\TwitchStream;
use App\YoutubeStream;
use App\Viewership;
use App\Channel;
use App\Stream;
use Session;

class ChannelsController extends Controller
{
    public function index(){
        $topAll = Cache::get('top_streams_c');
        if($topAll === null || Cache::get('top_streams_c_prev') === null){
            $topStreams = $this->getTopStreams(3);
            Cache::put('top_streams_c_prev', $topStreams, 15);
            return view('livestreams.addStream')->with('streams', $topStreams);
        }
        return view('livestreams.addStream')->with('streams', $topAll === null ? Cache::get('top_streams_c_prev') : array_slice($topAll, 0, 6, True));
        //return view('livestreams.trackStreams');
    }

    public function addStream(Request $request){
        $streams = array(
            'streamsToTrack' => array(
                $request->input('twitch'), 
                $request->input('youtube')
            )
        );
        $_id = uniqid();
        $streams['id'] = $_id;
        Log::error(print_r($streams, True));
        Cache::put($_id, $streams, 20);
        return redirect()->to('/track/' . $_id);
    }

    //returns current number of viewers as well as stats for the array of channels given
    public function getStreamStats(Request $request){
        //get the data sent by the ajax call from the front end
        $streams = $request['streams'];
        $streamsList = $request['streamsList'];
        $trackingId = $request['id'];
        $res = array(strtotime(date('m/d/Y h:i:s a', time())));
        //loop through each channel detecting if its youtube or twitch and gather data respectively
        for($i = 0; $i < count($streamsList); $i++){
            //all youtube urls will have youtube.com, else the url given is twitch
            //this is further checked by the regex expression on the front end, that checks for valid twitch and youtube urls      
            if($streams[$streamsList[$i]]["status"] == 1 || $streams[$streamsList[$i]]['numChecked'] % 5 == 0){
                if(stripos($streamsList[$i], 'www.youtube.com/') == null){
                    $stream = new twitchStream($streamsList[$i]);
                    $platform = 'Twitch';
                }
                else{
                    //check the youtube url given for key strings to determine if it specifies a channel or video
                    if(stripos($streamsList[$i], '/channel/') !== false){
                        $stream = new youtubeStream(substr($streamsList[$i], stripos($streamsList[$i], '/channel/') + 9));
                    }
                    else{
                        $stream = new youtubeStream(null, substr($streamsList[$i], stripos($streamsList[$i], 'watch?v=') + 8));
                    }           
                    $platform = 'Youtube';
                }
                $viewers = $stream->getCurrentViewers();
                if($viewers >= 0){
                    $streams[$streamsList[$i]]['viewersStats'][0] += $viewers; //total views
                    $streams[$streamsList[$i]]['viewersStats'][1] = $viewers; //current viewers
                    $streams[$streamsList[$i]]["status"] = 1;
                    if($viewers > $streams[$streamsList[$i]]['viewersStats'][2]){
                        $streams[$streamsList[$i]]['viewersStats'][2] = $viewers; //peak viewership
                    }
                    $streams[$streamsList[$i]]['viewersStats'][3]++; //num data count for viewership
                    //if first time adding stream, or 60 minutes has passed then get updated info of channel
                    if($streams[$streamsList[$i]]['numChecked'] % 60 == 0){
                        $streams[$streamsList[$i]]['channelInfo'] = $stream->getStreamInfo();
                    }
                    if($streams[$streamsList[$i]]['addedToDB'] == 0){
                        if($streams[$streamsList[$i]]['channelInfo'] !== null){
                            $this->storeChannel($streams[$streamsList[$i]]['channelInfo']);
                        }
                        $streams[$streamsList[$i]]['addedToDB'] = 1;                  
                    }
                }
                else{
                    $streams[$streamsList[$i]]['viewersStats'][1] = 0;
                    $streams[$streamsList[$i]]['status'] = 0;
                }
            }
            $streams[$streamsList[$i]]['numChecked']++;
        }
        Cache::put($trackingId, array('streamsToTrack' => $streamsList), 3);
        array_push($res, $streams);
        //encode the multidimensional associated array to json with the numeric_check option to ensure that numbers don't get converted
        //to strings
        return json_encode($res, JSON_NUMERIC_CHECK);
    }

    public function viewChannel(Request $request){
        return redirect()->to('/channel/'.$request->input('search-term'));
    }

    public function storeChannel($streamInfo){
        if(Channel::where('channel_id', $streamInfo['channelId'])->first() === null){
            $chan = new Channel();
            $chan->channel_name = $streamInfo['channel'];
            $chan->channel_id = $streamInfo['channelId'];
            $chan->platform = $streamInfo['platform'] == 'Twitch' ? 0 : 1;
            $chan->creation = date_format(date_create($streamInfo['channelCreation']), 'Y-m-d H:i:s');
            $chan->num_searched = 1;
            $chan->save();
        }
    }

    public function storeStreamViewership($streamInfo, $avgViewers, $peakViewers, $end = null){
        if(Channel::where('channel_id', $streamInfo['channelId'])->first() !== null){
            $livestream = new Stream();
            $livestream->avg_viewers = $avgViewers;
            $livestream->peak_viewers = $peakViewers;
            $livestream->stream_start = date('Y-m-d H:i:s', $streamInfo['createdAt']); #stream creation timestamp
            $livestream->stream_end = $end === null ? date('Y-m-d H:i:s', time()) : $end; #stream end timestamp, get he current time now in utc
            $livestream->category = $streamInfo['cat'];
            $livestream->channel_id = $streamInfo['channelId']; #$foreign key referring to channels table
            $livestream->followers = $streamInfo['followers'];
            $livestream->total_views = $streamInfo['totalViews'];
            $livestream->chatters = $streamInfo['chatters'];
            $livestream->title = $streamInfo['title'];
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

    public function getOnlineStreams($num, $added){
        $twitch = new twitchStream(null);
        $youtube = new youtubeStream(null);
        $topTwitch = $twitch->getTopLivestreams($num);
        $topYoutube = $youtube->getTopLivestreams($num);

        for($i = 0; $i < $num; $i++){
            if($topYoutube !== null){
                $yt = new youtubeStream(null, $topYoutube[$i]['id']['videoId']);
                if(!array_key_exists($yt->getChannelId(), $added) && !$yt->offline){
                    $added[$yt->getChannelId()] = $yt;
                }
            }
            $tw = new twitchStream(null, $topTwitch[$i]['user_id']);
            if($topTwitch !== null){
                if(!array_key_exists($tw->getChannelId(), $added) && !$tw->offline){
                    $added[$tw->getChannelId()] = $tw;
                }
            }
        }        
        return $added;
    }

    public function collectTopStreamersData(){
        set_time_limit(0); #ensure that the php script doesn't timeout as it is executing

        //get hte top 50 streams from youtube and twitch

        $numStreams = 100;
        
        //$allChannels = Channel::all();
        //$channelsFile = 'channels.txt';
        //$handleFile = fopen($channelsFile, 'a') or die('Cannot open file:  '.$channelsFile);
        /*
        foreach($allChannels as $channel){
            //fwrite($handleFile, $channel->channel_name . ' ' . $channel->channel_id . PHP_EOL);
            array_push($addedChannels, $channel->channel_name);
            if($channel->platform == 0){ //twitch
                $tw = new twitchStream($channel->channel_name);
                if(!$tw->offline){
                    array_push($streamsToTrack, $tw);
                }   
            }
            else{
                $yt = new youtubeStream($channel->channel_id);
                if(!$yt->offline){
                    array_push($streamsToTrack, $yt);
                }
            }
        }
        //fclose($handleFile);
        */
        $savefile = "stream_data.json";
        if(file_exists($savefile)){
            $streamsToTrack = json_decode(file_get_contents($savefile));
        }
        //push the youtube/twitch channel objects to an array for processing
        $streamsToTrack = $this->getOnlineStreams($numStreams, array());
        //this will hold the channel information of each youtube/twitch channel analyzed, since we can't get it if stream offline
        $streamChanInfo = array();
        $numSleep = 0;
        while(!empty($streamsToTrack)){
            foreach($streamsToTrack as $id => $chan){
                    error_log($chan->platform . ' ' . $chan->channelName);
                    $viewers = $chan->getCurrentViewers();
                    if($viewers >= 0){
                        $chan->totalViewership += $viewers;
                        if($viewers > $chan->peakViewership){
                            $chan->peakViewership = $viewers;
                        }
                        $chan->freq += 1;
                        $chan->end = null;
                        if($chan->freq <= 1){
                            $streamInfo = $chan->getStreamInfo();
                            if($streamInfo['channel'] !== null){
                                //if followers/subs is low then that means that channel is most likely viewbotted or streaming illegal content
                                //it will not be added to the database
                                if($streamInfo['followers'] <= 100){
                                    unset($streamsToTrack[$id]);
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
                        $chan->tries += 1;
                        if($chan->end === null){
                            $chan->end = date('Y-m-d H:i:s', time());
                        }
                        if($chan->tries > 2){
                            if($chan->freq > 0){
                                $avgViewership = floor($chan->totalViewership/$chan->freq);
                                $this->storeStreamViewership($streamChanInfo[$chan->channelName], $avgViewership, $chan->peakViewership, $chan->end);
                            }
                            unset($streamsToTrack[$id]);
                        }
                    }
            }
            error_log('PAUSING. Channels left: ' . count($streamsToTrack));
            file_put_contents($savefile, json_encode($streamsToTrack));
            sleep(60);
            $numSleep++;
            if($numSleep%10 === 0){
                error_log("Cheecking for online streams");
                $streamsToTrack = $this->getOnlineStreams($numStreams, $streamsToTrack);
            }
        }
    }

    //gets the top streams from youtube and twitch combined, and returns their channel data
    public function getTopStreams($numShow = 10){
        if(Cache::get('top_streams') === null){
            $twitch = new twitchStream(null);
            $youtube = new youtubeStream(null);
            if(Cache::get('top_youtube') === null || Cache::get('top_twitch') === null){
                $topTwitch = $twitch->getTopLivestreams($numShow);
                $topYoutube = $youtube->getTopLivestreams($numShow);
                if($numShow > 10){
                    Cache::put('top_twitch', $topTwitch, 10);
                    Cache::put('top_youtube', $topYoutube, 10);
                }
            }
            else{
                $topTwitch = Cache::get('top_twitch');
                $topYoutube = Cache::get('top_youtube');
            }
            //var_dump($twitch);
            $topStreams = array();

            for($i = 0; $i < min($numShow, count($topYoutube)); $i++){
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
            for($i = 0; $i < min($numShow, count($topTwitch)); $i++){
                $stream = new TwitchStream(null, $topTwitch[$i]['user_id']);
                $channelInfo = $stream->getStreamInfo();
                $game = $channelInfo['cat'] == 'PLAYERUNKNOWN\'S BATTLEGROUNDS' ? 'PUBG' : $channelInfo['cat'];
                $topStreams[$topTwitch[$i]['viewer_count']] = array(
                    'title' => $channelInfo['title'],          
                    'channel' => $channelInfo['channel'],
                    'cat' => $game,
                    'creation' => $channelInfo['createdAt'],
                    'logo' => $channelInfo['logo'],
                    'viewers' => $topTwitch[$i]['viewer_count'],
                    'link' => $channelInfo['url'],
                    'platform' => 'Twitch',
                    'channelLink' => 'https://twitch.tv/'.$channelInfo['channel'].'/videos'
                );
            }
            krsort($topStreams);
            if(count($topStreams) > 10){
                Cache::put('top_streams', $topStreams, 20);
            }
            return $topStreams;
        }
        return Cache::get('top_streams');
    }

    public function topStreams(){
        return view('livestreams.topChannels')->with('data', $this->getTopStreams(20));    
    }

    public function refreshTopStreams(){
        Cache::forget('top_streams');
        Cache::forget('top_twitch');
        Cache::forget('top_youtube');
        return redirect('/topStreams');
    }
}
