@extends('layout')

@section('content')
<main role="main" class="container">
<div>
    <form class="needs-validation justify-content-center form-inline" id="tw-channel-form" onkeypress="return event.keyCode != 13;" novalidate>
        <div class="form-group mb-2" style="width: 130px">
			<h6 id="twitch-platform">Twitch Channel</h6>        
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
        <div class="form-group mb-2" style="width: 130px">
            <h6 id="youtube-platform">Youtube URL</h6>
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

<div class="line"></div>

<div class="row justify-content-center hide" id="main-loader">
    <div class="main-loader"></div>
</div>
<div class="row">
    <div class="col-md-8">
        <h3 id="chart-title"></h3>
        <div id="curve_chart" download="nutcracker_chart.jpg" style="width: 100%; height: 70vh"></div>
    </div>
    <div class="col-md-4 hide" id="side-content">
        <h3>
            Additional Stats
            <span class="octicon octicon-graph"></span>
        </h3>
        <ul class="list-group mb-3" id="streamer-stats">
            <li class="list-group-item d-flex justify-content-between lh-condensed">
                <div>
                    <h4 class="my-0">Total Viewers</h4>
                </div>
                <strong id="total-viewers">
                <span class="viewers octicon octicon-person"></span>
                </strong> 
            </li>
            <li class="list-group-item d-flex justify-content-between lh-condensed">
                <div>
                    <h5 class="my-0" style="cursor:pointer" data-container="body" data-placement="left" data-toggle="popover" id="popover-0">Peak Total Viewers</h5>
                </div>
                <strong id="peak-viewers">
                <span class="viewers-peak octicon octicon-person"></span>
                </strong>
                <div id="popover-0-content" class="hide">
                </div>
            </li>
            <li class="list-group-item d-flex justify-content-between lh-condensed">
                <div>
                    <h5 class="my-0 success">Uptime</h5>
                    <!--<small class="text-muted">Brief description</small>-->
                </div>
                <strong id="uptime">
                	00:00 
                <span class="octicon octicon-clock"></span>
                </strong>
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
	
	//enable tooltips
	$(function(){
		$("[data-toggle='tooltip']").tooltip()
	});

    //declare the settings for the popover and how it's controlled
    var popoverSettings = {
        html: true,
        container: "body",
        content: function() {
            var popoverId = (this.id).split("-");
            console.log(popoverId);
            return $("#popover-content-" + popoverId[1]).html();
        }
    }

    google.load("visualization", "1", {
        "packages": ["corechart"]
    });

    //add the csrf token to ajax calls so that laravel can stop crying
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
        }
    });
    //sortable is a function implemented on jquery which gives the user the ability to move items on a list dynamically
    $("#streamer-stats").sortable();
    $("#streamer-stats").disableSelection();
    // Set a callback to run when the Google Visualization API is loaded.
    //google.setOnLoadCallback(initTable);

    var forms = document.getElementsByClassName("needs-validation");
    var channels = {};
    var channelsList = [];
    //delcare the regex expressions for validating user input on the forms
    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;
    //the start and now variables help keep track of how long the tracking has been going on
    var start, now, chartData, chart, update;
    //this setup variable is the boolean variable which helps decide when to show or hide html elements meant to give more information to user while tracking
    var setup = false;
    //max channels that the user can input, if it is reached the input fields are disabled
    var maxChannels = 7;
    //how many seconds must be waited until another ajax call to request updated information can be executed
    var cd = 60;
    var intervalSet = false;
    var peakViewers = 0;
    var activeChannels = 0;
    var peakViewersChannels;
    var options = {
        title: "Viewership",
        vAxis: {
            title: "Live Viewers"
        },
        hAxis: {
            title: "Time",
            textPosition: "none"
        },
        legend: "bottom"
    };

    $("#add-tw-channel").on('click', function(event) {
        if (channelsList.length > 0) {
            $("#mini-loader-tw").toggleClass("invis");
        }
        checkInput("#twitch-channel", 0, event);
    });

    $("#add-yt-channel").on('click', function(event) {
        if (channelsList.length > 0) {
            $("#mini-loader-yt").toggleClass("invis");
        }
        checkInput("#youtube-channel", 1, event);
    });

    // Loop over them and prevent submission
    function checkInput(formId, platformId, event) {
        disableAddButtons();
        console.log($(formId).serializeArray());
        if ($(formId)[0].checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
            enableAddButtons();
            toggleLoading();
        }
        else if (channelsList.includes($(formId).serializeArray()[0]["value"])) {
            event.preventDefault();
            event.stopPropagation();
            enableAddButtons();
            toggleLoading();
            alert("Channel already added");
        }
        else {
            if (platformId == 0 && ($(formId).serializeArray()[0]["value"]).match(twitchRe) === null) {
                event.preventDefault();
                event.stopPropagation();
                enableAddButtons();
	            toggleLoading();
                alert("Invalid twitch Channel");
            }
            else if (platformId == 1 && ($(formId).serializeArray()[0]["value"]).match(youtubeRe) === null) {
                event.preventDefault();
                event.stopPropagation();
                enableAddButtons();
            	toggleLoading();
                alert("Invalid Youtube URL");
            }
            else {
                if (channelsList.length >= maxChannels) {
                    $(formId).prop("disabled", true);
                    enableAddButtons();
                    toggleLoading();
                    alert("Max number of channels to track reached.");
                }
                else {
                	var userInput = $(formId).serializeArray()[0]["value"];
                    channels[userInput] = {
                    	"status": 1,
                    	'numChecked': 0,
                    	"channelInfo": null,
                    	"viewersHist": [0, 0, 0, 0],
                    	"addedToDB": 0
                    };
                    channelsList.push(userInput);
                    activeChannels++;
                    //console.log(channels);
                    $(formId)[0].classList.add("was-validated");
                    if (channelsList.length == 1) {
                        $("#main-loader").toggleClass("hide");
                        start = Math.floor(Date.now() / 60000);
                        initTable();
                        setInterval(updateTime, 1000 * cd);
                    }
                    else {
                        getData();
                    }
                    $(formId).val("");
                }
            }
        }
    }

    //updates the uptime of the tracking system and visualizes it for the user
    function updateTime() {
        var minsElapsed = (Math.floor(Date.now() / 60000) - start);
        var m = (minsElapsed%60) >= 10 ? (minsElapsed%60) : "0" + (minsElapsed%60);
        var time = "00:" + m;
        if (minsElapsed > 60) {
            var r = Math.floor(minsElapsed / 60);
            var h = r >= 10 ? r : "0" + r;
            time = h + ":" + m;
        }
        $("#uptime")[0].childNodes[0].nodeValue = time + " ";
    }

    $("#end-tracking").on("click", function() {
        if ($("#end-tracking").text() == "Stop") {
            if (intervalSet) {
                clearInterval(update);
                intervalSet = false;
            }
            $("#end-tracking").html("Start");
            $("#end-tracking").removeClass("btn-danger").addClass("btn-success");
        }
        else {
            if (!intervalSet) {
                update = setInterval(getData, 1000 * cd);
                intervalSet = true;
            }
            $("#end-tracking").html("Stop");
            $("#end-tracking").removeClass("btn-success").addClass("btn-danger");
        }
    });

    function initTable() {
        chartData = new google.visualization.DataTable();
        chartData.addColumn("string", "Time");
        getData();
    }

    function drawChart() {
        //php function needs to output the specified number of columns corressponding with number of users to look up
        chart = new google.visualization.LineChart(document.getElementById("curve_chart"));
        chart.draw(chartData, options);
    }

    $("#save-chart").on("click", function() {
        //create a tag to act as a pseudo download link, where it points to chart's image uri
        var downloadLink = document.createElement("a");
        downloadLink.href = chart.getImageURI();
        downloadLink.download = "nutcracker_chart.png";
        /// create a "fake" click-event to trigger the download
        if (document.createEvent) {
            e = document.createEvent("MouseEvents");
            e.initMouseEvent("click", true, true, window,
                0, 0, 0, 0, 0, false, false, false,
                false, 0, null);
            downloadLink.dispatchEvent(e);

        }
        else if (downloadLink.fireEvent) {
            downloadLink.fireEvent("onclick");
        }
    });

    function formatDate(date) {
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? "pm" : "am";
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? "0" + minutes : minutes;
        var strTime = hours + ":" + minutes + " " + ampm;
        return date.getMonth() + 1 + "/" + date.getDate() + "/" + date.getFullYear() + " " + strTime;
    }

    function getData() {
        //console.log("called");
        console.log(chartData.getNumberOfColumns());
        var jsonData = $.ajax({
            url: "/getViewershipStats",
            data: {
                channels: channels,
                chanList: channelsList,
            },
            method: "POST",
            dataType: "JSON",
            success: function(res) {
                console.log(typeof(res));
                console.log(res);
                var d = new Date(0); //get date from epoch
                d.setUTCSeconds(res[0]);
                channels = res[1];
                var streamData = processStreamData([formatDate(d)]);

                if (activeChannels === 0/*channelsList.length === 0*/) {
                    endTracking();
                }
                else if (!streamData["offlineExists"]) {
                    if (!setup) {
                        startTracking();
                    }
                    console.log(streamData["dataToAdd"]);
                    chartData.addRow(streamData["dataToAdd"]);

                    $("#total-viewers")[0].childNodes[0].nodeValue = streamData["viewershipSum"] + " ";
                    if (streamData['viewershipSum'] > peakViewers) {
                        $("#peak-viewers")[0].childNodes[0].nodeValue = streamData["viewershipSum"] + " ";
                        peakViewersChannels = streamData["contributingChannels"];
                        peakViewers = streamData['viewershipSum'];
                        $("#popover-0").popover(popoverSettings);
                        $("#popover-0-content").html(streamData['peakViewersHTML']);
                    }
                    if (!intervalSet) {
                        intervalSet = true;
                        update = setInterval(getData, 1000 * cd);
                    }
                    drawChart();
                }
                toggleLoading();
                enableAddButtons();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if (XMLHttpRequest.responseText.trim() == "Offline") {
                    console.log(chartData.getNumberOfRows());
                    //clearInterval(update);                      
                }
                console.log(typeof(XMLHttpRequest.responseText) + " " + XMLHttpRequest.responseText + "\n");
                console.log("Error: " + errorThrown);
            }
        });
    }

    function processStreamData(chartRows) {
        var output = {};
        output["viewershipSum"] = 0;
        output["peakViewersHTML"] = "<ul class='list-group' id='popover-content-0'>";
        output["offlineExists"] = false;
        output["dataToAdd"] = chartRows;
        output["contributingChannels"] = [];

        for (var i = 0; i < channelsList.length; i++) {
            var chanData = channels[channelsList[i]]; //current channel in the iteration
            if(chanData["channelInfo"] !== null){
				var currentViewers = chanData["viewersHist"][1];
	            var chanId = chanData["channelInfo"]["id"];
	            //console.log(chanData);
	            if ((chanData["numChecked"] === 1 || chanData["numChecked"]%60 === 0) && currentViewers >= 0) {
	                //comeback here
	                addStreamInfo(chanData);
	              
	                output["dataToAdd"].push(currentViewers);
	            }
	            else {
	            	//if channel is online then add viewerership numbers
	                if (chanData["status"] === 1) {
	                    output["dataToAdd"].push(currentViewers);
	                    output["contributingChannels"].push(chanData["channelInfo"]["channel"]);
	                }
	                else {
	                    if (c in streamInfo["chan"]) {
	                        $("#status-" + chanId).removeClass("online").addClass("offline");
	                        $("#status-" + chanId).attr("title", "Offline");
	                    }
	                    if (channelsList.length == 1) {
	                        endTracking();
	                    }
	                    else {
	                        activeChannels--;
	                    }
	                    output["offlineExists"] = true;
	                    return output;
	                }
	            }
	            output["viewershipSum"] += currentViewers;
	            //create the list elements for channels which contributed to peak viewership
	            output["peakViewersHTML"] += "<li>" + chanData["channelInfo"]["channel"] + " - " + currentViewers + "</li>";
		        $("#totalViews-" + chanId).html(chanData["channelInfo"]["totalViews"]);
		        $("#stream-cat-" + chanId).html(chanData["channelInfo"]["cat"]);
	            updateStreamInfo(chanData);
            }
            else{
            	channelsList.splice(i, 1);
            	activeChannels--;
            	output["offlineExists"] = true;
            }
        }
        output["peakViewersHTML"] += "</ul>";
        return output;
    }

    function updateStreamInfo(channelData) {
        var uptime = Math.floor((((new Date()).getTime() / 1000) - channelData["channelInfo"]["createdAt"]) / 60);
        $("#uptime-" + channelData["channelInfo"]["id"]).html(uptime + " minutes");
        $("#stream-viewers-" + channelData["channelInfo"]["id"]).html(Math.floor(channelData["viewersHist"][0] / channelData["viewersHist"][3]) +
            " Avg <span class='viewers octicon octicon-organization'></span>");
    }

    function startTracking() {
        $("#chart-title").html("Tracking");
        $("#end-tracking").removeClass("hide");
        $("#side-content").removeClass("hide");
        $("#save-chart").toggleClass("hide");
        setup = true;
        update = setInterval(getData, 1000 * cd);
        intervalSet = true;
    }

    function endTracking() {
        $("#chart-title").html();
        channelsList = [];
        //$("#end-tracking").addClass("hide");
        //$("#side-content").addClass("hide");
        $("#save-chart").toggleClass("hide");
        setup = false;
        clearInterval(update);
        //$("#save-chart").addClass("hide");
        intervalSet = false;
        if (activeChannels === 0) {
            clearSideContent();
        }
    }

    function toggleLoading() {
        if (!$("#mini-loader-yt").hasClass("invis")) {
            $("#mini-loader-yt").toggleClass("invis");
        }
        if (!$("#mini-loader-tw").hasClass("invis")) {
            $("#mini-loader-tw").toggleClass("invis");
        }
        if (!$("#main-loader").hasClass("hide")) {
            $("#main-loader").toggleClass("hide");
        }
    }

    function enableAddButtons() {
        if ($("#add-yt-channel").is(":disabled") || $("#add-tw-channel").is(":disabled")) {
            $("#add-yt-channel").prop("disabled", false);
            $("#add-tw-channel").prop("disabled", false);
        }
    }

    function disableAddButtons() {
        if (!$("#add-yt-channel").is(":disabled") || !$("#add-tw-channel").is(":disabled")) {
            $("#add-yt-channel").prop("disabled", true);
            $("#add-tw-channel").prop("disabled", true);
        }
    }
    function clearSideContent() {
        $("#end-tracking").addClass("hide");
        $("#side-content").addClass("hide");
        var myNode = document.getElementById("streamer-stats");
        var children = myNode.children;
        while (children.length > 3) {
            myNode.removeChild(children[3]);
        }
    }

    function addStreamInfo(data) {
        var title = data["channelInfo"]["title"].replace(/'/g, "&apos;");
        console.log(title);
        var now = new Date(0);
        var chanId = data["channelInfo"]["id"];
        console.log(data["channelInfo"]["createdAt"]);
        now.setUTCSeconds(data["channelInfo"]["createdAt"]);
        var liHTML = "<li class='list-group-item d-flex justify-content-between lh-condensed'>" +
            "<div><h5 class='my-0 stream-title' id='streamer-" + chanId + "' data-container='body'" +
            "data-toggle='popover' data-placement='left' title='" + title + "'>" +
            data["channelInfo"]["channel"] + " <span id='status-" + chanId + 
            "' class='online octicon octicon-primitive-dot' data-toggle='tooltip' data-placement='top' title='Online'></span></h5>" +
            "<ul class='list-group' id='popover-content-" + chanId + "' style='display: none'>" +
            "<li class='list-group-item d-flex justify-content-between'>" +
            "<div><strong class='my-0 success'>Start</strong></div>" +
            "<span class='text-muted' id='startDate-" + chanId + "'>" + formatDate(now) + "</span></li>" +
            "<li class='list-group-item d-flex justify-content-between'>" +
            "<div><strong class='my-0 success'># Chatters</strong></div>" +
            "<span class='text-muted' id='numChatters-" + chanId + "'>" + data["channelInfo"]["chatters"] + "</span></li>" +
            "<li class='list-group-item d-flex justify-content-between'>" +
            "<div><strong class='my-0 success'>Uptime</strong></div>" +
            "<span class='text-muted' id='uptime-" + chanId + "'></span></li>" +
            "<li class='list-group-item d-flex justify-content-between'>" +
            "<div><strong class='my-0 success'>Total Followers</strong></div>" +
            "<span class='text-muted' id='totalFollowers-" + chanId + "'>" + data["channelInfo"]["followers"] + "</span></li>" +
            "<li class='list-group-item d-flex justify-content-between'>" +
            "<div><strong class='my-0 success'>Total Views</strong></div>" +
            "<span class='text-muted' id='totalViews-" + chanId + "'>" + data["channelInfo"]["totalViews"] + "</span></li>" +
            "<li class='list-group-item d-flex justify-content-between'>" +
            "<div><strong class='my-0 success'>Platform</strong></div>" +
            "<span class='text-muted' id='platform-" + chanId + "'>" + data["channelInfo"]["platform"] + "</span></li></ul>" +
            "<small id='stream-cat-" + chanId + "' class='text-muted'>" + data["channelInfo"]["cat"] + "</small></div>" +
            "<strong id='stream-viewers-" + chanId + "'></strong></li>";
        $("#streamer-stats").append(liHTML);
        $("#streamer-" + chanId).popover(popoverSettings);
	    $("#title-" + chanId).html(title);
        chartData.addColumn("number", data["channelInfo"]["channel"]);

    }
</script>
@stop