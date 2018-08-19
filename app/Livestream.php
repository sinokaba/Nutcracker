<?php

namespace App;	
use Illuminate\Support\Facades\Log;

#get chat data url = https://tmi.twitch.tv/group/user/imaqtpie/chatters
class Livestream{
	protected $start, $viewersOverTime, $period;
	#protected $estTimezone = 'America/New_York';
	public $game, $offline, $channelName, $channelId, $peakViewership, $totalViewership, $freq, $end, $tries;

	//stream period for collecting given will be in minutes, freq is how many times per minute stats will be collected
	function __construct($freq = null){
		$this->viewersOverTime = array();
		array_push($this->viewersOverTime, array('Time', 'Concurrent Viewers'));
		$this->freq = 0;
		$this->totalViewership = 0;
		$this->tries = 0;
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

	function getChannelId(){
		return $this->channelId;
	}

	function getDataJson(){
		return json_encode($this->viewersOverTime);
	}

	function getDatetime(){
		return strtotime(date('m/d/Y h:i:s a', time()));
	}

	function getUrlContents($url, $header = null, $attempts = 1){
		if (!function_exists('curl_init')){ 
			die('CURL is not installed!');
		}
		$curlOptions = array(
			CURLOPT_RETURNTRANSFER => true,   // return web page
			CURLOPT_FOLLOWLOCATION => true,   // follow redirects
			CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
			CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
			CURLOPT_TIMEOUT        => 120,    // time-out on response
			CURLOPT_URL			   => $url,
		);

		if($header !== null){
			$curlOptions[CURLOPT_HTTPHEADER] = $header;
		}

		//Log::error($url . ' attempts: ' . $attempts);
		//Log::error(var_dump($header));
		
		$ch = curl_init($url);
		curl_setopt_array($ch, $curlOptions);
		$output = curl_exec($ch);
		if(curl_errno($ch) > 0){
			print curl_error($ch);
		}
		curl_close($ch);
		if($output == false && $attempts < 3){
			return $this->getUrlContents($url, $header, $attempts++);
		}
		if($output == false){
			return null;
		}
		return json_decode($output, true);
	}

	function printOutput($item){
		ob_start();
		if(is_array($item)){
			var_dump($item);
			echo('\n');
		}
		else{
			echo($item . ' \n');
		}
		ob_end_flush();
		sleep(5);
	}
}

?>