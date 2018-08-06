@extends('layout')

@section('content')

<main role="main" class="container">
	<div>
		<form class="needs-validation justify-content-center form-inline" id="tw-channel-form" novalidate>
			<div class="form-group mb-2">
				<input type="text" readonly class="form-control-plaintext" id="twitch-platform" value="Twitch Channel">
			</div>
			<div class="form-group mx-sm-4 mb-2">
				<input type="text" class="form-control" style="width:21em" name="twitch-channel" maxlength="30" id="twitch-channel" placeholder="riotgames" required>
			</div>
			<button class="my-btn btn btn-outline-success mb-2" type="button" id="add-tw-channel">Add</button>
			<div style="margin-left:1em">
				<div id="mini-loader-tw" class="mini-loader invis"></div>
			</div>
		</form>
		<form class="needs-validation justify-content-center form-inline" id="yt-channel-form" novalidate>
			<div class="form-group mb-2">
				<input type="text" readonly class="form-control-plaintext" id="youtube-platform" value="Youtube URL">
			</div>
			<div class="form-group mx-sm-4 mb-2">
				<input type="text" class="form-control" style="width:21em"  name="youtube-channel" maxlength="60" id="youtube-channel" placeholder="https://www.youtube.com/CHANNEL" required>
			</div>
			<button class="my-btn btn btn-outline-success mb-2" type="button" id="add-yt-channel">Add</button>
			<div style="margin-left:1em">
				<div id="mini-loader-yt" class="mini-loader invis"></div>
			</div>
		</form>
	</div>

	<div class="row justify-content-center hide" id="main-loader">
		<div class="main-loader"></div>
	</div>
	
	<div class="row">
		<div class="col-md-8">
		    <h2 id="status"></h2>
		    <div id="curve_chart" style="width: 100%; height: 70vh"></div>
		</div>
		<div class="col-md-4 hide" id="side-content">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <h3>
            	Additional Stats
            	<span class="octicon octicon-graph"></span>
            </h3>
          </h4>
          <ul class="list-group mb-3" id="streamer-stats">
            <li class="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h4 class="my-0">Total Viewers</h4>
              </div>
              <strong id="total-viewers"></strong>
            </li>
             <li class="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h5 class="my-0" style="cursor:pointer" data-container="body" data-placement="left" data-toggle="popover" id="popover-0">Peak Total Viewers</h5>
              </div>
              <strong id="peak-viewers">0</strong>
              <div id="popover-0-content" class="hide">

              </div>
            </li>           
            <li class="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h5 class="my-0 success">Uptime</h5>
                <!--<small class="text-muted">Brief description</small>-->
              </div>
              <strong id="uptime">0m</strong>
            </li>
          </ul>
		<button id="end-tracking" type="button" class="hide btn btn-danger">Stop</button>
		<button id="save-chart" type="button" class="my-btn hide btn btn-outline-success ">Download Chart</button>
		</div>
	</div>
</div>
@stop

