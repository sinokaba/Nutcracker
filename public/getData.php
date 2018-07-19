<?php	
	$estTimezone = 'America/New_York';
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
	}

	class twitchStream extends Stream{
		private $twitchClientId = 'ud3fxi21z72s0dq3e3u55yh4fnjuq0';
		private $secret = 's22em0dfi04zrddq1pbotl8lytxeb2';
		private $apiUrlBase = 'https://api.twitch.tv/kraken/streams/';
		private $apiUrl;
		
		function __construct($twitchChannel, $freq){
			parent::__construct($twitchChannel, $freq);
			$this->apiUrl = "$this->apiUrlBase$twitchChannel?client_id=".$this->twitchClientId;
		}

		function getStreamInfo(){
			//uptime, stream start date, total views of channel, number of followers, language
			//game that is being streamed
			$this->isOffline();
			$json = json_decode(file_get_contents($this->apiUrl), true);
			$stats = $json['stream'];	
			$this->game = $stats['game'];
			$this->streamTitle = $stats['channel']['status'];
			return array(
				'createdAt' => $stats['created_at'], 
				'game' => $this->game,
				'title' => $this->streamTitle,
				'followers' => $stats['channel']['followers'], 
				'totalViews' => $stats['channel']['views']
				);				
		}

		function getCurrentViewers(){
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
				$this->start = date('m/d/Y h:i:s a', time());
				while($timeInMinutes > 0 && !$this->isOffline()){
					array_push($this->viewersOverTime, array(date('m/d/Y h:i:s a', time()), $this->getCurrentViewers()));
					sleep($this->freq);
					$timeInMinutes -= intdiv($this->freq, 60);
				}	
				$this->end = date('m/d/Y h:i:s a', time());	
			}			
		}
	}

	class youtubeStream extends Stream{
		private $apiKey = 'AIzaSyDLc4ppSH3_VauvHUjqHyJ9e0eTFsOLVDU';
		private $liveViewersUrl = 'https://www.youtube.com/live_stats?v=';
		private $apiUrl, $channelUrlBase = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=';
		private $channelInfoUrl = 'https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=';
		private $videoInfoUrl = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=';
		private $channelId, $videoId;
		public $channelName;

		function __construct($youtubeChannel, $video, $freq, $game){
			parent::__construct($youtubeChannel, $freq);
			$this->game = $game;
			$this->channelId = $youtubeChannel;
			$this->videoId = $video;
			if($youtubeChannel != null){
				$this->apiUrl = "$this->channelUrlBase$youtubeChannel&eventType=live&type=video&key=$this->apiKey";
			}
			else{
				$this->apiUrl = null;
			}
		}

		function getStreamTitle(){
			//get youtube stream title, channel name, num subscribers
			#need video id of live video i think
			$res = file_get_contents("$this->channelInfoUrl$this->channelId&key=$this->apiKey");
			$this->streamTitle = $res['items']['snippet']['title'];
			return $this->streamTitle;
		}

		function getChannelName(){
			if($this->channelId != null){
				$res = json_decode(file_get_contents("$this->channelInfoUrl$this->channelId&key=$this->apiKey"), true);
			}
			else{
				//echo $this->videoId;
				$res = json_decode(file_get_contents("$this->videoInfoUrl$this->videoId&key=$this->apiKey"), true);
			}
			#var_dump($res);
			if(array_key_exists('channelTitle', $res['items'][0]['snippet'])){
				return json_encode($res['items'][0]['snippet']['channelTitle']);
			}
			return json_encode($res['items'][0]['snippet']['title']);
		}

		function isOffline(){
			$json = json_decode(file_get_contents($this->apiUrl), true);
			return empty($json['items']);
		}

		//api key has rate limit of 1m calls per day, need to check if limit is reached
		function rateLimited(){
			//check if reached api calls limit
			return false;
		}

		function getCurrentViewers(){
			if($this->apiUrl != null){
				$json = json_decode(file_get_contents($this->apiUrl), true);
				$video = $json['items'][0]['id']['videoId'];
		    	return (int)file_get_contents("https://www.youtube.com/live_stats?v=$video");				
			}
			else if($this->videoId != null){
		    	return (int)file_get_contents("https://www.youtube.com/live_stats?v=$this->videoId");				
			}
		    return 0;		
		}

		function trackViewership($timeInMinutes){
			$this->getChannelInfo();
			if(!$this->isOffline()){
				//if duration to track viewership not set, then default to 24 houts
				if(is_null($timeInMinutes)){
					$timeInMinutes = 1440;
				}
				$this->start = date('m/d/Y h:i:s a', time());
				while($timeInMinutes > 0 and !$this->isOffline() and !$this->rateLimited()){
					array_push($this->viewersOverTime, array(date('m/d/Y h:i:s a', time()), $this->getCurrentViewers()));
					sleep($this->freq);
					$timeInMinutes -= intdiv($this->freq, 60);
				}
				$this->end = date('m/d/Y h:i:s a', time());	
			}
		}
	}

	function getTotalViewership($channels, $game, $duration){
		$twitchStreamsArr = array();
		$youtubeStreamsArr = array();
		
		for($i = 0; $i < count($channels[0]); $i++){
			$twitchStream =  new twitchStream($channels[0][$i], null);
			if(stripos($twitchStream->getStreamInfo()['title'], 'lcs') !== false && !$twitchStream->isOffline()){
				array_push($twitchStreamsArr, $twitchStream);
			}
		}

		for($i = 0; $i < count($channels[1]); $i++){
			$ytStream = new youtubeStream($channels[1][$i], null, null, $game);
			if(!$ytStream->isOffline()){
				array_push($youtubeStreamsArr, $ytStream);
			}
		}
		
		/*
		$totalViewership = array(
			array(
			"Time", 
			"Total Concurrent Viewers", 
			"Twitch English Viewers",
			"Twitch Foreign Viewers", 
			"Youtube Concurrent Viewers")
			);
		*/
		#print_r($twitchStreamsArr);
		$totalViewership;

		$offlineStreams = 0;
		#while($duration > 0 && $offlineStreams < (count($twitchStreamsArr) + count($youtubeStreamsArr))){
		$twitchForeign = 0;
		$twitchEnglish = 0;
		$youtubeViewershipSum = 0;
		for($i = 0; $i < count($twitchStreamsArr); $i++){
			if(!$twitchStreamsArr[$i]->isOffline()){
				#echo $twitchStreamsArr[$i]->getStreamId().' '.$twitchStreamsArr[$i]->getCurrentViewers();
				#riot games is the main twitch channel for streaming lolesports content
				if(strtolower($twitchStreamsArr[$i]->getStreamId()) === 'riotgames'){
					$twitchEnglish += $twitchStreamsArr[$i]->getCurrentViewers();
				}
				else{
					$twitchForeign += $twitchStreamsArr[$i]->getCurrentViewers();
				}
			}
			else{
				$offlineStreams++;
			}
		}
		for($i = 0; $i < count($youtubeStreamsArr); $i++){
			if(!$youtubeStreamsArr[$i]->isOffline()){
				$youtubeViewershipSum += $youtubeStreamsArr[$i]->getCurrentViewers();
			}
			else{
				$offlineStreams++;
			}
		}
		/*
		array_push($totalViewership, 
			array(
			date('m/d/Y h:i:s a', time()), 
			$twitchEnglish + $youtubeViewershipSum + $twitchForeign, 
			$twitchEnglish,
			$twitchForeign,
			$youtubeViewershipSum)
			);
		sleep();
		$duration--;
		*/
		if(($twitchEnglish + $youtubeViewershipSum) < 100){
			echo 'Offline';
		}
		else{
			$totalViewership = 	array(
									date('m/d/Y h:i:s a', time()), 
									$twitchEnglish + $youtubeViewershipSum + $twitchForeign, 
									$twitchEnglish,
									$twitchForeign,
									$youtubeViewershipSum
								);
			echo json_encode($totalViewership);
		}
		#}

		#return json_encode();
	}
	//echo getViewersTwitch("riotgames")."<br>";
	//echo getViewersTwitch("hashinshin")."<br>";
	//getViewersYT($_lolesportsChannelId);
	
	#$qtpie = new twitchStream("imaqtpie", null, "League of Legends");
	
	#print $qtpie->getCurrentViewers();
	#print "<br>";
	
	#$qtpie->trackViewership(20);
	
	#$stream = new twitchStream("hashinshin", null, "League of Legends");
	#$stream->trackViewership(20);
	
	#print_r($qtpie->getViewership());s
	#print "<br>";
	#print($qtpie->getStreamDuration());
	
	#getTotalViewership($_lcsChannels, 'LOL', 300);

	function getChannelViewership($channel){
		$viewers_arr = array(date('m/d/Y h:i:s a',  time()));
		for($i = 0; $i < count($channel); $i++){
			//echo stripos($channel[$i], 'www.youtube.com/');
			if(stripos($channel[$i], 'www.youtube.com/') == null){
				$stream = new twitchStream($channel[$i], null);
			}
			else{
				if(stripos($channel[$i], '/channel/') !== false){
					//echo substr($channel[$i], stripos($channel[$i], '/channel/') + 9);
					$stream = new youtubeStream(substr($channel[$i], stripos($channel[$i], '/channel/') + 9), null, null, null);
				}
				else{
					//echo substr($channel[$i], stripos($channel[$i], 'watch?v=') + 8);
					$stream = new youtubeStream(null, substr($channel[$i], stripos($channel[$i], 'watch?v=') + 8), null, null);
				}			
			}
			$viewers = $stream->getCurrentViewers();
			if($viewers == null){
				array_push($viewers_arr, 0);
			}
			else{
				array_push($viewers_arr, $viewers);
			}
		}
		echo json_encode($viewers_arr);
	}
	
	function getEsportsViewership($event, $apiKeys){
		$_lolesportsChannelId = 'UCvqRdlKsE5Q8mf8YXbdIJLw';
		$_lcsChannels = array(array('riotgames', 'summonersinnlive', 'ogaminglol', 'lvpes', 'Nervarien', 'pg_esports'), array($_lolesportsChannelId)); 
		$_lckChannels = array(array('lck1', 'lck_korea'), array($_lolesportsChannelId));
	    switch($event) {
	        case 'lck' : getTotalViewership($_lckChannels, 'LOL', 300);
	        break;
	        case 'lcs' : getTotalViewership($_lcsChannels, 'LOL', 300);
	        break;
	        case 'lpl' : getTotalViewership($_lplChannels, 'LOL', 300); 
	        break;
	        case 'ow'  : getTotalViewership($_owChannels, 'OW', 300);
	        break;
	        case 'esl_csgo' : getTotalViewership($_esl_cs_channels, 'CSGO', 300);
	        break;
	        case 'dh_csgo'  : getTotalViewership($_dh_cs_channels, 'CSGO', 300);
	        break;
	        case 'faceit_csgo' : getTotalViewership($_faceit_channels, 'CSGO', 300);
	        break;
	        default:
	        	getChannelViewership($event);
	    }
	}

	if(isset($_POST['action']) && !empty($_POST['action'])) {
	    $action = $_POST['action'];
	    #echo $action;
		$apiKeys = include($_SERVER['DOCUMENT_ROOT'].'/../config/api.php');
		$res = json_decode($action);
		if(json_last_error() === 0){
			getEsportsViewership($res, $apiKeys);
		}
		else{
	    	getEsportsViewership($action, $apiKeys);
		}
	}

	if(isset($_POST['getYTName']) && !empty($_POST['getYTName'])) {
	    $userInput = $_POST['getYTName'];
	    #echo $action;
		$apiKeys = include($_SERVER['DOCUMENT_ROOT'].'/../config/api.php');

		//need to figure out if video or channel id or channel name

		//if the url to the channel id is given
		if(stripos($userInput, '/channel/') !== false){
			//echo substr($userInput, stripos($userInput, '/channel/') + 9);
			$yt = new youtubeStream(substr($userInput, stripos($userInput, '/channel/') + 9), null, null, null);
		}
		else{
			//echo substr($userInput, stripos($userInput, 'watch?v='));
			$yt = new youtubeStream(null, substr($userInput, stripos($userInput, 'watch?v=') + 8), null, null);
		}
		echo $yt->getChannelName();
	}
	#perhaps scrape liquidpedia or gamepedia to see current live events, doing it manually might be a pain
?>