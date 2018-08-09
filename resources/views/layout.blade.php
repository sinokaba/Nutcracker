<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--Load the AJAX API-->
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        @yield('customJs')
        
        <link rel="icon" type="img/ico" href="{{ asset('imgs/logo.png') }}">

        <title>{{config('app.name', 'Nutcracker')}}</title>
        
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <!--icons github-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/font/octicons.min.css">
        <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" rel="stylesheet"/>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/main.css') }}" rel="stylesheet" type="text/css">
        @yield('customCss')
    </head>
    <body>
        <main role="main">
            @include('components.navbar')
            @yield('content')

            <div class="line"></div>
            <!-- FOOTER -->
            <footer class="container" style=>
                <p class="float-right"><a href="#">Back to top</a></p>
                <p>&copy; Nutcracker &middot; <a href="#">Privacy</a> &middot; <a href="#">Terms</a></p>
            </footer>
        </main>

        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript">
            $(function(){
                $("#search-channel").autocomplete({
                    source: '{!!URL::route('autocomplete')!!}',
                    minlength: 1,
                    autoFocus: true
                });
            })
        </script>

        @yield('addScript')
    </body>
</html>