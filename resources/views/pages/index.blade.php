@extends('layout')

@section('customCss')
    <link href="{{ URL::asset('/css/welcome.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="flex-center position-ref full-height">
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

        <div class="content">
            <div class="title m-b-md">
                Nutcracker
            </div>
            <div class="subtitle m-b-md">
                Viewership statistics for the top esports titles.
            </div>

            <div class="esportsTitles">
                <a href="{{ url('/esportsViewers/lol') }}">League of Legends</a>
                <a href="{{ url('/esportsViewers/csgo') }}">CS:GO</a>
                <a href="{{ url('/esportsViewers/ow') }}">Overwatch</a>
                <a href="{{ url('/esportsViewers/dota2') }}">Dota 2</a>
            </div>
        </div>
    </div>
@stop