@section('addScript')
	<script type="text/javascript">
		//declare the settings for the popover and how it's controlled
		var popoverSettings = {
			html: true,
			container: 'body',
			content: function(){
				var popoverId = (this.id).split('-');
				console.log(popoverId);
				return $("#popover-content-" + popoverId[1]).html();
			}
		}

		google.load('visualization', '1', {'packages':['corechart']});

		//add the csrf token to ajax calls so that laravel can stop crying
		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }}
		);
		//sortable is a function implemented on jquery which gives the user the ability to move items on a list dynamically
		$("#streamer-stats").sortable();
		$("#streamer-stats").disableSelection();
		// Set a callback to run when the Google Visualization API is loaded.
		//google.setOnLoadCallback(initTable);

	    var forms = document.getElementsByClassName('needs-validation');
	    var channels = [];
	    //delcare the regex expressions for validating user input on the forms
	    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
	    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;
	    //the start and now variables help keep track of how long the tracking has been going on
	    var start, now;
	    //this setup variable is the boolean variable which helps decide when to show or hide html elements meant to give more information to user while tracking
	    var setup = false;
	    //max channels that the user can input, if it is reached the input fields are disabled
	    var maxChannels = 7;
	    //how many seconds must be waited until another ajax call to request updated information can be executed
	    var cd = 60;

	  	$("#add-tw-channel").on('click', function(event){
	  		if(channels.length > 0){
	  			$("#mini-loader-tw").toggleClass("invis");
	  		}
		  	checkInput("#twitch-channel", 0, event);
	  	});
	    
	    $("#add-yt-channel").on('click', function(event){
	  		if(channels.length > 0){
	  			$("#mini-loader-yt").toggleClass("invis");
	  		}	    	
		  	checkInput("#youtube-channel", 1, event);
	  	});

	    // Loop over them and prevent submission
	    function checkInput(formId, platformId, event){
	    	disableAddButtons();
	    	console.log($(formId).serializeArray());
	 	    if($(formId)[0].checkValidity() === false){
		    	event.preventDefault();
		    	event.stopPropagation();
		    }
		    else if(channels.includes($(formId).serializeArray()[0]["value"])){
		    	event.preventDefault();
		    	event.stopPropagation();
		    	alert("Channel already added");	    	
		    }
		    else{
		    	if(platformId == 0 && ($(formId).serializeArray()[0]["value"]).match(twitchRe) === null){
			    	event.preventDefault();
			    	event.stopPropagation();
			    	alert("Invalid twitch Channel");
			    }
		    	else if(platformId == 1 && ($(formId).serializeArray()[0]["value"]).match(youtubeRe) === null){
			    	event.preventDefault();
			    	event.stopPropagation();
			    	alert("Invalid Youtube URL");	    		
		    	}
			    else{
		    		if(channels.length >= maxChannels){
		    			$(formId).prop('disabled', true);
		    			alert("Max number of channels to track reached.");
		    		}
		    		else{
			    		channels.push($(formId).serializeArray()[0]["value"]);
			    		//console.log(channels);
				    	$(formId)[0].classList.add('was-validated');
				    	if(channels.length == 1){
	  						$("#main-loader").toggleClass("hide");
							start = Math.floor(Date.now() / 60000);
				    		initTable();
	   						setInterval(updateTime, 1000 * cd);
				    	}	
				    	else{
					    	getData();
				    	}
				    	$(formId).val("");
		    		}
			    }
		    }   	
	    }

	    //updates the uptime of the tracking system and visualizes it for the user
	    function updateTime(){
	    	$("#uptime").html((Math.floor(Date.now() / 60000) - start) + "m");
	    }

	    $("#end-tracking").on('click', function(){
	    	if($("#end-tracking").text() == "Stop"){
		    	if(intervalSet){
		    		clearInterval(update);
		    		intervalSet = false;
		    	}
		    	$("#end-tracking").html("Start");
		    	$("#end-tracking").removeClass("btn-danger").addClass("btn-success");
	    	}
	    	else{
	    		if(!intervalSet){
					update = setInterval(getData, 1000 * cd); 
	    			intervalSet = true;
	    		}
		    	$("#end-tracking").html("Stop");
		    	$("#end-tracking").removeClass("btn-success").addClass("btn-danger");
	    	}
	    });

		var chartData, chart, update;
		var intervalSet = false;
		var peakViewers = 0;
		var peakViewersChannels;
		var options = {
			title: "Viewership",
			vAxis: {title: "Live Viewers"},
			hAxis: {title: "Time", textPosition: "none"},
			legend: "bottom"
		};

		function initTable(){
			chartData = new google.visualization.DataTable();
			chartData.addColumn('string', 'Time');
			getData(); 
		}

		function drawChart() {
			//php function needs to output the specified number of columns corressponding with number of users to look up
			chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
			chart.draw(chartData, options);
		}  	

		$("#save-chart").on('click', function () {
			var imageUrl = chart.getImageURI();
			var newWindow = window.open();
			newWindow.document.write("<img src = '" + imageUrl + "' />");
		});

		//contains the channels added as key, and their category as value
		var addedChannelsObj = {};
		//contains channel info and stats as well as current stream viewership
		var streamInfo = {};
		streamInfo['chan'] = {};
		streamInfo['views'] = {};

		function formatDate(date){
			var hours = date.getHours();
			var minutes = date.getMinutes();
			var ampm = hours >= 12 ? 'pm' : 'am';
			hours = hours % 12;
			hours = hours ? hours : 12; // the hour '0' should be '12'
			minutes = minutes < 10 ? '0'+minutes : minutes;
			var strTime = hours + ':' + minutes + ' ' + ampm;
			return date.getMonth()+1 + "/" + date.getDate() + "/" + date.getFullYear() + " " + strTime;
		}

		function getData(){
		  //console.log("called");
			console.log(chartData.getNumberOfColumns());		
			var jsonData = $.ajax({
				url: "/getViewershipStats",
				data: {channels: channels, info: streamInfo, added: addedChannelsObj},
				method: "POST",
				dataType:"JSON",
				success: function(res) {
					console.log(typeof(res));
					console.log(res);
					var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
					d.setUTCSeconds(res[0][0]);
					//console.log(res[1]);
					var dataToAdd = [formatDate(d)];
					var viewershipSum = 0;
					var peakViewershipHTML = "<ul class='list-group' id='popover-content-0'>";
					for(var i = 1; i < res[0].length; i++){
						var channelViewers = res[0][i];
						var c = channels[i-1];
						if(res[1][c] != null){
							updateStreamInfo(res, res[1][c], c);
							dataToAdd.push(res[0][i]);
						}
						else{
							if(channelViewers >= 0){
								dataToAdd.push(res[0][i]);
							}
							else{
								if(c in streamInfo['chan']){
									$("#status-" + streamInfo['chan'][c]['id']).removeClass('online').addClass('offline');									
								}
								console.log("channels before: " + channels);
								if(channels.length == 1){
									endTracking();
								}
								else{
									channels.splice(i-1, 1);
									if((channels.length + 1) < chartData.getNumberOfColumns()){
										chartData.removeColumn(i);
									}
								}
								console.log("channels after: " + channels);
							}
						}
						if(channelViewers >= 0){
							viewershipSum += channelViewers;
							//create the list elements for channels which contributed to peak viewership
							peakViewershipHTML += "<li>"+streamInfo['chan'][c]['channel']+" - " + channelViewers+"</li>";
					  		if(streamInfo['views'] === null){
					  			streamInfo['views'] = {};
								streamInfo['views'][c] = [channelViewers, channelViewers, 1];
							}
							else if(!(c in streamInfo['views'])){
								streamInfo['views'][c] = [channelViewers, channelViewers, 1];			
							}
							else{
								streamInfo['views'][c][0] += channelViewers;
								streamInfo['views'][c][2] += 1;
								if(channelViewers > streamInfo['views'][c][1]){
									streamInfo['views'][c][1] = channelViewers;
								}
							}
							var now = new Date(0);
							var uptime = Math.floor((((new Date()).getTime()/1000)  - streamInfo['chan'][c]['createdAt'])/60);
							$("#uptime-" + streamInfo['chan'][c]['id']).html(uptime + " minutes");
							$("#stream-viewers-"+streamInfo['chan'][c]['id']).html(Math.floor(streamInfo['views'][c][0]/streamInfo['views'][c][2]) 
							+ " Avg <span class='viewers octicon octicon-organization'></span>");
						}
					}

					peakViewershipHTML += "</ul>";

					if(channels.length === 0){
					  endTracking();
					}
					else{
						if(!setup){
							startTracking();
						}
						console.log(dataToAdd);
						$("#status").html("Live");                        
						chartData.addRow(dataToAdd);
						$("#total-viewers").html(viewershipSum + " <span class='viewers octicon octicon-person'></span>");
						if(viewershipSum > peakViewers){
							$("#peak-viewers").html(viewershipSum);
							peakViewersChannels = channels;
							peakViewers = viewershipSum;
							$("#popover-0").popover(popoverSettings);
							$("#popover-0-content").html(peakViewershipHTML);
						}
						if(!intervalSet){
							intervalSet = true;
							update = setInterval(getData, 1000 * cd); 
						}
						toggleLoading();
						drawChart();
				  		enableAddButtons();
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					if(XMLHttpRequest.responseText.trim()  == "Offline"){
					  console.log(chartData.getNumberOfRows());
					  if(chartData.getNumberOfRows() <= 0){
					    $("#status").html(XMLHttpRequest.responseText);                        
					  }
					  //clearInterval(update);                      
					}
					console.log(typeof(XMLHttpRequest.responseText) + " " + XMLHttpRequest.responseText + "\n");
					console.log("Error: " + errorThrown); 
				}     
			});
	  	}

	  	function startTracking(){
			$("#end-tracking").removeClass("hide");
			$("#side-content").removeClass("hide");
			$("#save-chart").toggleClass("hide");
			$("#status").html("Online");                        
			setup = true;
			update = setInterval(getData, 1000 * cd); 
			intervalSet = true;   
	  	}

	  	function endTracking(){
			channels = [];
			//$("#end-tracking").addClass("hide");
			//$("#side-content").addClass("hide");
	    	$("#save-chart").toggleClass("hide");
			$("#status").html("Offline");                        
	    	setup = false;
			clearInterval(update);
			//$("#save-chart").addClass("hide");
			intervalSet = false;
			if(chartData.getNumberOfRows() <= 1){
				clearSideContent();
			}
			toggleLoading();
	  	}

	  	function enableAddButtons(){
			$("#add-yt-channel").prop("disabled", false);
			$("#add-tw-channel").prop("disabled", false);
	  	}

	  	function disableAddButtons(){
			$("#add-yt-channel").prop("disabled", true);
			$("#add-tw-channel").prop("disabled", true);
	  	}

	  	function clearSideContent(){
			$("#end-tracking").addClass("hide");
			$("#side-content").addClass("hide");
			var myNode = document.getElementById("streamer-stats");
			var children = myNode.children;
			while(children.length > 2){
				myNode.removeChild(children[2]);
			}
	  	}

	  	function toggleLoading(){
			if(!$("#mini-loader-yt").hasClass("invis")){
				$("#mini-loader-yt").toggleClass("invis");
			}
			if(!$("#mini-loader-tw").hasClass("invis")){
				$("#mini-loader-tw").toggleClass("invis");
			}
			if(!$("#main-loader").hasClass("hide")){
				$("#main-loader").toggleClass("hide");
			}
	  	}

	  	function updateStreamInfo(response, chanInfo, channel){
			var title = chanInfo['title'].replace(/'/g, "&apos;");
			console.log(title);
			if(!(channel in addedChannelsObj)){
				var now = new Date(0);
				console.log(chanInfo['createdAt']);
				now.setUTCSeconds(chanInfo['createdAt']);
				streamInfo['chan'][channel] = chanInfo;
				var liHTML = "<li class='list-group-item d-flex justify-content-between lh-condensed'>"
				             +"<div><h5 class='my-0 stream-title' id='streamer-"+chanInfo['id']+"' data-container='body'" 
				             +"data-toggle='popover' data-placement='left' title='"+title+"'>"
				             +chanInfo['channel']+" <span id='status-"+chanInfo['id']+"' class='online octicon octicon-primitive-dot'></span></h5>"
							 +"<ul class='list-group' id='popover-content-"+chanInfo['id']+"' style='display: none'>"
						     +"<li class='list-group-item d-flex justify-content-between'>"
						     +"<div><strong class='my-0 success'>Start</strong></div>"
						     +"<span class='text-muted' id='startDate-"+chanInfo['id']+"'>"+formatDate(now)+"</span></li>"
						     +"<li class='list-group-item d-flex justify-content-between'>"
						     +"<div><strong class='my-0 success'># Chatters</strong></div>"
						     +"<span class='text-muted' id='numChatters-"+chanInfo['id']+"'>"+chanInfo['chatters']+"</span></li>"					     
						     +"<li class='list-group-item d-flex justify-content-between'>"
						     +"<div><strong class='my-0 success'>Uptime</strong></div>"
						     +"<span class='text-muted' id='uptime-"+chanInfo['id']+"'></span></li>"
						     +"<li class='list-group-item d-flex justify-content-between'>"
						     +"<div><strong class='my-0 success'>Total Followers</strong></div>"
						     +"<span class='text-muted' id='totalFollowers-"+chanInfo['id']+"'>"+chanInfo['followers']+"</span></li>"
						     +"<li class='list-group-item d-flex justify-content-between'>"
						     +"<div><strong class='my-0 success'>Total Views</strong></div>"
						     +"<span class='text-muted' id='totalViews-"+chanInfo['id']+"'>"+chanInfo['totalViews']+"</span></li>"
							 +"<li class='list-group-item d-flex justify-content-between'>"
							 +"<div><strong class='my-0 success'>Platform</strong></div>"
							 +"<span class='text-muted' id='platform-"+chanInfo['id']+"'>"+chanInfo['platform']+"</span></li></ul>"
						     +"<small id='stream-cat-"+chanInfo['id']+"' class='text-muted'>"+chanInfo['cat']+"</small></div>"
						     +"<strong id='stream-viewers-"+chanInfo['id']+"'></strong></li>";
	            addedChannelsObj[channel] = chanInfo['cat'];
	            $("#streamer-stats").append(liHTML);
	            $("#streamer-" + chanInfo['id']).popover(popoverSettings);
	            chartData.addColumn('number', response[1][channel]['channel']);
			}
			else if(channel in response[1]){
				$("#totalViews-" + chanInfo['id']).html(chanInfo['totalViews']);
				$("#stream-cat-" + chanInfo['id']).html(chanInfo['cat']);
				$("#title-" + chanInfo['id']).html(title);
			}
	  	}
	</script>

@stop