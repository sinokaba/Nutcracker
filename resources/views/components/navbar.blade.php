<nav class="navbar navbar-expand-md navbar-dark mb-4" style="background-color: #17252A">
    <a class="navbar-brand" href="/">
      <img src="{{ asset('imgs/logo.png') }}" alt="Nutcracker.png" style="height:4em; width:4em">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item {{ Request::segment(1) == '' ? 'active' : null }}">
                <a class="nav-link" href="/">Home<span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item {{ Request::segment(1) === 'trackViewership' ? 'active' : null }}">
                <a class="nav-link" href="/trackViewership">Live Viewership Tracker</a>
            </li>
            <li class="nav-item {{ Request::segment(1) === 'topStreams' ? 'active' : null }}">
                <a class="nav-link" href="/topStreams">Live Now</a>
            </li>
            <li class="nav-item {{ Request::segment(1) === 'about' ? 'active' : null }}">
                <a class="nav-link" href="{{ url('/about') }}">About</a>
            </li>
        </ul>
        <form class="form-inline mt-2 mt-md-0" action="{{ URL::route('channelSearch') }}" method="GET">
            <input class="form-control mr-sm-2" type="text" name="search-term" id="search-channel" placeholder="Channel" aria-label="Search">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button class="my-btn btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
    </div>
</nav>

