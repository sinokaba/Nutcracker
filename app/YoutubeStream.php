<?php

namespace App;

class youtubeStream extends Stream{
	private $apiKey;# = 'AIzaSyDLc4ppSH3_VauvHUjqHyJ9e0eTFsOLVDU';
	private $liveViewersUrl = 'https://www.youtube.com/live_stats?v=';
	private $apiUrl, $urlBase = 'https://www.googleapis.com/youtube/v3/';
	private $liveInfoUrl = 'search?part=snippet&channelId=';
	private $channelInfoUrl = 'channels?part=snippet,statistics&id=';
	private $videoInfoUrl = 'videos?part=snippet&id=';
	private $channelId, $videoId, $categories;
	public $channelName;

	function __construct($youtubeChannel, $video, $freq, $game){
		parent::__construct($youtubeChannel, $freq);
		$this->game = $game;
		$this->channelId = $youtubeChannel;
		$this->apiKey = config('app.youtube_api_key');
		if($youtubeChannel == null){
			$this->videoId = $video;
			$this->videoInfoUrl = "$this->urlBase$this->videoInfoUrl$this->videoId&key=$this->apiKey";
			$this->channelId = json_decode(file_get_contents($this->videoInfoUrl), true)['items'][0]['snippet']['channelId'];
		}
		else{
			$this->channelId = $youtubeChannel;
			$this->apiUrl = "$this->urlBase$this->liveInfoUrl$youtubeChannel&eventType=live&type=video&key=$this->apiKey";
			$this->videoId = json_decode(file_get_contents($this->apiUrl), true)['items'][0]['id']['videoId'];
		}
		$this->categories = json_decode(file_get_contents($this->urlBase."videoCategories?part=snippet&regionCode=US&key=$this->apiKey"), true)['items'];
	}

	function getApiKey(){
		return $this->apiKey;
	}

	function getStreamTitle(){
		//get youtube stream title, channel name, num subscribers
		#need video id of live video i think
		if(!$this->isOffline()){
			$res = json_decode(file_get_contents($this->videoInfoUrl), true);
			return json_encode($res['items'][0]['snippet']['title']);
		}
		return null;
	}

	function getChannelName(){
		if($this->isOffline()){
			return null;
		}
		$res = json_decode(file_get_contents($this->videoInfoUrl), true);
		return $res['items'][0]['snippet']['channelTitle'];
	}

	function getStreamInfo(){
		$channelStats = json_decode(file_get_contents("$this->urlBase$this->channelInfoUrl$this->channelId&key=$this->apiKey"), true);
		$livestreamInfo = json_decode(file_get_contents($this->videoInfoUrl), true);
		$category = null;
		for($i = 0; $i < count($this->categories); $i++){
			if($this->categories[$i]['id'] == $livestreamInfo['items'][0]['snippet']['categoryId']){
				$category = $this->categories[$i]['snippet']['title'];
			}
		}
		return array(
				'channel' => $channelStats['items'][0]['snippet']['title'],
				'cat' => $category,
				'title' => $livestreamInfo['items'][0]['snippet']['title'],
				'createdAt' => $livestreamInfo['items'][0]['snippet']['publishedAt'],
				'followers' => $channelStats['items'][0]['statistics']['subscriberCount'],
				'totalViews' => $channelStats['items'][0]['statistics']['viewCount']
				);
	}

	function isOffline(){
		$res = json_decode(file_get_contents($this->videoInfoUrl), true);
		return $res['items'][0]['snippet']['liveBroadcastContent'] == 'none';
	}

	//api key has rate limit of 1m calls per day, need to check if limit is reached
	function rateLimited(){
		//check if reached api calls limit
		return false;
	}

	function getCurrentViewers(){
		if($this->isOffline()){
			return 0;
		}
	    return (int)file_get_contents("$this->liveViewersUrl$this->videoId");					
	}

	function trackViewership($timeInMinutes){
		$this->getChannelInfo();
		if(!$this->isOffline()){
			//if duration to track viewership not set, then default to 24 houts
			if(is_null($timeInMinutes)){
				$timeInMinutes = 1440;
			}
			$this->start = $this->getDatetime();
			while($timeInMinutes > 0 and !$this->isOffline() and !$this->rateLimited()){
				array_push($this->viewersOverTime, array($this->getDatetime(), $this->getCurrentViewers()));
				sleep($this->freq);
				$timeInMinutes -= intdiv($this->freq, 60);
			}
			$this->end = $this->getDatetime();
		}
	}
}
?>