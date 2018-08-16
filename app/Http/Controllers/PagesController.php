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
use Illuminate\Support\Facades\Response;
use Request;

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
			array_push($topStreams, $vid['id']['videoId']);
		}
		for($i = 0; $i < 2; $i++){
			$stream = new twitchStream(null, $topTwitch[$i]['user_id']);
            $streamInfo = $stream->getStreamInfo();
			array_push($topStreams, $streamInfo['channel']);
		}
    	return view('pages.index')->with('topStreams', $topStreams);
    }

    public function about(){
    	return view('pages.about');
    }

    public function trackStreams(Request $request){
        $path = Request::getPathInfo();
        //Log::error(explode('/', $path)[2]);
        $streams = session()->get('streams_' . explode('/', $path)[2]);
        if($streams !== null){
            return view('livestreams.trackStreams')->with($streams);
        }
        return abort(404);
    }

    public function getChannel($channelName){
        Log::error($channelName);
        $channel = Channel::where('channel_name', $channelName)->first();
        $streams = Stream::where('channel_id', $channel->channel_id)->get();
        if($channel !== null){
            if($channel->platform == 0){
                $channel['url'] = 'https://www.twitch.tv/' . $channel->channel_name;
            }
            else{
                $channel['url'] = 'https://www.youtube.com/channel/' . $channel->channel_id;
            }
            $data = array(
                'chan' => $channel,
                'streams' => $streams
            );
            //Log::error(var_dump($data));
            return view('pages.channel')->with($data);
        }
        $searchResults = Channel::where('channel_name','LIKE','%'.$term.'%')->take(10)->get();
        //return view('pages.search')->with('results', $searchResults);
        echo 'not found';
    }

    public function autocomplete(){
        $term = Input::get('term');
        Log::error($term);
        $results = array();
        $data = Channel::where('channel_name','LIKE','%'.$term.'%')->take(5)->get();
        
        $result = array();
        foreach ($data as $key => $v){
            array_push($result, $v->channel_name);
        }
        return json_encode($result); 
    }
}
