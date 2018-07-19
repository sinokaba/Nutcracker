<nav class="navbar navbar-expand-md navbar-dark mb-4" style="background-color: #17252A">
  <a class="navbar-brand" href="/">
    <img src="{{ asset('imgs/logo.png') }}" alt="Nutcracker.png" height="75" width="75">
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
        <a class="nav-link" href="/trackViewership">Track Viewership</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/esportsViewers">Esports Viewership</a>
      </li>
      <!--
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Esports Viewership
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="{{ url('esportsViewers/lol') }}">LOL</a>
          <a class="dropdown-item" href="{{ url('esportsViewers/csgo') }}">CSGO</a>
          <a class="dropdown-item" href="{{ url('esportsViewers/ow') }}">Overwatch</a>
          <a class="dropdown-item" href="{{ url('esportsViewers/dota2') }}">Dota2</a>
          <a class="dropdown-item" href="{{ url('esportsViewers/fortnite') }}">Fortnite</a>
        </div>
      </li>
    -->
      <li class="nav-item">
        <a class="nav-link" href="{{ url('/about') }}">About</a>
      </li>
    </ul>
  </div>
</nav>