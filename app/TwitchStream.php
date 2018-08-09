<?php

namespace App;
use Illuminate\Support\Facades\Log;

class TwitchStream extends Livestream{
	private $twitchClientId;
	public $_API = array(
		'streams' => 'https://api.twitch.tv/kraken/streams/',
		'users' => 'https://api.twitch.tv/kraken/users?',
		'channels' => 'https://api.twitch.tv/kraken/channels/',
		'chat' => 'https://tmi.twitch.tv/group/user/',
		'games' => 'https://api.twitch.tv/kraken/games/',
		'search' => 'https://api.twitch.tv/kraken/search/',
		'videos' => 'https://api.twitch.tv/kraken/videos'
    );
    private $videoIdPattern = '/^[v]?[0-9]+/';
    private $userIdPattern = '/^[0-9]+/';
	private $urlBase = 'https://api.twitch.tv/kraken';
	private $userExists;
	private $header;
	private $streamInfo;
	public $platform = 'Twitch';

	function __construct($twitchChannel, $freq = null){
		parent::__construct($freq);
		$this->setHeader();
		$this->channelName = $twitchChannel;
		if($this->channelName !== null){
			$this->isOffline();
			$this->getUserByName();
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
		$result = $this->getApiResponse($this->_API['users'], $params);
		if($result['users'] === null || count($result['users']) == 0){
			$this->userExists = false;
			return null;
		}
		return $result;
	}

	function getStreamDetails($channelId = null){
		if($channelId == null){
			$user = $this->getUserByName($this->channelName);
			if($user === null){
				return null;
			}
			$channelId = $user['users'][0]['_id'];
		}
		Log::error("twitch chan = " . $channelId);

		$stream = $this->getApiResponse($this->_API['streams'] . $channelId);
		return $stream['stream'];
	}

	function getTopLivestreams($limit = 25){
		$params = array('limit' => $limit);
		return $this->getApiResponse($this->_API['streams'] . '?', $params);
	}

	function getNumChatters(){
		Log::error($this->channelName);
		return $this->getUrlContents($this->_API['chat'] . $this->channelName . '/chatters');
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

	function getStreamInfo(){
		if(!$this->isOffline()){
			$chatters = $this->getNumChatters();
			//error_log('chatters: ' . $chatters['chatter_count']);
			//error_log(var_dump($chatters));
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
		if($this->isOffline()){
			return -1;
		}
		return $this->getStreamDetails()['viewers'];
	}

	function isOffline(){
		$this->streamInfo = $this->getStreamDetails();
		if($this->streamInfo === null){
			$this->offline = true;
		}
		else{
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