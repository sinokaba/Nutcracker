@extends('layout')

@section('customCss')
    <link href="{{ URL::asset('/css/addStream.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
<div class="container">
    <h1>
        Track Channels
    </h1>

    <hr class="featurette-divider">

    <div class="row">
        <div class="col-md-8">
            <div id="form-container">
                <form class="form-add-channel" action="/addStream" type="GET" onsubmit="return validateForm()">
                    <div class="justify-content-center mb-3" style="text-align: center; display: inline-block; width: 100%">
                        <h1 class="h3 font-weight-normal">
                            <span style="font-size: 2.5em" class="octicon octicon-telescope"></span>
                        </h1>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <h6 id="twitch-platform">Twitch Channel</h6>       
                    <div class="form-group" id="twitch-input-container"> 
                        <input type="twitch" id="inputTwitch" class="form-control" maxlength="25" name="twitch" maxlength="30" placeholder="riotgames" autofocus>
                        <div class="invalid-feedback">
                            Invalid Twitch channel
                        </div>
                    </div>

                    <h6 id="youtube-platform">Youtube URL</h6>
                    <div class="form-group" id="youtube-input-container">                           
                        <input type="youtube" id="inputYoutube" class="form-control" maxlength="60" name="youtube" maxlength="60" placeholder="https://www.youtube.com/CHANNEL">
                         <div class="invalid-feedback">
                            Invalid Youtube channel or video URL
                        </div>                   
                    </div>

                    <hr class="featurette-divider">            
                    
                    <button class="my-btn btn btn-lg btn-block btn-outline-success mb-2" type="submit">Start Tracking</button>
                </form>
            </div>
        </div>
        <div class="col-md-4" id="side-content">

            <div class="media text-muted">
                <h5 class="my-0">
                    <img src="{{ asset('imgs/twitch_icon.png') }}" alt="twitch_icon" style="width:1.5em">
                </h5>
                <ul class="track-input-ref">
                    <li>twitch.tv/<span class="text-success">{channel_name}</span></li>
                    <li>5-25 characters consisting of numbers and letters</li>
                </ul>
            </div>
            <div class="media text-muted">
                <h5 class="my-0">
                    <img src="{{ asset('imgs/youtube_icon.png') }}" alt="youtube_icon" style="width:1.5em">
                </h5>
                <ul class="track-input-ref">
                    <li>Youtube URL for a channel or a video</li>
                    <li><span class="text-success">https://www.youtube.com/channel/{channel_id}</span></li>
                    <li><span class="text-success">https://www.youtube.com/watch=?v{video_id}</span></li>
                </ul>
            </div>

            <div class="media text-muted justify-content-center">
                <h4 class="my-0 success">Top Online Streams</h4>
            </div>

            <hr class="featurette-divider">

            <ul class="list-group mb-3 chart-side-bar" id="streamer-stats">
                @foreach ($streams as $stream)
                <li class="list-group-item channel-container custom-flex justify-content-between lh-condensed">
                    <div>
                        <a class="chan-name" href="/channel/{{ $stream['channel'] }}">
                            <h5 class="my-0" id="streamer-36029255">
                                {{ $stream['channel'] }}
                            </h5>
                        </a>
                        <small id="stream-cat-36029255" class="text-muted">{{ $stream['cat'] }}</small>
                    </div>
                    <strong class="stream-viewers" id="stream-viewers-36029255">
                        {{ $stream['viewers'] }} 
                        <span class="viewers octicon octicon-person"></span>
                    </strong>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@stop

@section('addScript')
<script type="text/javascript">
    //delcare the regex expressions for validating user input on the forms
    var youtubeRe = /^(https:\/\/www.youtube.com\/)[\w\-\._~:/?#[\]@!\$&\(\)\*\+,;=.]+$/g;
    var twitchRe = /^[a-zA-Z0-9_]{4,25}$/;

    function validateForm(){
        if(!$("#inputTwitch").val().match(twitchRe) && !$("#inputYoutube").val().match(youtubeRe)){
            $("#inputTwitch").addClass("is-invalid");
            $("#inputYoutube").addClass("is-invalid");
            return false;
        }
        else if($("#inputYoutube").val().match(youtubeRe) && $("#inputTwitch").val().length > 0){
            if($("#inputYoutube").hasClass("is-invalid")){
                $("#inputYoutube").removeClass("is-invalid").addClass("is-valid");
            }
            $("#inputTwitch").addClass("is-invalid");
            return false;
        }
        else if($("#inputTwitch").val().match(twitchRe) && $("#inputYoutube").val().length > 0){
            if($("#inputTwitch").hasClass("is-invalid")){
                $("#inputTwitch").removeClass("is-invalid").addClass("is-valid");
            }
            $("#inputYoutube").addClass("is-invalid");
            return false;
        }
        return true;
    }
</script>
@stop