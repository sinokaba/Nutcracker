<?php

namespace App;
use Illuminate\Support\Facades\Log;

class TwitchStream extends Livestream{
	private $twitchClientId;
	public $_API_V5 = array(
		'streams' => 'https://api.twitch.tv/kraken/streams/',
		'users' => 'https://api.twitch.tv/kraken/users?',
		'channels' => 'https://api.twitch.tv/kraken/channels/',
		'chat' => 'https://tmi.twitch.tv/group/user/',
		'games' => 'https://api.twitch.tv/kraken/games/',
		'search' => 'https://api.twitch.tv/kraken/search/',
		'videos' => 'https://api.twitch.tv/kraken/videos'
    );
    public $_API_NEW = array(
    	'oauth' => 'https://id.twitch.tv/oauth2/authorize',
    	'bits' => 'https://api.twitch.tv/helix/bits/leaderboard',
    	'clips' => 'https://api.twitch.tv/helix/clips',
    	'games' => 'https://api.twitch.tv/helix/games',
    	'streams' => 'https://api.twitch.tv/helix/streams',
    	'users' => 'https://api.twitch.tv/helix/users',
    	'videos' => 'https://api.twitch.tv/helix/videos'
    );

    private $videoIdPattern = '/^[v]?[0-9]+/';
    private $userIdPattern = '/^[0-9]+/';
	private $urlBase = 'https://api.twitch.tv/kraken';
	private $userExists;
	private $header;
	private $streamInfo;
	private $maxStreams = 100;
	public $platform = 'Twitch';

	function __construct($twitchChannel, $userId = null, $freq = null){
		parent::__construct($freq);
		$this->setHeader();
		if($twitchChannel !== null || $userId !== null){
			if($twitchChannel !== null){
				$this->channelName = $twitchChannel;
			}
			else{
				$this->channelId = $userId;
			}
			$this->isOffline();
		}

	}

	function setHeader(){
		$this->twitchClientId = config('app.twitch_client_id');
		$this->header = array(
			0 => 'Accept: application/vnd.twitchtv.v5+json',
			1 => 'Client-ID: ' . $this->twitchClientId 
		);
	}

	function doesUserExist(){
		return $this->userExists;
	}

	function getClientId(){
		return $this->twitchClientId;
	}

	function getApiResponse($url, $params = null){
		if($params === null){
			return $this->getUrlContents($url, $this->header);
		}
		return $this->getUrlContents($url . http_build_query($params), $this->header);
	}

	function getUserByName($user = null){
		$user = $user === null ? $this->channelName : $user;
		$params = array('login' => $user);
		$result = $this->getApiResponse($this->_API_V5['users'], $params);
		if($result['users'] === null || count($result['users']) == 0){
			$this->userExists = false;
			return null;
		}
		return $result;
	}

	function getStreamDetails($chanId = null){
		$channelId = $chanId === null ? $this->channelId : $chanId; 
		if($channelId === null){
			$user = $this->getUserByName($this->channelName);
			if($user === null){
				return null;
			}
			$channelId = $user['users'][0]['_id'];
		}
		$stream = $this->getApiResponse($this->_API_V5['streams'] . $channelId);
		if($stream == null){
			Log::error($this->channelName);
			return null;
		}
		else if(!array_key_exists('stream', $stream)){
			Log::error($this->channelName);
			Log::error(var_dump($stream));
			return null;
		}
		return $stream['stream'];
	}

	function getAllPastBroadcasts(){
		$next = 0;
		$limit = 100;
		$params = array(
			'broadcasts' => true,
			'offset' => $next,
			'limit' => $limit
		);
		$url = $this->_API_V5['channels'] . $this->channelName . '/videos?';
		$pastBroadcasts = $this->getApiResponse($url, $params);
		$fullData = $pastBroadcasts['videos'];
		$next += $limit;
		$left = $pastBroadcasts['_total'] - $limit;
		while($left > 0){
			$params['offset'] = $next;
			$pastBroadcasts = $this->getApiResponse($url, $params);
			$next += $limit;
			$left -= $limit;
			$fullData = array_merge($fullData, $pastBroadcasts['videos']);
		}
		return $allData;
	}

