<?php

namespace App;	

#get chat data url = https://tmi.twitch.tv/group/user/imaqtpie/chatters
class Stream{
	protected $streamId, $start, $end, $viewersOverTime, $period, $freq;
	protected $estTimezone = 'America/New_York';
	public $streamTitle, $game, $offline;

	//stream period for collecting given will be in minutes, freq is how many times per minute stats will be collected
	function __construct($stream, $freq){
		$this->viewersOverTime = array();
		array_push($this->viewersOverTime, array('Time', 'Concurrent Viewers'));
		$this->streamId = $stream;
		if(is_null($freq)){
			$this->freq = 60;
		}
		else{
			$this->freq = $freq;
		}
		date_default_timezone_set($this->estTimezone);
	}

	//returns how long stream was live in seconds;
	function getStreamDuration(){
		return 'Start: '.$this->start.' End: '.$this->end;
	}

	function getAvgViewers(){
		$sum = 0;
		for($i = 0; $i < count($this->viewersOverTime); $i++){
			$sum += $this->viewersOverTime[$i];
		}
		return intdiv($sum, count($this->viewersOverTime));
	}

	function getEvent(){
		return "Game: $this->game | Broadcast Title: $this->streamTitle";
	}

	function getViewership(){
		if(empty($this->viewersOverTime)){
			return 'Offline';
		}
		return $this->viewersOverTime;
	}

	function getDataJson(){
		return json_encode($this->viewersOverTime);
	}

	function getStreamId(){
		return $this->streamId;
	}

	function getDatetime(){
		return date('m/d/Y h:i:s a', time());
	}
}

?>