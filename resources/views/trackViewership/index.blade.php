@extends('layout')

@section('customJs')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"></script>
@stop

@section('content')

<main role="main" class="container">
	<div>
		<form class="needs-validation justify-content-center form-inline" id="twChannelForm" novalidate>
			<div class="form-group mb-2">
				<input type="text" readonly class="form-control-plaintext" id="twitchPlatform" value="Twitch Channel">
			</div>
			<div class="form-group mx-sm-4 mb-2">
				<input type="text" class="form-control" style="width:21em" name="twitchChannel" maxlength="30" id="twitchChannel" placeholder="riotgames" required>
			</div>
			<button class="btn btn-primary mb-2" type="button" id="addTWChannel">Add</button>
		</form>
		<form class="needs-validation justify-content-center form-inline" id="ytChannelForm" novalidate>
			<div class="form-group mb-2">
				<input type="text" readonly class="form-control-plaintext" id="youtubePlatform" value="Youtube URL">
			</div>
			<div class="form-group mx-sm-4 mb-2">
				<input type="text" class="form-control" style="width:21em"  name="youtubeChannel" maxlength="60" id="youtubeChannel" placeholder="https://www.youtube.com/CHANNEL" required>
			</div>
			<button class="btn btn-primary mb-2" type="button" id="addYTChannel">Add</button>
		</form>
	</div>
	<div class="row">
		<div class="col-md-8">
		    <h2 id="status"></h2>
		    <div id="curve_chart" style="width: 100%; height: 70vh"></div>
			<button id="saveChart" type="button" class="hide btn btn-success">Save as PDF</button>
		</div>
		<div class="col-md-4 hide" id="sideContent">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <h3>
            	Additional Stats
            	<span class="octicon octicon-graph"></span>
            </h3>
          </h4>
          <ul class="list-group mb-3" id="streamerStats">
            <li class="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h5 class="my-0">Total Viewers</h5>
                <!--<small class="text-muted">Brief description</small>-->
              </div>
              <strong id="totalViewers">0</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h5 class="my-0 success">Uptime</h5>
                <!--<small class="text-muted">Brief description</small>-->
              </div>
              <strong id="uptime">0m</strong>
            </li>
          </ul>
		<button id="endTracking" type="button" class="hide btn btn-danger">Stop</button>
		</div>
	</div>
</div>

