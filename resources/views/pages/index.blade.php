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
        <div class="row justify-content-center welcome-page">
            <div class="subtitle">
                Viewership statistics and tracking for Livestreams.
            </div>
        </div>
          <div id="stream-carousel" class="carousel slide" data-ride="carousel" data-interval="false">
            <ol class="carousel-indicators">
              <li data-target="#stream-carousel" data-slide-to="0" class="active"></li>
              <li data-target="#stream-carousel" data-slide-to="1"></li>
              <li data-target="#stream-carousel" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
              <div class="carousel-item active">
                    <div id="twitch-embed-1" align='center'>
                        <iframe src="https://player.twitch.tv/?channel={{ $topStreams[2] }}" width="800" height="450" frameborder="0" scrolling="no"></iframe>
                    </div>      
              </div>
              <div class="carousel-item">
                    <div id="twitch-embed-2" align='center'>
                        <iframe src="https://player.twitch.tv/?channel={{ $topStreams[3] }}" width="800" height="450" frameborder="0" scrolling="no"></iframe>
                    </div>
              </div>
              <div class="carousel-item">
                <div id="youtube-embed-1" align='center'>
                    <iframe width="800" height="450" align='center' src="https://www.youtube.com/embed/live_stream?channel={{ $topStreams[0] }}" frameborder="0" allowfullscreen></iframe>
                </div>
              </div>
            </div>
            <a class="carousel-control-prev" href="#stream-carousel" role="button" data-slide="prev">
              <span class="octicon octicon-chevron-left dark-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#stream-carousel" role="button" data-slide="next">
              <span class="octicon octicon-chevron-right dark-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
    </div>
@stop