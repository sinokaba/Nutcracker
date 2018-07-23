<nav class="navbar navbar-expand-md navbar-dark mb-4" style="background-color: #17252A">
  <a class="navbar-brand" href="/">
    <img src="{{ asset('imgs/logo.png') }}" alt="Nutcracker.png" style="height:4em; width:4em">
    Nutcracker
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarCollapse">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/trackViewership">Live Viewership Tracker</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/esportsViewers">Esports Viewership</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ url('/about') }}">About</a>
      </li>
    </ul>
    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Login</button>
  </div>
</nav>