<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--Load the AJAX API-->
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
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

            <!-- FOOTER -->
            <footer class="footer-bs">
                <div class="row">
                    <div class="col-md-3 footer-brand animated fadeInLeft text-center">
                        <h2>Nutcracker</h2>
                        <p>© 2018 SNKB, All rights reserved</p>
                    </div>
                    <div class="col-md-7 footer-nav animated fadeInUp">
                        <div class="row justify-content-center">
                            <ul class="pages list-inline">
                                <li class="list-inline-item"><a href="#">About Us</a></li>
                                <li class="list-inline-item"><a href="#">Contacts</a></li>
                                <li class="list-inline-item"><a href="#">Terms & Condition</a></li>
                                <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2 footer-nav animated fadeInDown">
                        <div class="text-center">
                            <img src="{{ asset('imgs/logo.png') }}" alt="nutcracker_logo.png" style="width: 5em">
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript">
            $(function(){
                $("#search-channel").autocomplete({
                    source: '/autocomplete',
                    minlength: 2,
                    autoFocus: true
                });
            })
        </script>

        @yield('addScript')
    </body>
</html>