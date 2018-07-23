@extends('layout')

@section('customJs')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>
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
		    <div id="curve_chart" style="width: 100%; height: 65vh"></div>
			<button id="saveChart" type="button" class="hide btn btn-success">Save as PDF</button>
		</div>
		<div class="col-md-4">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <h3>Additional Stats</h3>
          </h4>
          <ul class="list-group mb-3" id="sideContent">
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
		</div>
	</div>
</div>

<script>
	var popoverSettings = {
							html: true,
							content: function() {
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
    var numDataPoints = [];
    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;
    var start, now;
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
	    		if(channels.length >= 5){
	    			$(formId).prop('disabled', true);
	    			alert("Max number of channels to track reached.");
	    		}
	    		else{
		    		channels.push($(formId).serializeArray()[0]["value"]);
		    		numDataPoints.push(0);
		    		//console.log(channels);
			    	$(formId)[0].classList.add('was-validated');
			    	if(channels.length == 1){
						start = Math.floor(Date.now() / 60000);
			    		initTable();
   						setInterval(updateTime, 1000 * 60);
			    	}	
			    	else{
				    	updateCols();
			    	}
			    	$(formId).val("");
	    		}
		    }
	    }   	
    }

  	$("#addTWChannel").on('click', function(event) {
	  	checkInput("#twitchChannel", 0, event);
  	});
    
    $("#addYTChannel").on('click', function(event) {
	  	checkInput("#youtubeChannel", 1, event);
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
		updateCols();
		update = setInterval(getData, 1000 * 60); 
		intervalSet = true;    
		if($("#status").innerHTML != "Offline"){
    		$("#saveChart").removeClass("hide");
		}
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

	var addedChannelsObj = {};
	var totalChanViewership = {};

	function getData(){
	  //console.log("called");
		console.log(chartData.getNumberOfColumns());		
		var jsonData = $.ajax({
			url: "/getViewershipStats",
			data: {channels: channels, numDataRec: numDataPoints, added: addedChannelsObj},
			method: "POST",
			dataType:"JSON",
			success: function(res) {
				console.log(typeof(res));
				console.log(res[0]);
				console.log(res[1]);
				var viewershipSum = 0;
				for(var i = 1; i < res[0].length; i++){
					viewershipSum += res[0][i];
					c = channels[i-1];
					chanInfo = res[1][c];
					if(!(c in totalChanViewership)){
						totalChanViewership[c] = res[0][i];
					}
					else{
						totalChanViewership[c] += res[0][i];
					}
					if(!(c in addedChannelsObj)){
						var liHTML = "<li class='list-group-item d-flex justify-content-between lh-condensed'>"
						             +"<div><h5 class='my-0 stream-title text-success' id='streamer-"+ (i-1) +"' data-container='body' data-toggle='popover' data-placement='left'>"
									 +chanInfo['channel']+"</h5><ul class='list-group mb-3' id='popover-content-"+ (i-1) +"' style='display: none'>"
								     +"<li class='list-group-item d-flex justify-content-between'><div><strong class='my-0 success'>Start</strong>"
								     +"</div><span class='text-muted' id='startDate-"+(i-1)+"'>"+chanInfo['createdAt']+"</span></li><li"
								     +" class='list-group-item d-flex justify-content-between'><div><strong class='my-0 success'>Total Followers"
								     +"</strong></div><span class='text-muted' id='totalFollowers-"+(i-1)+"'>"+chanInfo['followers']+"</span>"
								     +"</li><li class='list-group-item d-flex justify-content-between'><div><strong class='my-0 success'>Total Views</strong>"
								     +"</div><span class='text-muted' id='totalViews-"+(i-1)+"'>"+chanInfo['totalViews']+"</span></li></ul>"
								     +"<small id='stream-cat-'"+(i-1)+" class='text-muted'>"+chanInfo['cat']+"</small></div><strong id='stream-viewers-"+(i-1)+"'></strong></li>";
			            addedChannelsObj[c] = chanInfo['cat'];
			            $("#sideContent").append(liHTML);
			            $("#streamer-" + (i-1)).popover(popoverSettings);
			            chartData.addColumn('number', res[1][c]['channel']);
					}
					else if(c in res[1]){
						$("#totalViews-" + (i-1)).html(chanInfo['totalViews']);
						$("#stream-cat-" + (i-1)).html(chanInfo['cat']);
					}
					$("#stream-viewers-"+(i-1)).html(Math.floor(totalChanViewership[c]/(numDataPoints[i-1] + 1)) + " Avg Viewers");
				}
				if(viewershipSum <= 0 && intervalSet){
				  $("#status").html("Offline");                        
				  clearInterval(update);
				  //$("#saveChart").addClass("hide");
				  intervalSet = false;
				}
				else{
				  $("#status").html("Live");                        
				  chartData.addRow(res[0]);
				  chart.draw(chartData, options);
				  $("#totalViewers").html(viewershipSum);
				  
				  for(var i = 0; i < numDataPoints.length; i++){
				  	numDataPoints[i]++;
				  }
				  
				  if(!intervalSet){
				  	intervalSet = true;
					setInterval(update)
				  }
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

  	function updateCols(){
  		/*
  		if(channels[channels.length - 1].indexOf("youtube") !== -1){
	  		var youtubeChanName;
	  		if(channels[channels.length - 1].indexOf("youtube.com/user/") !== -1){
	  			youtubeChanName = channels[channels.length - 1].substring(channels[channels.length - 1].indexOf("youtube.com/user/"));
	  		}
	  		else{
		  		var jsonData = $.ajax({
					url: "/getYoutubeName",
					data: {youtube_channel: channels[channels.length - 1]},
					method: 'POST',
					success: function(res) {
						console.log(typeof(res) + " res text: " + res);
						chartData.addColumn('string', res);
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
						console.log(typeof(XMLHttpRequest.responseText) + " " + XMLHttpRequest.responseText + "\n");
						console.log("Error: " + errorThrown); 
					}     
				});
	  		}
			//chartData.addColumn('number', youtubeChanName + ' Viewers');
  		}
  		else{
			console.log(channels + " adding: " + channels[channels.length - 1]);
			chartData.addColumn('string', channels[channels.length - 1]);
  		}
  		*/
  		getData();
		drawChart();
  	}
</script>
@stop