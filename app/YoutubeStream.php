<?php

namespace App;

class youtubeStream extends Livestream{
	private $apiKey, $rateLimitReached;
	private $channelId, $videoId, $categories;
	public $APIs = array(
		'categories' => 'https://www.googleapis.com/youtube/v3/videoCategories?',
		'videos' => 'https://www.googleapis.com/youtube/v3/videos?',
		'search' => 'https://www.googleapis.com/youtube/v3/search?',
		'channels' => 'https://www.googleapis.com/youtube/v3/channels?',
		'playlists' => 'https://www.googleapis.com/youtube/v3/playlists?',
		'playlistItems' => 'https://www.googleapis.com/youtube/v3/playlistItems?',
		'activities' => 'https://www.googleapis.com/youtube/v3/activities?',
		'live.viewers' => 'https://www.youtube.com/live_stats?v=',
    );
	private $channelInfoUrl = 'channels?part=snippet,statistics&id=';
	private $streamInfoUrl = 'videos?part=snippet%2CliveStreamingDetails&id=';

	function __construct($youtubeChannel, $video = null, $freq = null){
		parent::__construct($freq);
		$this->setApiKey(config('app.youtube_api_key'));
		if($youtubeChannel == null){
			$this->videoId = $video;
		}
		else{
			$liveChanInfo = $this->getLiveVideoByChannel($youtubeChannel);
			$this->videoId = $liveChanInfo[0]['id']['videoId'];
		}
		if($this->videoId !== null){
			$liveVideoInfo = $this->getLivestreamDetails($this->videoId);
			$this->channelId = $liveVideoInfo[0]['snippet']['channelId'];
			$this->channelName = $liveVideoInfo[0]['snippet']['channelTitle'];
		}
		$this->categories = $this->getVideosCategory();
	}

	function getVideosCategory(){
		$params = array(
			'part' => 'snippet',
			'regionCode' => 'US',
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->APIs['categories'], $params, true);
	}

	function getTopLivestreams($max = 30){
		$params = array(
			'part' => 'snippet',
			'eventType' => 'live',
			'type' => 'video',
			'getVideosCategory' => '20',
			'maxResults' => $max,
			'order' => 'viewcount',
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->APIs['search'], $params);
	}

	function getChannelDetails($channel = null){
		$chan = $channel === null ? $this->channelId : $channel; 
		$params = array(
			'part' => 'snippet,statistics',
			'id' => $chan,
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->APIs['channels'], $params);
	}

	function getLivestreamDetails($liveVideo){
		$params = array(
			'part' => 'snippet,liveStreamingDetails',
			'id' => $liveVideo,
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->APIs['videos'], $params);
	}

	function getLiveVideoByChannel($chan){
		$params = array(
			'part' => 'snippet',
			'channelId' => $chan,
			'eventType' => 'live',
			'type' => 'video',
			'key' => $this->apiKey
		);
		return $this->getApiResponse($this->APIs['search'], $params);
	}

	function getApiResponse($api, $params, $cat = false){
		$result = json_decode(file_get_contents($api . http_build_query($params)), true);
		if(!$cat && $result['pageInfo']['totalResults'] === 0){
			return null;
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
		#$channelStats = json_decode(file_get_contents($this->ytUrlBase.$this->channelInfoUrl.$this->channelId.'&key='.$this->apiKey), true);
		$channelStats = $this->getChannelDetails()[0];
		$livestreamInfo = $this->getLivestreamDetails($this->videoId);
		$this->game = null;
		$streamInfo = $livestreamInfo[0]['snippet'];
		for($i = 0; $i < count($this->categories); $i++){
			if($this->categories[$i]['id'] == $streamInfo['categoryId']){
				$this->game = $this->categories[$i]['snippet']['title'];
			}
		}
		return array(
			'channel' => $channelStats['snippet']['title'],
			'cat' => $this->game,
			'title' => $streamInfo['title'],
			'createdAt' => strtotime($livestreamInfo[0]['liveStreamingDetails']['actualStartTime']),
			'followers' => $channelStats['statistics']['subscriberCount'],
			'totalViews' => $channelStats['statistics']['viewCount'],
			'channelCreation' => $channelStats['snippet']['publishedAt'],
			'channelId' => $this->channelId,
			'platform' => 'Youtube',
			'chatters' => null
		);
	}

	function isOffline($video = null){
		$res = $this->getLivestreamDetails($video === null ? $this->videoId : $video);
		$this->offline = $res[0]['snippet']['liveBroadcastContent'] == 'none';
		return $this->offline;
	}

	function getCurrentViewers($video = null){
		$this->videoId = $video === null ? $this->videoId : $video;
		if($this->isOffline()){
			return -1;
		}
	    return (int)file_get_contents($this->APIs['live.viewers'] . $this->videoId);					
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