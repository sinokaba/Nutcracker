<?php

namespace App\Http\Controllers;

use Session;
use App\Livestream;
use App\Channel;
use App\twitchStream;
use App\youtubeStream;
use App\Stream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Request;
use DateTime;

//returns specified route
class PagesController extends Controller
{
    public function index(){
        if(Cache::get('top_streams_prev') === null){
            if(Cache::get('top_youtube') === null || Cache::get('top_twitch') === null){
                $twitch = new twitchStream(null);
                $youtube = new youtubeStream(null);
                $topTwitch = $twitch->getTopLivestreams(3);
                $topYoutube = $youtube->getTopLivestreams(3);
            }
            else{
                $topTwitch = Cache::get('top_twitch');
                $topYoutube = Cache::get('top_youtube');
            }
            $topStreams = array();
            for($i = 0; $i < 2; $i++){
                $vid = $topYoutube[$i];
                array_push($topStreams, $vid['id']['videoId']);
            }
            for($i = 0; $i < 2; $i++){
                $stream = new twitchStream(null, $topTwitch[$i]['user_id']);
                $streamInfo = $stream->getStreamInfo();
                array_push($topStreams, $streamInfo['channel']);
            }
            Cache::put('top_streams_prev', $topStreams, 15);
            return view('pages.index')->with('topStreams', $topStreams);
        }
        return view('pages.index')->with('topStreams', Cache::get('top_streams_prev'));
    }

    public function about(){
    	return view('pages.about');
    }

    public function trackStreams(Request $request){
        $path = Request::getPathInfo();
        //Log::error(explode('/', $path)[2]);
        $streams = Cache::get(explode('/', $path)[2]);
        if($streams !== null){
            return view('livestreams.trackStreams')->with($streams);
        }
        return abort(404);
    }

    public function getChannel($channelName){
        date_default_timezone_set('America/New_York');
        Log::error($channelName);
        $cacheRes = Cache::get($channelName . '_data');
        if($cacheRes === null){
            $channel = Channel::where('channel_name', $channelName)->first();
            if($channel !== null){
                if($channel->platform === 0){
                    $channelObj = new twitchStream(null, $channel->channel_id);
                    $channel['data'] = $channelObj->getChannelInfo();
                }
                else if($channel->platform === 1){
                    $channelObj = new youtubeStream($channel->channel_id);
                    $channel['data'] = $channelObj->getChannelInfo($channel->channel_id);
                }
                $channel['offline'] = $channelObj->offline;
                $streams = Stream::where('channel_id', $channel->channel_id)->orderBy('stream_start', 'desc')->get();

                if($channel->platform == 0){
                    $channel['url'] = 'https://www.twitch.tv/' . $channel->channel_name;
                }
                else{
                    $channel['url'] = 'https://www.youtube.com/channel/' . $channel->channel_id;
                }
                $past5Streams = array();
                $over_avg_viewers = 0;
                $over_chat = 0;
                $over_peak = 0;
                $over_hours = 0;
                $max_hours = 0;
                $num = count($streams) > 0 ? count($streams) : 1;
                for($i = 0; $i < count($streams); $i++){
                    $over_avg_viewers += $streams[$i]['avg_viewers'];
                    if($streams[$i]['peak_viewers'] > $over_peak){
                        $over_peak = $streams[$i]['peak_viewers'];
                    }
                    $over_chat += $streams[$i]['chatters'];
                    if($i < 5){
                        array_push($past5Streams, $streams[$i]);
                    }
                    if($i < count($streams) - 1){
                        $streams[$i]['views_growth'] = $streams[$i]['total_views'] - $streams[$i + 1]['total_views'];
                        $streams[$i]['followers_growth'] = $streams[$i]['followers'] - $streams[$i + 1]['followers']; 
                        $streams[$i]['avg_growth'] = $streams[$i]['avg_viewers'] - $streams[$i + 1]['avg_viewers'];
                        $streams[$i]['peak_growth'] = $streams[$i]['peak_viewers'] - $streams[$i + 1]['peak_viewers'];
                    }
                    else{
                        $streams[$i]['views_growth'] = 0;
                        $streams[$i]['followers_growth'] = 0;    
                        $streams[$i]['avg_growth'] = 0;
                        $streams[$i]['peak_growth'] = 0;                                
                    }
                    Log::error($streams[$i]['avg_growth']);
                    $start = new DateTime($streams[$i]['stream_end']);
                    $timeDiff = $start->diff(new DateTime($streams[$i]['stream_start']));
                    $streams[$i]['duration'] = $timeDiff->format("%H:%I:%S");
                    $hoursStreamed = (strtotime($streams[$i]['stream_end']) - strtotime($streams[$i]['stream_start']))/3600;
                    $over_hours += $hoursStreamed;
                    if($max_hours < $hoursStreamed){
                        $max_hours = $hoursStreamed;
                    }
                    $time = strtotime($streams[$i]['stream_start'] . ' UTC');
                    $streams[$i]['stream_start'] = date('Y-m-d H:i:s', $time);
                }
                $data = array(
                    'chan' => $channel,
                    'streams' => $streams,
                    'streams_rev' => $past5Streams,
                    "date" => date("Y-m-d"),
                    'avg_viewers' => round($over_avg_viewers/$num),
                    'peak' => $over_peak,
                    'chat' => round($over_chat/$num),
                    'hours' => round($over_hours, 2),
                    'avg_hours' => round($over_hours/$num, 2),
                    'max_hours' => round($max_hours, 2)
                );
                //Log::error(print_r($data, True));
                Cache::put($channelName . '_data', $data, 60);
                return view('pages.channel')->with($data);
            }
            return abort(404, "Channel not found");
        }
        return view('pages.channel')->with($cacheRes);
    }

    public function autocomplete(){
        $term = Input::get('term');
        $cacheRes = Cache::get($term);
        if($cacheRes === null){
            Log::error('null ' . $term);
            $results = array();
            $data = Channel::where('channel_name','LIKE', $term.'%')->take(5)->get();
            
            $result = array();
            foreach ($data as $key => $v){
                array_push($result, $v->channel_name);
            }
            $encodedRes = json_encode($result); 
            Cache::put($term, $encodedRes, 1440);
            return $encodedRes;
        }
        return $cacheRes; 
    }
}
