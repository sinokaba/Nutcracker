<?php

namespace App;

class TwitchStream extends Stream{
	private $twitchClientId;# = 'ud3fxi21z72s0dq3e3u55yh4fnjuq0';
	private $secret = 's22em0dfi04zrddq1pbotl8lytxeb2';
	private $apiUrlBase = 'https://api.twitch.tv/kraken/streams/';
	private $apiUrl, $channel;
	
	function __construct($twitchChannel, $freq){
		parent::__construct($twitchChannel, $freq);
		$this->twitchClientId = config('app.twitch_client_id');
		$this->channel = $twitchChannel;
		$this->apiUrl = "$this->apiUrlBase$twitchChannel?client_id=$this->twitchClientId";
	}

	function getStreamTitle(){
		if(!$this->isOffline()){
			$res = json_decode(file_get_contents($this->apiUrl), true);
			return $res['stream']['channel']['status'];
		}
		return null;
	}

	function getChannelName(){
		return $this->channel;
	}

	function getStreamGame(){
		if(!$this->isOffline()){
			$res = json_decode(file_get_contents($this->apiUrl), true);
			return $res['stream']['game'];
		}
		return null;
	}

	function getStreamInfo(){
		//uptime, stream start date, total views of channel, number of followers, language
		//game that is being streamed
		if(!$this->isOffline()){
			$json = json_decode(file_get_contents($this->apiUrl), true);
			$stats = $json['stream'];	
			return array(
				'channel' => $this->getChannelName(),
				'cat' => $stats['game'],
				'title' => $stats['channel']['status'],
				'createdAt' => $stats['created_at'], 
				'followers' => $stats['channel']['followers'], 
				'totalViews' => $stats['channel']['views']
				);		
		}		
		return null;
	}

	function getCurrentViewers(){
		if($this->isOffline()){
			return 0;
		}
		$json = json_decode(file_get_contents($this->apiUrl), true);
		$stats = $json['stream'];
		return $stats['viewers'];
	}

	function isOffline(){
		$json = json_decode(file_get_contents($this->apiUrl), true);
		$stats = $json['stream'];
		if(is_null($stats)){
			$this->offline = true;
		}
		else{
			$this->offline = false;
		}
		return $this->offline;				
	}

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