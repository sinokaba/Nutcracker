<?php

namespace App;	

#get chat data url = https://tmi.twitch.tv/group/user/imaqtpie/chatters
class Livestream{
	protected $start, $end, $viewersOverTime, $period;
	#protected $estTimezone = 'America/New_York';
	public $streamTitle, $game, $offline, $channelName, $peakViewership, $totalViewership, $freq, $s;

	//stream period for collecting given will be in minutes, freq is how many times per minute stats will be collected
	function __construct($freq = null){
		$this->viewersOverTime = array();
		array_push($this->viewersOverTime, array('Time', 'Concurrent Viewers'));
		$this->freq = 0;
		$this->totalViewership = 0;
		#date_default_timezone_set($this->estTimezone);
	}

	//returns how long stream was live in seconds;
	function getStreamDuration(){
		return 'Start: '.$this->start.' End: '.$this->end;
	}

	function getEvent(){
		return "Game: $this->game | Broadcast Title: $this->streamTitle";
	}

	function getChannelName(){
		return $this->channelName;
	}

	function getDataJson(){
		return json_encode($this->viewersOverTime);
	}

	function getDatetime(){
		return strtotime(date('m/d/Y h:i:s a', time()));
	}

	function getUrlContents($url){
		if (!function_exists('curl_init')){ 
			die('CURL is not installed!');
		}
		$curlOptions = array(
			CURLOPT_RETURNTRANSFER => true,   // return web page
			CURLOPT_FOLLOWLOCATION => true,   // follow redirects
			CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
			CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
			CURLOPT_TIMEOUT        => 120,    // time-out on response
			CURLOPT_URL			   => $url
		); 
		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$output = curl_exec($ch);
		if(curl_errno($ch)){
			print curl_error($ch);
		}
		curl_close($ch);
		return json_decode($output, true);
	}
}

?>