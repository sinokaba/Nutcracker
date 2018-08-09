<?php

namespace App;
use Illuminate\Support\Facades\Log;

class youtubeStream extends Livestream{
	private $apiKey, $rateLimitReached;
	private $channelId, $videoId, $categories;
	public $platform = 'Youtube';
	//all the urls to make youtube api calls
	public $_API = array(
		'categories' => 'https://www.googleapis.com/youtube/v3/videoCategories?',
		'videos' => 'https://www.googleapis.com/youtube/v3/videos?',
		'search' => 'https://www.googleapis.com/youtube/v3/search?',
		'channels' => 'https://www.googleapis.com/youtube/v3/channels?',
		'playlists' => 'https://www.googleapis.com/youtube/v3/playlists?',
		'playlistItems' => 'https://www.googleapis.com/youtube/v3/playlistItems?',
		'activities' => 'https://www.googleapis.com/youtube/v3/activities?',
		'live.viewers' => 'https://www.youtube.com/live_stats?v=',
		'live.chat' => 'https://www.googleapis.com/youtube/v3/liveChat/messages?'
    );

	function __construct($youtubeChannel, $video = null, $freq = null){
		//construct a Livestream object which youtube class inherits from
		parent::__construct($freq);
		$this->setApiKey(config('app.youtube_api_key'));
		$this->setChannel($youtubeChannel, $video);
	}

	function setChannel($youtubeChannel, $video){
		if($youtubeChannel !== null || $video !== null){
			$this->videoId = $video;
			//if a youtube channel url is given, then get the video id of livestream
			if($youtubeChannel !== null){
				$liveChanInfo = $this->getLiveVideoByChannel($youtubeChannel);
				$this->videoId = $liveChanInfo[0]['id']['videoId'];
			}
			Log::error($this->videoId); //log the video id of current youtube class, to help debug any unforseen errors
			$liveVideoInfo = $this->getLivestreamDetails($this->videoId);
			$this->channelId = $liveVideoInfo[0]['snippet']['channelId'];
			$this->channelName = $liveVideoInfo[0]['snippet']['channelTitle'];
			$this->isOffline();
			$this->categories = $this->getVideosCategory();
		}
	}

	function getVideosCategory(){
		$params = array(
			'part' => 'snippet',
			'regionCode' => 'US',
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->_API['categories'], $params, true);
	}

	function getTopLivestreams($max = 30){
		$params = array(
			'part' => 'snippet',
			'eventType' => 'live',
			'type' => 'video',
			'maxResults' => min($max, 50),
			'order' => 'viewcount',
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->_API['search'], $params);
	}

	function getChannelDetails($channel = null){
		$chan = $channel === null ? $this->channelId : $channel; 
		$params = array(
			'part' => 'snippet,statistics',
			'id' => $chan,
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->_API['channels'], $params);
	}

	function getChatDetails($chatId = null){
		$params = array(
			'part' => 'snippet',
			'maxResults' => '2000',
			'liveChatId' => $chatId,
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->_API['live.chat'], $params, false, false);
	}

	function getLivestreamDetails($liveVideo){
		$params = array(
			'part' => 'snippet,contentDetails,statistics,liveStreamingDetails',
			'id' => $liveVideo,
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->_API['videos'], $params);
	}

	function getLiveVideoByChannel($chan){
		$params = array(
			'part' => 'snippet',
			'channelId' => $chan,
			'eventType' => 'live',
			'type' => 'video',
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->_API['search'], $params);
	}

	function getApiResponse($url, $params, $cat = false, $getItems = true){
		Log::error($this->videoId);
		$result = $this->getUrlContents($url . http_build_query($params));
		if($result === null || !array_key_exists('items', $result) || count($result['items']) == 0){
			return null;
		} 
		if(!$getItems){
			return $result;
		}
		return $result['items'];
	}

	function setApiKey($key){
		$this->apiKey = $key;
	}

	function getStreamTitle(){
		if(!$this->isOffline()){
			$res = $this->getLivestreamDetails($this->videoId);
			return json_encode($res[0]['snippet']['title']);
		}
		return null;
	}

	function getStreamInfo(){
		$channelStats = $this->getChannelDetails()[0];
		$livestreamInfo = $this->getLivestreamDetails($this->videoId)[0];
		$chatters = null;
		if($livestreamInfo !== null && in_array('activeLiveChatId', $livestreamInfo)){
			$chatters = $this->getChatDetails($livestreamInfo['liveStreamingDetails']['activeLiveChatId']);
		}
		//echo $chatters;
		$this->game = null;
		$streamInfo = $livestreamInfo['snippet'];
		for($i = 0; $i < count($this->categories); $i++){
			if($this->categories[$i]['id'] == $streamInfo['categoryId']){
				$this->game = $this->categories[$i]['snippet']['title'];
			}
		}
		return array(
			'channel' => $channelStats['snippet']['title'],
			'id' => $this->channelId,
			'cat' => $this->game,
			'title' => $streamInfo['title'],
			'logo' => $channelStats['snippet']['thumbnails']['high'],
			'url' => 'https://www.youtube.com/channel/' . $this->channelId,
			'createdAt' => strtotime($livestreamInfo['liveStreamingDetails']['actualStartTime']),
			'followers' => $channelStats['statistics']['subscriberCount'],
			'totalViews' => $channelStats['statistics']['viewCount'],
			'channelCreation' => $channelStats['snippet']['publishedAt'],
			'channelId' => $this->channelId,
			'platform' => $this->platform,
			'chatters' => ($chatters === null ? 0 : $chatters['pageInfo']['totalResults']) + $channelStats['statistics']['commentCount']
		);
	}

	function isOffline($video = null){
		$res = $this->getLivestreamDetails($video === null ? $this->videoId : $video);
		if($res !== null){
			$this->offline = $res[0]['snippet']['liveBroadcastContent'] == 'none';
		}
		else{
			if(is_array($res)){
				Log::error($video . $this->videoId . implode($res));
			}
			else{
				Log::error($video . $this->videoId . $res);
			}
			$this->offline = true;
		}
		return $this->offline;
	}

	function getCurrentViewers($video = null){
		$this->videoId = $video === null ? $this->videoId : $video;
		if($this->isOffline()){
			return -1;
		}
	    return (int)file_get_contents($this->_API['live.viewers'] . $this->videoId);					
	}

	function trackViewership($timeInMinutes){
		if(!$this->isOffline()){
			//if duration to track viewership not set, then default to 24 houts
			if(is_null($timeInMinutes)){
				$timeInMinutes = 1440;
			}
			$this->start = $this->getDatetime();
			while($timeInMinutes > 0 and !$this->isOffline() and !$this->rateLimitReached){
				array_push($this->viewersOverTime, array($this->getDatetime(), $this->getCurrentViewers()));
				sleep($this->freq);
				$timeInMinutes -= intdiv($this->freq, 60);
			}
			$this->end = $this->getDatetime();
		}
	}
}
?>