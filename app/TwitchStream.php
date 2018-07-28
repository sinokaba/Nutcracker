<?php

namespace App;

class TwitchStream extends Livestream{
	private $twitchClientId;
	private $urlBase = 'https://api.twitch.tv/kraken/streams/';
	
	function __construct($twitchChannel, $freq = null){
		parent::__construct($freq);
		$this->twitchClientId = config('app.twitch_client_id');
		$this->channelName = $twitchChannel;
	}

	function getStreamDetails($channel = null){
		$params = array(
			'client_id' => $this->twitchClientId
		);
		$chan = $channel == null ? $this->channelName : $channel;
		$res = json_decode(file_get_contents($this->urlBase . $chan . '?' . http_build_query($params)), true);
		return $res['stream'];	
	}

	function getTopLivestreams(){
		return json_decode(file_get_contents($this->urlBase.'?client_id='.$this->twitchClientId), true);
	}

	function getNumChatters(){
		return json_decode(file_get_contents('https://tmi.twitch.tv/group/user/' . $this->channelName . '/chatters'), true);
	}

	function getStreamTitle(){
		if(!$this->isOffline()){
			$res = $this->getStreamDetails();
			return $res['channel']['status'];
		}
		return null;
	}

	function getStreamGame(){
		if(!$this->isOffline()){
			$res = $this->getStreamDetails();
			return $res['game'];
		}
		return null;
	}

	function getStreamInfo(){
		if(!$this->isOffline()){
			$stats = $this->getStreamDetails();
			return array(
				'channel' => $stats['channel']['name'],
				'cat' => $stats['game'],
				'title' => $stats['channel']['status'],
				'createdAt' => strtotime($stats['created_at']), 
				'followers' => $stats['channel']['followers'], 
				'totalViews' => $stats['channel']['views'],
				'channelCreation' => $stats['channel']['created_at'],
				'channelId' => $stats['channel']['_id'],
				'platform' => 'Twitch',
				'chatters' => $this->getNumChatters()['chatter_count']
				);		
		}		
		return null;
	}

	function getCurrentViewers(){
		if($this->isOffline()){
			return -1;
		}
		$stats = $this->getStreamDetails();
		return $stats['viewers'];
	}

	function isOffline(){
		$stats = $this->getStreamDetails();
		if(is_null($stats)){
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