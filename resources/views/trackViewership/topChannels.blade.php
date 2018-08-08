@extends('layout')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-11">
				<h1>Top Gaming Streams</h1>
			</div>
			<div class="col-md-1">
				<a class="btn btn-primary pull-right" type="button" href="/topStreams" id="refreshTopChannels">Refresh</a>
			</div>
		</div>

		<hr class="featurette-divider">
		
		<div class="row">
			<div class="card-deck">
			@foreach ($data as $stream)
				<div class="col-lg-4 mb-4 d-flex align-items-stretch">
					<div class="card border-info">
						<div class="card-header bg-transparent text-success d-flex justify-content-between align-items-center">
							<strong>
								{{ $stream['viewers'] }}
								<span class="octicon octicon-person"></span>
							</strong>
							<strong>
								{{ $stream['cat'] }}
							</strong>
						</div>
						<img class="card-img-top" src="{{ $stream['logo'] }}">
						<div class="card-body">
							<h5 class="card-title hideOverflow"><a href="{{ $stream['link'] }}">{{ $stream['title'] }}</a></h5>
							<ul class="list-group list-group-flush">
								<li class="list-group-item d-flex justify-content-between align-items-center">
									<a href="{{ $stream['channelLink'] }}">{{ $stream['channel'] }}</a>
								    <span class="badge badge-primary">{{ $stream['platform'] }}</span>
								</li>
							</ul>							
						</div>
					    <div class="card-footer">
							<small>Started at: {{ $stream['creation'] }}</small>
					    </div>
					</div>
				</div>
			@endforeach
			</div>
		</div>
	</div>
@stop