	function getTopLivestreams($limit = 25){
		if($limit <= $this->maxStreams){
			$params = array('first' => $limit);
			return $this->getApiResponse($this->_API_NEW['streams'] . '?', $params)['data'];
		}
		$numLeft = $limit;
		$pageId = null;
		$fullData = array();
		while($numLeft > 0){
			$numLeft -= $this->maxStreams;
			$params = array('first' => $this->maxStreams);
			if($pageId !== null){
				$params['after'] = $pageId;
			}
			$d = $this->getApiResponse($this->_API_NEW['streams'] . '?', $params);
			$pageId = $d['pagination']['cursor'];
			$fullData = array_merge($fullData, $d['data']);
		}
		return $fullData;
	}

	function getNumChatters(){
		//Log::error($this->channelName);
		return $this->getUrlContents($this->_API_V5['chat'] . $this->channelName . '/chatters');
	}

	function getStreamTitle(){
		if(!$this->isOffline()){
			return $this->getStreamDetails()['channel']['status'];
		}
		return null;
	}

	function getStreamGame(){
		if(!$this->isOffline()){
			return $this->getStreamDetails()['game'];
		}
		return null;
	}

	function getChannelInfo(){
		$channel = $this->getApiResponse($this->_API_V5['channels'] . $this->channelId);
		$user = $this->getUserByName($channel['name']);
		return array(
			'followers' => $channel['followers'],
			'totalViews' => $channel['views'],
			'logo' => $channel['logo'],
			'bio' => $user['users'][0]['bio']
		);
	}

	function getStreamInfo(){
		if(!$this->isOffline()){
			$chatters = $this->getNumChatters();
			//error_log('chatters: ' . $chatters['chatter_count']);
			if(!array_key_exists('chatter_count', $chatters)){
				Log::info(print_r($chatters, true));
			}
			return array(
				'channel' => $this->streamInfo['channel']['name'],
				'id' => $this->streamInfo['channel']['_id'],
				'cat' => $this->streamInfo['game'],
				'title' => $this->streamInfo['channel']['status'],
				'logo' => $this->streamInfo['channel']['logo'],
				'url' => $this->streamInfo['channel']['url'],
				'createdAt' => strtotime($this->streamInfo['created_at']), 
				'followers' => $this->streamInfo['channel']['followers'], 
				'totalViews' => $this->streamInfo['channel']['views'],
				'channelCreation' => $this->streamInfo['channel']['created_at'],
				'channelId' => $this->streamInfo['channel']['_id'],
				'platform' => $this->platform,
				'chatters' => $chatters === null ? 0 : $chatters['chatter_count']
				);		
		}		
		return null;
	}

	function getCurrentViewers(){
		$stream = $this->getStreamDetails();
		if($stream === null){
			return -1;
		}
		else if($stream['stream_type'] === 'rerun'){
			return -2;
		}
		return $this->getStreamDetails()['viewers'];
	}

	function isOffline(){
		$this->streamInfo = $this->getStreamDetails();
		if($this->streamInfo === null){
			$this->offline = true;
		}
		else{
			$this->channelName = $this->streamInfo['channel']['name'];
			$this->channelId = $this->streamInfo['channel']['_id'];
			$this->offline = false;
		}
		return $this->offline;				
	}

	//tracks viewership of a channel for a specified amount of time in minutes
	function trackViewership($timeInMinutes){
		$this->getStreamInfo();
		if(!$this->offline){
			//if duration to track viewership not set, then default to 24 houts
			if(is_null($timeInMinutes)){
				$timeInMinutes = 1440;
			}
			$this->start = $this->getDatetime();
			while($timeInMinutes > 0 && !$this->isOffline()){
				array_push($this->viewersOverTime, array($this->getDatetime(), $this->getCurrentViewers()));
				sleep($this->freq);
				$timeInMinutes -= intdiv($this->freq, 60);
			}	
			$this->end = $this->getDatetime();
		}			
	}
}

?>