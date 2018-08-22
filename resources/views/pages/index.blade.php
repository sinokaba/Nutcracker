@extends('layout')

@section('customCss')
    <link href="{{ URL::asset('/css/welcome.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center welcome-page">
        @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <a href="{{ url('/home') }}">Home</a>
            @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
        @endif
        <div class="title">
            Nutcracker
        </div>
    </div>
    <div class="row justify-content-center text-center welcome-page">
        <div class="subtitle">
            Viewership statistics and tracking for Livestreams.
        </div>
    </div>

    <hr class="featurette-divier">

    <div id="stream-carousel" class="carousel slide" data-ride="carousel" data-interval="false">
        <ol class="carousel-indicators">
            <li data-target="#stream-carousel" data-slide-to="0" class="active"></li>
            <li data-target="#stream-carousel" data-slide-to="1"></li>
            <li data-target="#stream-carousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner" style="height: 27em !important">
            <div class="carousel-item active">
                <div id="twitch-embed-1" align='center'>
                    <iframe src="https://player.twitch.tv/?channel={{ $topStreams[2] }}&autoplay=false" width="100%" height="380" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
            <div class="carousel-item">
                <div id="twitch-embed-2" align='center'>
                    <iframe src="https://player.twitch.tv/?channel={{ $topStreams[3] }}&autoplay=false" width="100%" height="380" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
            <div class="carousel-item">
                <div id="youtube-embed-1" align='center'>
                    <iframe height="380" width="100%" align='center' src="https://www.youtube.com/embed/{{ $topStreams[0] }}" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#stream-carousel" role="button" data-slide="prev">
            <span class="octicon octicon-chevron-left" style="font-size: 5em" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#stream-carousel" role="button" data-slide="next">
            <span class="octicon octicon-chevron-right" style="font-size: 5em" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <hr class="featurette-divider">

    <div class="row featurette">
        <div class="col-md-5">
            <h2 class="featurette-heading">Track your favourite streams. </h2>
            <p class="lead">See the viewership of your favourite streams grow LIVE.</p>
            <p class="lead">Youtube or Twitch, Nutcracker is the streaming hub of the web.</p>
        </div>
        <div class="col-md-7">
            <img class="featurette-image img-fluid mx-auto" src="{{ asset('imgs/tracking_sample.png') }}" alt="tracking_sample.png">
        </div>
    </div>
    <hr class="featurette-divider">

    <div class="row featurette">
        <div class="col-md-7">
            <img class="featurette-image img-fluid mx-auto" src="{{ asset('imgs/nut_promo.png') }}" alt="tracking_sample.png">            
        </div>
        <div class="col-md-5">
            <h2 class="featurette-heading">Get access to historical data of Livestream channels</h2>
            <p class="lead">Youtube or Twitch, be able to view past broadcasts, peak viewers, chat activity, and much more.</p>
            <p class="lead">As Streamers, you will be able to know how to grow your streams.</p>
        </div>
    </div>
</div>

<hr class="featurette-divider">

@stop