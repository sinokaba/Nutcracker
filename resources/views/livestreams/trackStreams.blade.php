@extends('layout')

@section('content')
<div class="container">
    <form class="needs-validation" id="tw-channel-form" onkeypress="return event.keyCode != 13;" novalidate>
        <div class="form-row justify-content-center">
            <div class="col-auto my-1" style="width:9em">
                <label>
                    <h6>
                        Twitch Channel
                    </h6>
                </label>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control mb-2" style="width:24em" name="twitch-channel" maxlength="25" id="twitch-channel" placeholder="riotgames" style="width:21em" required>
                <div class="invalid-feedback" id="twitch-input-feedback">
                    Invalid Twitch channel
                </div>
            </div>
            <div class="col-auto">
                <button class="my-btn btn btn-outline-success mb-2" type="button" id="add-tw-channel" disabled>Add</button>
            </div>
            <div class="col-auto">
                <div id="mini-loader-tw" class="mini-loader invis"></div>
            </div>
        </div>
    </form>
    <form class="needs-validation" id="yt-channel-form" onkeypress="return event.keyCode != 13;" novalidate>
        <div class="form-row justify-content-center">
            <div class="col-auto my-1" style="width:9em">
                <label>
                    <h6>
                        Youtube URL
                    </h6>
                </label>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control mb-2" style="width:24em" name="youtube-channel" maxlength="60" id="youtube-channel" placeholder="https://www.youtube.com/CHANNEL" required>
                <div class="invalid-feedback" id="twitch-input-feedback">
                    Invalid Youtube URL
                </div>
            </div>
            <div class="col-auto">
                <button class="my-btn btn btn-outline-success mb-2" type="button" id="add-yt-channel" disabled>Add</button>
            </div>
            <div class="col-auto">
                <div id="mini-loader-yt" class="mini-loader invis"></div>
            </div>
        </div>
    </form>

    <hr class="featurette-divider">

    <div class="row justify-content-center hide" id="main-loader">
        <div class="main-loader"></div>
    </div>
    <div class="row invis" id="tracking-content">
        <div class="col-md-8">
            <h3 id="chart-title"></h3>
            <div id="viewership-chart-container" style="position: relative; width: 100%; height: 70vh">
                <canvas id="viewership-chart" download="nutcracker_chart.jpg">
            </div>
        </div>
        <div class="col-md-4" id="side-content">
            <h3 class="border-bottom border-gray pb-2 mb-0">
                Additional Stats
                <span class="octicon octicon-graph"></span>
            </h3>
            <div class="media text-muted pt-3 custom-flex justify-content-between">
                <h5 class="my-0">Total Live Viewers</h5>
                <strong id="total-viewers">
                    <span class="viewers octicon octicon-organization"></span>
                </strong> 
            </div>
            <div class="media text-muted pt-3 custom-flex justify-content-between">
                <h5 class="my-0" style="cursor:pointer" data-container="body" data-placement="left" data-toggle="popover" id="popover-0">Peak Total Viewers</h5>
                <strong id="peak-viewers">
                    <span class="viewers-peak octicon octicon-organization"></span>
                </strong>
                <div id="popover-0-content" class="hide">
                </div>
            </div>
            <div class="media text-muted pt-3 custom-flex justify-content-between">
                <h5 class="my-0 success">Uptime</h5>
                <strong id="uptime">
                    00:00 
                <span class="octicon octicon-clock"></span>
                </strong>
            </div>
            <div class="media text-muted pt-3 custom-flex justify-content-center">
                <h4 class="my-0 success">Streams</h4>
            </div>

            <hr class="featurette-divider">

            <ul class="list-group mb-3 chart-side-bar" id="streamer-stats">
            </ul>
            
            <button id="end-tracking" type="button" class="hide btn btn-danger">Stop</button>
            <button id="save-chart" type="button" class="my-btn hide btn btn-outline-success">Download Chart</button>
        </div>
    </div>
</div>

<hr class="featurette-divider">