<script>
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
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }}
	);
	// Set a callback to run when the Google Visualization API is loaded.
	//google.setOnLoadCallback(initTable);

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    var channels = [];
    //var numDataPoints = [];
    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;
    var start, now;
    var setup = false;
    var maxChannels = 7;
	//need to check if channels exist

    function updateTime(){
    	$("#uptime").html((Math.floor(Date.now() / 60000) - start) + "m");
    }

    // Loop over them and prevent submission
    function checkInput(formId, platformId, event){
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
						start = Math.floor(Date.now() / 60000);
			    		initTable();
   						setInterval(updateTime, 1000 * 60);
			    	}	
			    	else{
				    	getData();
			    	}
			    	$(formId).val("");
	    		}
		    }
	    }   	
    }

  	$("#addTWChannel").on('click', function(event){
	  	checkInput("#twitchChannel", 0, event);
  	});
    
    $("#addYTChannel").on('click', function(event){
	  	checkInput("#youtubeChannel", 1, event);
  	});

    $("#endTracking").on('click', function(){
    	if($("#endTracking").text() == "Stop"){
	    	if(intervalSet){
	    		clearInterval(update);
	    		intervalSet = false;
	    	}
	    	$("#endTracking").html("Start");
	    	$("#endTracking").removeClass("btn-danger").addClass("btn-success");
    	}
    	else{
    		if(!intervalSet){
				update = setInterval(getData, 1000 * 60); 
    			intervalSet = true;
    		}
	    	$("#endTracking").html("Stop");
	    	$("#endTracking").removeClass("btn-success").addClass("btn-danger");
    	}
    });

	var chartData, chart, update;
	var intervalSet = false;
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

	$("#saveChart").on('click', function () {
		var pdf = new jsPDF("l");
		var today = new Date();
		var date = (today.getMonth() + 1) + "-" + today.getDate() + "-" + today.getFullYear();
		pdf.addImage(chart.getImageURI(), 0, 0);
		pdf.save(channels + "_viewership_" + date + ".pdf");
	});

	//contains the channels added as key, and their category as value
	var addedChannelsObj = {};
	//contains the number of times viewership for a channel was tracked, the peak viewership, and total viewership
	var streamViewership = {};
	//contains the exact datetime when a channel initally launches a stream
	var streamCreationTime = {};

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
			data: {channels: channels, viewership: streamViewership, added: addedChannelsObj},
			method: "POST",
			dataType:"JSON",
			success: function(res) {
				console.log(typeof(res));
				console.log(res);
				var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
				d.setUTCSeconds(res[0][0]);
				//console.log(res[1]);
				var dataToAdd = [formatDate(d)];
				var viewershipSum = -1;
				streamViewership = res[2];
				console.log(streamViewership);
				for(var i = 1; i < res[0].length; i++){
					var channelViewers = res[0][i];
					var c = channels[i-1];
					if(channelViewers >= 0){
						viewershipSum += channelViewers;
				  		if(streamViewership === null){
				  			streamViewership = {};
							streamViewership[c] = [channelViewers, channelViewers, 1, false];
						}
						else if(!(c in streamViewership)){
							streamViewership[c] = [channelViewers, channelViewers, 1, false];			
						}
						else{
							streamViewership[c][0] += channelViewers;
							streamViewership[c][2] += 1;
							if(channelViewers > streamViewership[c][1]){
								streamViewership[c][1] = channelViewers;
							}
						}
					}
					if(res[1][channels[i-1]] != null){
						updateStreamInfo(res, res[1][c], c, i);
						dataToAdd.push(res[0][i])
					}
					else{
						if(channelViewers >= 0){
							dataToAdd.push(res[0][i]);
						}
						else{
							console.log("channels before: " + channels);
							if(channels.length == 1){
								endTracking();
							}
							else{
								$("#streamer-" + (i-1)).html(c + ' offline').removeClass('text-success').addClass('text-danger');
								channels.splice(i - 1, 1);
								if((channels.length + 1) < chartData.getNumberOfColumns()){
									chartData.removeColumn(i);
								}
							}
							console.log("channels after: " + channels);
						}
					}
					var now = new Date(0);
					var uptime = Math.floor((((new Date()).getTime()/1000)  - streamCreationTime[c])/60);
					$("#uptime-" + (i-1)).html(uptime + " minutes");
					$("#stream-viewers-"+(i-1)).html(Math.floor(streamViewership[c][0]/streamViewership[c][2]) + " Avg Viewers");
				}
				if(viewershipSum < 0){
				  endTracking();
				}
				else{
					if(!setup){
						startTracking();
					}
					console.log(dataToAdd);
					$("#status").html("Live");                        
					chartData.addRow(dataToAdd);
					$("#totalViewers").html(viewershipSum);

					if(!intervalSet){
						intervalSet = true;
						update = setInterval(getData, 1000 * 60); 
					}
					drawChart();
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
		$("#endTracking").removeClass("hide");
		$("#sideContent").removeClass("hide");
		$("#saveChart").toggleClass("hide");
		$("#status").html("Online");                        
		setup = true;
		update = setInterval(getData, 1000 * 60); 
		intervalSet = true;   
  	}

  	function endTracking(){
		channels = [];
		//$("#endTracking").addClass("hide");
		//$("#sideContent").addClass("hide");
    	$("#saveChart").toggleClass("hide");
		$("#status").html("Offline");                        
    	setup = false;
		clearInterval(update);
		//$("#saveChart").addClass("hide");
		intervalSet = false;
		if(chartData.getNumberOfRows() <= 1){
			clearSideContent();
		}
  	}

  	function clearSideContent(){
		$("#endTracking").addClass("hide");
		$("#sideContent").addClass("hide");
		var myNode = document.getElementById("streamerStats");
		var children = myNode.children;
		while(children.length > 2){
			myNode.removeChild(children[2]);
		}
  	}

  	function updateStreamInfo(response, chanInfo, c, i){
		if(!(c in addedChannelsObj)){
			var now = new Date(0);
			console.log(chanInfo['createdAt']);
			now.setUTCSeconds(chanInfo['createdAt']);
			streamCreationTime[c] = chanInfo['createdAt'];
			var liHTML = "<li class='list-group-item d-flex justify-content-between lh-condensed'>"
			             +"<div><h5 class='my-0 stream-title text-success' id='streamer-"+(i-1)+"' data-container='body'" 
			             +"data-toggle='popover' data-placement='left' title='"+chanInfo['title']+"'>"+chanInfo['channel']+"</h5>"
						 +"<ul class='list-group' id='popover-content-"+(i-1)+"' style='display: none'>"
					     +"<li class='list-group-item d-flex justify-content-between'>"
					     +"<div><strong class='my-0 success'>Start</strong></div>"
					     +"<span class='text-muted' id='startDate-"+(i-1)+"'>"+formatDate(now)+"</span></li>"
					     +"<li class='list-group-item d-flex justify-content-between'>"
					     +"<div><strong class='my-0 success'># Chatters</strong></div>"
					     +"<span class='text-muted' id='numChatters-"+(i-1)+"'>"+chanInfo['chatters']+"</span></li>"					     
					     +"<li class='list-group-item d-flex justify-content-between'>"
					     +"<div><strong class='my-0 success'>Uptime</strong></div>"
					     +"<span class='text-muted' id='uptime-"+(i-1)+"'></span></li>"
					     +"<li class='list-group-item d-flex justify-content-between'>"
					     +"<div><strong class='my-0 success'>Total Followers</strong></div>"
					     +"<span class='text-muted' id='totalFollowers-"+(i-1)+"'>"+chanInfo['followers']+"</span></li>"
					     +"<li class='list-group-item d-flex justify-content-between'>"
					     +"<div><strong class='my-0 success'>Total Views</strong></div>"
					     +"<span class='text-muted' id='totalViews-"+(i-1)+"'>"+chanInfo['totalViews']+"</span></li>"
						 +"<li class='list-group-item d-flex justify-content-between'>"
						 +"<div><strong class='my-0 success'>Platform</strong></div>"
						 +"<span class='text-muted' id='platform-"+(i-1)+"'>"+chanInfo['platform']+"</span></li></ul>"
					     +"<small id='stream-cat-"+(i-1)+"' class='text-muted'>"+chanInfo['cat']+"</small></div>"
					     +"<strong id='stream-viewers-"+(i-1)+"'></strong></li>";
            addedChannelsObj[c] = chanInfo['cat'];
            $("#streamerStats").append(liHTML);
            $("#streamer-" + (i-1)).popover(popoverSettings);
            chartData.addColumn('number', response[1][c]['channel']);
		}
		else if(c in response[1]){
			$("#totalViews-" + (i-1)).html(chanInfo['totalViews']);
			$("#stream-cat-" + (i-1)).html(chanInfo['cat']);
			$("#title-" + (i-1)).html(chanInfo['title']);
		}
  	}

</script>
@stop