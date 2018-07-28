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
          <div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
            <ol class="carousel-indicators">
              <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
              <li data-target="#myCarousel" data-slide-to="1"></li>
              <li data-target="#myCarousel" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
              <div class="carousel-item active">
                    <!-- Add a placeholder for the Twitch embed -->
                    <div id="twitch-embed-1" align='center'></div>

                    <!-- Load the Twitch embed script -->
                    <script src="https://embed.twitch.tv/embed/v1.js"></script>

                    <!-- Create a Twitch.Embed object that will render within the "twitch-embed" root element. -->
                    <script type="text/javascript">
                        new Twitch.Embed("twitch-embed-1", {
                            width: 800,
                            height: 450,
                            channel: "{{ $topStreams[2] }}"
                        });
                    </script>        
              </div>
              <div class="carousel-item">
                    <!-- Add a placeholder for the Twitch embed -->
                    <div id="twitch-embed-2" align='center'></div>

                    <!-- Load the Twitch embed script -->
                    <script src="https://embed.twitch.tv/embed/v1.js"></script>

                    <!-- Create a Twitch.Embed object that will render within the "twitch-embed" root element. -->
                    <script type="text/javascript">
                        new Twitch.Embed("twitch-embed-2", {
                            width: 800,
                            height: 450,
                            channel: "{{ $topStreams[3] }}"
                        });
                    </script>
              </div>
              <div class="carousel-item">
                <iframe width="800" height="450" align='center' src="https://www.youtube.com/embed/live_stream?channel={{ $topStreams[0] }}" frameborder="0" allowfullscreen></iframe>
              </div>
            </div>
            <a class="carousel-control-prev" href="#myCarousel" style="background-color: black" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#myCarousel" style="background-color: black" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
    </div>
@stop