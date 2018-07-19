@extends('layout')

@section('content')

<main role="main" class="container">
	<div class="row justify-content-center">
		<div class="justify-content-center">
			<form class="needs-validation form-inline" id="twChannelForm" novalidate>
			    {{ csrf_field() }}
				<div class="form-group mb-2">
					<input type="text" readonly class="form-control-plaintext" id="twitchPlatform" value="Twitch Channel">
				</div>
				<div class="form-group mx-sm-4 mb-2">
					<input type="text" class="form-control" name="twitchChannel" maxlength="30" id="twitchChannel" placeholder="sodapoppin" required>
				</div>
				<button class="btn btn-primary mb-2" type="button" id="addTWChannel">Add</button>
			</form>
			<form class="needs-validation form-inline" id="ytChannelForm" novalidate>
				<div class="form-group mb-2">
					<input type="text" readonly class="form-control-plaintext" id="youtubePlatform" value="Youtube URL">
				</div>
				<div class="form-group mx-sm-4 mb-2">
					<input type="text" class="form-control" name="youtubeChannel" maxlength="60" id="youtubeChannel" placeholder="https://www.youtube.com/CHANNEL" required>
				</div>
				<button class="btn btn-primary mb-2" type="button" id="addYTChannel">Add</button>
			</form>
		</div>
	</div>
	<div class="row justify-content-center">
	    <h2 id="status"></h2>
	    <div id="curve_chart" style="width: 100%; height: 50vh"></div>
	</div>
</div>

<script>
	google.load('visualization', '1', {'packages':['corechart']});

	// Set a callback to run when the Google Visualization API is loaded.
	//google.setOnLoadCallback(initTable);

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    var channels = [];
    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;
	//need to check if channels exist

    // Loop over them and prevent submission
  	$("#addTWChannel").on('click', function(event) {
	  	console.log($("#twChannelForm").serializeArray()[1]["value"]);
	    if($("#twChannelForm")[0].checkValidity() === false){
	    	event.preventDefault();
	    	event.stopPropagation();
	    }
	    else if(channels.includes($("#twChannelForm").serializeArray()[1]["value"])){
	    	event.preventDefault();
	    	event.stopPropagation();
	    	alert("Channel already added");	    	
	    }
	    else if(($("#twChannelForm").serializeArray()[1]["value"]).match(twitchRe) === null){
	    	event.preventDefault();
	    	event.stopPropagation();
	    	alert("Invalid twitch Channel");
	    }
	    else{
    		channels.push($("#twChannelForm").serializeArray()[1]["value"]);
    		console.log(channels);
	    	$("#twChannelForm")[0].classList.add('was-validated');
	    	if(channels.length == 1){
	    		initTable();
	    	}	
	    	else{
		    	updateCols();
	    	}
	    	$("#twitchChannel").val("");
	    }
  	});
    
    $("#addYTChannel").on('click', function(event) {
	  	console.log($("#ytChannelForm").serializeArray()[0]["value"]);
	    if($("#ytChannelForm")[0].checkValidity() === false){
	    	event.preventDefault();
	    	event.stopPropagation();
	    }
	    else if(channels.includes($("#ytChannelForm").serializeArray()[0]["value"])){
	    	event.preventDefault();
	    	event.stopPropagation();	
	    	alert("Channel already added.");    	
	    }
	    else if(($("#ytChannelForm").serializeArray()[0]["value"]).match(youtubeRe) === null){
	    	event.preventDefault();
	    	event.stopPropagation();
	    	alert("Invalid youtube url");
	    }
	    else{
	    	console.log($("#ytChannelForm").serializeArray());
		    channels.push($("#ytChannelForm").serializeArray()[0]["value"]);	    	
			$("#ytChannelForm")[0].classList.add('was-validated');
	    	if(channels.length == 1){
	    		initTable();
	    	}	
	    	else{
		    	updateCols();
	    	}
	    	$("#youtubeChannel").val("");
	    }
  	});

	var chartData, chart, update;
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
		getData();
		update = setInterval(getData, 1000 * 20);        
	}

	function drawChart() {
		//php function needs to output the specified number of columns corressponding with number of users to look up
		chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
		chart.draw(chartData, options);
	}  	

	function getData(){
	  //console.log("called");
		console.log(chartData.getNumberOfColumns());		
		var jsonData = $.ajax({
			url: "/getData.php",
			data: {action: JSON.stringify(channels) },
			type: 'post',
			dataType:"json",
			success: function(res) {
				console.log(typeof(res));
				console.log(res);
				if(typeof(res) == String || res[1] <= 0){
				  $("#status").html("Offline");                        
				  //clearInterval(update);
				}
				else{
				  $("#status").html("Live");                        
				  chartData.addRow(res);
				  chart.draw(chartData, options);
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
  		if(channels[channels.length - 1].indexOf("youtube") !== -1){
	  		var youtubeChanName;
	  		if(channels[channels.length - 1].indexOf("youtube.com/user/") !== -1){
	  			youtubeChanName = channels[channels.length - 1].substring(channels[channels.length - 1].indexOf("youtube.com/user/"));
	  		}
	  		else{
		  		var jsonData = $.ajax({
					url: "/getData.php",
					data: {getYTName: channels[channels.length - 1]},
					type: 'post',
					dataType:"json",
					success: function(res) {
						console.log(typeof(res));
						console.log(res);
						chartData.addColumn('number', res + ' Viewers');
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
			chartData.addColumn('number', channels[channels.length - 1] + ' Viewers');
  		}
		drawChart();
  	}
</script>
@stop