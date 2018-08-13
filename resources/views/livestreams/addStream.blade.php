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
                <form class="form-add-channel" action="/addStream" method="GET">
                    <div class="justify-content-center mb-3" style="text-align: center; display: inline-block; width: 100%">
                        <h1 class="h3 font-weight-normal">
                            <span style="font-size: 2.5em" class="octicon octicon-telescope"></span>
                        </h1>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <h6 id="twitch-platform">Twitch Channel</h6>       
                    <div class="form-group"> 
                        <input type="twitch" id="inputTwitch" class="form-control" name="twitch" maxlength="30" placeholder="riotgames" autofocus>
                    </div>

                    <h6 id="youtube-platform">Youtube URL</h6>
                    <div class="form-group">                           
                        <input type="youtube" id="inputYoutube" class="form-control"  name="youtube" maxlength="60" placeholder="https://www.youtube.com/CHANNEL">
                    </div>

                    <hr class="featurette-divider">            
                    
                    <button class="my-btn btn btn-lg btn-block btn-outline-success mb-2" type="submit">Start Tracking</button>
                </form>
            </div>
        </div>
        <div class="col-md-4" id="side-content">
            <div class="media text-muted pt-3 custom-flex justify-content-center">
                <h3 class="my-0 success">
                    Live Tracking 
                </h3>
            </div>

            <hr class="featurette-divider">

            <div class="media text-muted pt-3 custom-flex justify-content-between">
                <h5 class="my-0">
                    <a href="#">Dyrus, TheOddone, LOL</a>
                </h5>
            </div>
            <div class="media text-muted pt-3 custom-flex justify-content-between">
                <h5 class="my-0">
                    <a href="#">Riotgames, pvpes, ogaming</a>
                </h5>
            </div>

            <div class="media text-muted pt-3 custom-flex justify-content-center">
                <h3 class="my-0 success">Top Streams</h3>
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