@stop
@section('addScript')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script type="text/javascript">
    myStorage = window.localStorage;

    //sortable is a function implemented on jquery which gives the user the ability to move items on a list dynamically
    /*
    $("#streamer-stats").sortable();
    $("#streamer-stats").disableSelection();
    */
    // Set a callback to run when the Google Visualization API is loaded.
    //google.setOnLoadCallback(initTable);

    //declare the settings for the popover and how it's controlled
    var popoverSettings = {
        html: true,
        container: "body",
        placement: "left",
        content: function() {
            var popoverId = (this.id).split("-");
            console.log(popoverId);
            return $("#popover-content-" + popoverId[1]).html();
        }
    }
    var colorNames = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)'
    };
    var channels = {};  //keeps information of all channels added
    var channelsList = []; //keeps track of name of all channels added
    var viewershipHistory = {};
    var activeChannels = 0;    
    //delcare the regex expressions for validating user input on the forms
    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;
    //this setup variable is the boolean variable which helps decide when to show or hide html elements meant to give more information to user while tracking
    var setup = false;
    //max channels that the user can input, if it is reached the input fields are disabled
    var maxChannels = 7;
    //how many seconds must be waited until another ajax call to request updated information can be executed
    var cd = 120;
    var colorKeys = Object.keys(colorNames);
    var intervalSet = false;
    var chartRendered = false;
    var peakViewers = 0;
    var config = {
        type: "line",
        fill: false,
        data: {
            datasets: []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Live Viewership'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Time'
                    },
                    ticks: {
                        display: false
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Viewers'
                    }
                }]
            },
            animation: {
                onComplete: function() {
                    chartRendered = true
                }
            }
        }
    };
    //the start variable help keep track of how long the tracking has been going on
    //update variable calls the getData() funciton every interval of cd seconds
    var start, update;

    //enable tooltips
    $(function(){
        $("[data-toggle='tooltip']").tooltip();
        var ctx = document.getElementById('viewership-chart').getContext('2d');
        window.myLine = new Chart(ctx, config);
    });
    //add the csrf token to ajax calls so that laravel can stop crying
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
        }
    });

    //*********

    //not the first time it's being loaded
    if(myStorage.getItem(window.location.href.split('/')[4]) !== null){
        var pageData = JSON.parse(myStorage.getItem(window.location.href.split('/')[4]));
        console.log(pageData);
        if(pageData['twitch'] !== null && pageData['twitch'].trim().length > 0){
            addStream(pageData['twitch'], 0);
        }
        if(pageData['youtube'] !== null && pageData['youtube'].trim().length > 0){
            addStream(pageData['youtube'], 1);
        }
    }
    else{
        if('{!! $twitch !!}' !== null){
            addStream('{!! $twitch !!}', 0);
        }
        if('{!! $youtube !!}' !== null && '{!! $youtube !!}'.trim().length > 0){
            addStream('{!! $youtube !!}', 1);
        }
        myStorage.setItem('{!! $id !!}', JSON.stringify({'twitch': '{!! $twitch !!}', 'youtube': '{!! $youtube !!}'}));
    }


    $("#add-tw-channel").on('click', function(event) {
        if (channelsList.length > 0) {
            $("#mini-loader-tw").toggleClass("invis");
        }
        checkInput("#twitch-channel", 0);
    });

    $("#add-yt-channel").on('click', function(event) {
        if (channelsList.length > 0) {
            $("#mini-loader-yt").toggleClass("invis");
        }
        checkInput("#youtube-channel", 1);
    });

    // Loop over them and prevent submission
    function checkInput(formId, platformId) {
        disableAddButtons();
        console.log($(formId).serializeArray());
        if($(formId).serializeArray().length === 0){
            invalidInput(platformId, 3);
        }
        else{
            if ($(formId)[0].checkValidity() === false) {
                invalidInput(platformId, 0);
            }
            //channel already added
            else if (channelsList.includes($(formId).serializeArray()[0]["value"])) {
                invalidInput(platformId, 1);
            }
            else {
                //0 is twitch input field, does not match twitch channel pattern
                if (platformId == 0 && ($(formId).serializeArray()[0]["value"]).match(twitchRe) === null) {
                    invalidInput(platformId, 2);
                }
                //1 is youtube input field, does not match youtube url pattern
                else if (platformId == 1 && ($(formId).serializeArray()[0]["value"]).match(youtubeRe) === null) {
                    invalidInput(platformId, 2);
                }
                else {
                    if (channelsList.length >= maxChannels) {
                        invalidInput(null, 3);
                    }
                    else {
                        var userInput = $(formId).serializeArray()[0]["value"];
                        addStream(userInput, platformId);
                        $(formId)[0].classList.add("was-validated");
                        if(platformId == 0){
                            $("#twitch-channel").removeClass("is-invalid").addClass("is-valid");
                        }
                        else{
                            $("#youtube-channel").removeClass("is-invalid").addClass("is-valid");
                        }
                        $(formId).val("");
                    }
                }
            }
        }
    }

    function invalidInput(field, type){
        enableAddButtons();
        toggleLoading();
        if(field === 0){
            $("#twitch-channel").removeClass("is-valid").addClass("is-invalid");
            if(type === 1){  
                $("#twitch-input-feedback").html("Channel already added.");
            }   
            else if(type === 2){
                $("#twitch-input-feedback").html("Invalid Twitch Channel");
            }
            else{
                $("#twitch-input-feedback").html("Channel offline or does not exist.");
            }
        }
        else if(field === 1){
            $("#youtube-channel").removeClass("is-valid").addClass("is-invalid");
            if(type === 1){
                $("#youtube-input-feedback").html("Channel already added");
            }
            else if(type === 2){
                $("#youtube-input-feedback").html("Invalid Youtube URL");
            }
            else{
                $("#youtube-input-feedback").html("Channel offline or does not exist.");
            }
        }
        else{
            $("#twitch-channel").val("").prop("disabled", true);
            $("#youtube-channel").val("").prop("disabled", true);
            alert("Max channels added reached.");
        }
    }

    function addStream(userInput, platform){
        channels[userInput] = {
            "status": 1, //1 = online, 0 = offline
            'numChecked': 0, //number of items this channel was checked
            "channelInfo": null, //derived from api calls
            "viewersStats": [0, 0, 0, 0], //total viewers, current viewers, peak viewers, data count
            "addedToDB": 0, //0 = not added, 1 = channel added, 2 = stream added
            "platform": platform //0 = twitch, 1 = youtube
        };
        channelsList.push(userInput);
        //console.log(channels);
        activeChannels++;
        if (channelsList.length === 1 && activeChannels === 1) {
            $("#main-loader").toggleClass("hide");
            start = Math.floor(Date.now() / 60000);
            setInterval(updateTime, 1000 * cd);
        }
        getData();
    }

    function addOldData(){
        channelsList = Object.keys(data);
        addNewDataSet(channels);
        if (channelsList.length === 1 && activeChannels === 1) {
            $("#main-loader").toggleClass("hide");
            start = Math.floor(Date.now() / 60000);
            setInterval(updateTime, 1000 * cd);
        }    
        getData();   
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

    $("#save-chart").on("click", function() {
        if (!chartRendered) return; // return if chart not rendered
            html2canvas(document.getElementById("viewership-chart-container"), {
            onrendered: function(canvas) {
                var dlTitle = "nutcracker_chart";
                for(var i = 0; i < channelsList.length; i++){
                    if(channels[channelsList[i]]["channelInfo"] !== null){
                        dlTitle += "_"+channels[channelsList[i]]["channelInfo"]["channel"];
                    }
                }
                var link = document.createElement("a");
                link.href = canvas.toDataURL("image/png", 1.0);
                link.download = dlTitle + ".png";
                $("#viewership-chart-container").css({"width": "100vw", "height": "100vh"});
                window.myLine.update();
                link.click();
                $("#viewership-chart-container").css({"width": "100%", "height": "70vh"});
            }
        })
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
        var jsonData = $.ajax({
            url: "/getViewershipStats",
            data: {
                channels: channels,
                chanList: channelsList
            },
            method: "POST",
            dataType: "JSON",
            success: function(res) {
                channels = res[1];
                var d = new Date(0); //get date from epoch
                d.setUTCSeconds(res[0]);
                var streamData = processStreamData();

                if (activeChannels === 0) {
                    endTracking();
                }
                else{
                    updateStats(streamData, d);
                }
                toggleLoading();
                enableAddButtons();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(typeof(XMLHttpRequest.responseText) + " " + XMLHttpRequest.responseText + "\n");
                console.log("Error: " + errorThrown);
            }
        });
    }

    function updateStats(streamData, date){
        if (!setup) {
            startTracking();
        }
        console.log(streamData["dataToAdd"]);
        addDataPoints(date, streamData["dataToAdd"]);

        $("#total-viewers")[0].childNodes[0].nodeValue = streamData["viewershipSum"] + " ";
        if (streamData['viewershipSum'] > peakViewers) {
            $("#peak-viewers")[0].childNodes[0].nodeValue = streamData["viewershipSum"] + " ";
            peakViewers = streamData['viewershipSum'];
            $("#popover-0").popover(popoverSettings);
            $("#popover-0-content").html(streamData['peakViewersHTML']);
        }
        if (!intervalSet) {
            intervalSet = true;
            update = setInterval(getData, 1000 * cd);
        }
    }

    function processStreamData() {
        var output = {};
        output["viewershipSum"] = 0;
        output["peakViewersHTML"] = "<ul class='list-group' id='popover-content-0'>";
        output["dataToAdd"] = [];
        temp = channelsList;
        console.log("channels list: " + channelsList);
        for (var i = 0; i < channelsList.length; i++) {
            var chanData = channels[channelsList[i]]; //current channel in the iteration
            var currentViewers = chanData["viewersStats"][1];            
            if (chanData["channelInfo"] !== null) {
                var chanId = chanData["channelInfo"]["id"];
                if (chanData["status"] == 1) {
                    //add the info of new channels
                    if (!datasetExists(chanData["channelInfo"]["channel"])) {
                        addStreamInfo(chanData);
                    }
                    output["viewershipSum"] += currentViewers;
                    //create the list elements for channels which contributed to peak viewership
                    output["peakViewersHTML"] += "<li>" + chanData["channelInfo"]["channel"] + " - " + currentViewers + "</li>";
                    updateStreamInfo(chanData);
                    if($("#status-" + chanId).hasClass("offline")){
                        $("#status-" + chanId).removeClass("offline").addClass("online");
                        $("#status-" + chanId).attr("title", "Online");                        
                    }
                }
                else if ($("#status-" + chanId).hasClass("online")) {
                        //indicate a channel that has gone offline
                        $("#status-" + chanId).removeClass("online").addClass("offline");
                        $("#status-" + chanId).attr("title", "Offline");
                }
                output["dataToAdd"].push(currentViewers);
                console.log(chanData);
                viewershipHistory[chanData["channelInfo"]["channel"]].push(currentViewers);
                $("#stream-viewers-" + chanId).html(chanData["viewersStats"][1] + " <span class='viewers octicon octicon-person'></span>");
            }
            else{
                invalidInput(channels[channelsList[i]]["platform"], 3);
                //remove channel that is offline or does not exists form list from list and channels object
                if(channelsList[i] in channels){
                    delete channels[channelsList[i]];
                }
                activeChannels--;
                console.log(channels);
                temp.splice(i, 1);
            }
        }
        output["peakViewersHTML"] += "</ul>";
        channelsList = temp;
        console.log("channels list: " + channelsList);
        return output;
    }

    function updateStreamInfo(channelData) {
        var chanId = channelData["channelInfo"]["id"];
        var uptime = Math.floor((((new Date()).getTime() / 1000) - channelData["channelInfo"]["createdAt"]) / 60);
        $("#uptime-" + chanId).html(uptime + " minutes");
        $("#stream-avg-" + chanId).html(Math.floor(channelData["viewersStats"][0] / channelData["viewersStats"][3]));
        if(channelData["viewersStats"][2] > $("#stream-peak-" + chanId).text()){
            $("#stream-peak-" + chanId).html(channelData["viewersStats"][2]);
        }
        if(channelData["numChecked"] % 60 == 0){
            $("#total-views-" + chanId).html(channelData["channelInfo"]["totalViews"]);
            $("#total-followers" + chanId).html(channelData["channelInfo"]["followers"]);
            $("#stream-cat-" + chanId).html(channelData["channelInfo"]["cat"]);
            $("#stream-title-" + chanId).html(channelData["channelInfo"]["title"].replace(/'/g, "&apos;"));
            $("#num-chatters-" + chanId).html(channelData["channelInfo"]["chatters"]);
        }
    }

    function startTracking() {
        $("#chart-title").html("Tracking");
        $("#end-tracking").removeClass("hide");
        $("#tracking-content").removeClass("invis");
        $("#save-chart").toggleClass("hide");
        setup = true;
        update = setInterval(getData, 1000 * cd);
        intervalSet = true;
    }

    function endTracking() {
        $("#chart-title").html();
        channelsList = [];
        setup = false;
        clearInterval(update);
        $("#tracking-content").addClass("invis");
        //empty chart data
        //config.data.datasets = [];
        //$("#save-chart").addClass("hide");
        intervalSet = false;
        if (activeChannels === 0 && channelsList.length < 1) {
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
        var liHTML = "<li class='list-group-item channel-container custom-flex justify-content-between lh-condensed'>" +
            "<div><a class='chan-name' data-toggle='collapse' href='#collapse-" + chanId + "' role='button' aria-expanded='false' aria-controls='collapseExample'>"+
            "<h4 class='my-0' id='streamer-" + chanId + "'>" + data["channelInfo"]["channel"] + " <span id='status-" + chanId + 
            "' class='online octicon octicon-primitive-dot' data-toggle='tooltip' data-placement='top' title='Online'></span></h4></a>" +
            "<small id='stream-cat-" + chanId + "' class='text-muted'>" + data["channelInfo"]["cat"] + "</small></div>" +
            "<strong class='stream-viewers' id='stream-viewers-" + chanId + "'></strong></li>" +
            "<div class='collapse' id='collapse-" + chanId + "'><ul class='list-group'>"+
                "<li class='list-group-item d-flex justify-content-between'>" + 
                "<div><strong class='my-0 success'>Title</strong></div>" + 
                "<span class='text-muted' id='stream-title-" + chanId + "'>" + title + "</span></li>"+
                "<li class='list-group-item d-flex justify-content-between'>" + 
                "<div><strong class='my-0 success'>Average Viewers</strong></div>" + 
                "<span class='text-muted' id='stream-avg-" + chanId + "'></span></li>"+
                "<li class='list-group-item d-flex justify-content-between'>" + 
                "<div><strong class='my-0 success'>Peak Viewers</strong></div>" + 
                "<span class='text-muted' id='stream-peak-" + chanId + "'></span></li>"+                
                "<li class='list-group-item d-flex justify-content-between'>" +
                "<div><strong class='my-0 success'>Start</strong></div>" +
                "<span class='text-muted' id='start-date-" + chanId + "'>" + formatDate(now) + "</span></li>" +
                "<li class='list-group-item d-flex justify-content-between'>" +
                "<div><strong class='my-0 success'># Chatters</strong></div>" +
                "<span class='text-muted' id='num-chatters-" + chanId + "'>" + data["channelInfo"]["chatters"] + "</span></li>" +
                "<li class='list-group-item d-flex justify-content-between'>" +
                "<div><strong class='my-0 success'>Uptime</strong></div>" +
                "<span class='text-muted' id='uptime-" + chanId + "'></span></li>" +
                "<li class='list-group-item d-flex justify-content-between'>" +
                "<div><strong class='my-0 success'>Total Followers</strong></div>" +
                "<span class='text-muted' id='total-followers-" + chanId + "'>" + data["channelInfo"]["followers"] + "</span></li>" +
                "<li class='list-group-item d-flex justify-content-between'>" +
                "<div><strong class='my-0 success'>Total Views</strong></div>" +
                "<span class='text-muted' id='total-views-" + chanId + "'>" + data["channelInfo"]["totalViews"] + "</span></li>" +
                "<li class='list-group-item d-flex justify-content-between'>" +
                "<div><strong class='my-0 success'>Platform</strong></div>" +
                "<span class='text-muted' id='platform-" + chanId + "'>" + data["channelInfo"]["platform"] + "</span></li>" +
            "</ul></div>";
        $("#streamer-stats").append(liHTML);
        //chartData.addColumn("number", data["channelInfo"]["channel"]);
        addNewDataSet(data["channelInfo"]["channel"], data);
    }

    function addNewDataSet(channel, channelInfo){
        var color = colorKeys[config.data.datasets.length % colorKeys.length];
        console.log("num data sets: " + config.data.datasets.length + " num colors: " + colorKeys.length);
        var newColor = colorNames[color];
        var newDataset = {
            label: channel,
            backgroundColor: newColor,
            borderColor: newColor,
            data: [],
            fill: false,
            pointRadius: 6,
            numData: 0
        };
        config.data.datasets.push(newDataset);

        viewershipHistory[channel] = [];
        for(var i = 0; i < config.data.datasets[0].data.length; i++){
            newDataset.data.push(null);
            viewershipHistory[channel].unshift(null);
        }
        window.myLine.update();        
    }

    function addCompleteDataSet(completeData){
        for(var i = 0; i < channelsList.length; i++){
            if(channels[channelsList[i]]["channelInfo"] !== null){
                var color = colorKeys[config.data.datasets.length % colorKeys.length];
                var newColor = colorNames[color];
                var newDataset = {
                    label: completeData["channelInfo"]["channel"],
                    backgroundColor: newColor,
                    borderColor: newColor,
                    data: completeData["viewersHist"],
                    fill: false,
                    pointRadius: completeData["viewersHist"].length < 10 ? 6 : 2,
                    numData: 0
                };
                config.data.datasets.push(newDataset);
                activeChannels++;
            }
        }
        window.myLine.update();   
    }

    function datasetExists(label){
        for(var i = 0; i < config.data.datasets.length; i++){
            if(config.data.datasets[i].label === label){
                return true;
            }
        }
        return false;
    }

    function addDataPoints(label, dataRow){
        if (config.data.datasets.length > 0) {
            config.data.labels.push(label);
            for(var i = 0; i < config.data.datasets.length; i++){
                config.data.datasets[i].data.push(dataRow[i]);
                config.data.datasets[i].numData++;
                if(config.data.datasets[i].numData > 20){
                    config.data.datasets[i].pointRadius = 0;
                }
                else if(config.data.datasets[i].numData > 10){
                    config.data.datasets[i].pointRadius = 2;
                }
            }
            window.myLine.update();
        }
    }
</script>
@stop