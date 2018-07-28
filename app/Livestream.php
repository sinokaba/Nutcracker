<?php

namespace App;	

#get chat data url = https://tmi.twitch.tv/group/user/imaqtpie/chatters
class Livestream{
	protected $start, $end, $viewersOverTime, $period, $freq;
	#protected $estTimezone = 'America/New_York';
	public $streamTitle, $game, $offline, $channelName;

	//stream period for collecting given will be in minutes, freq is how many times per minute stats will be collected
	function __construct($freq = null){
		$this->viewersOverTime = array();
		array_push($this->viewersOverTime, array('Time', 'Concurrent Viewers'));
		if(is_null($freq)){
			$this->freq = 60;
		}
		else{
			$this->freq = $freq;
		}

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
}

?>