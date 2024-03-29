@section('customCss')
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet" type="text/css">
@stop

@extends('layout')

@section('content')
	<!--collapsable sidebar taken from:
		https://bootstrapious.com/p/bootstrap-sidebar
	-->

    <div class="wrapper">
        <!-- Sidebar Holder -->
        <nav id="sidebar">
            <div class="sidebar-header text-center">
                <h3>{{ $chan->platform === 0 ? 'Twitch' : 'Youtube' }}</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="#"><h4>Status: {{ $chan->offline == true ? 'Offline' : 'Online'}}</h4></a>
                </li>            	
                <li>
                    <a href="#">Rank: {{ $rank }}</a>
                </li>
                <li>
                    <a href="#">Rating</a>
                </li>
                <li>
                    <a href="#" onclick="showStream()">Stream</a>
                </li>                             
                <li>
                    <a href="#past-stream-dates" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Last Streams</a>
                    <ul class="collapse list-unstyled" id="past-stream-dates">
                        @foreach ($streams_rev as $s)                    	
	                        <li>
	                            <a href="#">{{ $s->stream_start }}</a>
	                        </li>
                        @endforeach
                    </ul>
                </li>
                <li>
                    <a href="#">Last Updated: {{ $date }}</a>
                </li>
            </ul>

            <ul class="list-unstyled CTAs">
                <li>
                    <a href="{{ $chan->url }}" class="button-white">Channel Link</a>
                </li>
                <li style="text-align: center; cursor: pointer">
                    <span class="oction octicon-heart" title="Favourite" style="color: red; font-size: 5em"></span>
                </li>
            </ul>
        </nav>

        <!-- Page Content Holder -->
        <div id="content">
        	<div class="p-2 channel-header text-center">
	            <h1 class="text-center" style="margin-bottom: 0px">
					<button type="button" id="sidebarCollapse" class="sidebar-btn">
					    <span></span>
					    <span></span>
					    <span></span>
					</button>
	            	<img src="{{ $chan['data']['logo'] }}" style="border-radius: 50%; width: 2em" alt="channel_logo">
	            	 {{ $chan->channel_name }}
	            </h1>
        	</div>

        	<hr class="feauturette-line">

        	<div id="stream-embed" class="hide" align="center">
                @php
                	if($chan->platform === 0){ 
                		echo "<iframe src='https://player.twitch.tv/?channel=$chan->channel_name&autoplay=false' width='720' height='405' frameborder='0' scrolling='no'></iframe>";
        			}
        			else{
                    	echo "<iframe width='720' height='405' align='center' src='https://www.youtube.com/embed/$chan->channel_id' frameborder='0' allowfullscreen></iframe>";
        			}
        		@endphp
        	</div>

        	<h3>
        		About: <span class="text-muted">{{ $chan['data']['bio'] }}</spansss>
        	</h3>
        	<hr class="feauturette-line">

        	<div id="channel-stats">
				<div class="card-group">
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Creation Date</h5>
				            <p class="card-text">{{ $chan->creation }} </p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">OG</small>
				        </div>
				    </div>
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">{{ $chan->platform === 0 ? 'Followers' : 'Subscribers' }}</h5>
				            <p class="card-text stat-val">{{ $chan['data']['followers'] }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated {{ $date }}</small>
				        </div>
				    </div>
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Total Views</h5>
				            <p class="card-text stat-val">{{ $chan['data']['totalViews'] }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated {{ $date }}</small>
				        </div>
				    </div>
				</div>
				<div class="card-group">
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Peak Viewers</h5>
				            <p class="card-text stat-val">{{ $peak }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated 3 mins ago</small>
				        </div>
				    </div>
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Average Viewers</h5>
				            <p class="card-text stat-val">{{ $avg_viewers }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated 3 mins ago</small>
				        </div>
				    </div>
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Chat Activty</h5>
				            <p class="card-text stat-val">{{ $chat }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated 3 mins ago</small>
				        </div>
				    </div>
				</div>	
				<div class="card-group">
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Average Hours streamed per week</h5>
				            <p class="card-text">{{ $avg_hours }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated 3 mins ago</small>
				        </div>
				    </div>
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Hours streamed this week</h5>
				            <p class="card-text">{{ $hours }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated 3 mins ago</small>
				        </div>
				    </div>
				    <div class="card">
				        <div class="card-body">
				            <h5 class="card-title">Most hours streamed a day</h5>
				            <p class="card-text">{{ $max_hours }}</p>
				        </div>
				        <div class="card-footer">
				            <small class="text-muted">Last updated 3 mins ago</small>
				        </div>
				    </div>
				</div>		
	            <p>Stream Category breakdown</p>
	        </div>

			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
			    <h1 class="h2">Broadcast History</h1>
			    <div class="btn-toolbar mb-2 mb-md-0">
			        <div class="btn-group mr-2">
			            <button class="btn btn-sm btn-outline-secondary">Hide</button>
			            <button class="btn btn-sm btn-outline-secondary">Save</button>
			        </div>
					<select class="form-control" style="width: 50%">
					    <option value="one" default>All Time</option>
					    <option value="two">Day</option>
					    <option value="three">Week</option>
					    <option value="four">Month</option>
					    <option value="five">Year</option>
					</select>
			    </div>
			</div>
			<div class="my-4 w-100" id="broadcast-history" width="100%" height="300"></div>
			<h2>Growth</h2>
			<div class="table-responsive" style="height: 50vh">
			    <table class="table table-striped table-sm">
			        <thead>
			            <tr>
			                <th>Start</th>
			                <th>Duration</th>
			                <th>Views+</th>
			                <th>{{ $chan->platform == 0 ? 'Followers+' : 'Subscribers+' }}</th>
			                <th>Average Viewers</th>
			                <th>Peak Viewers</th>
			                <th>Category</th>
			                <th>Title</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach ($streams as $stream)
				            <tr>
				                <td>{{ $stream->stream_start }}</td>
				                <td>{{ $stream->duration }}</td>
				                <td class="stat-val {{ $stream->views_growth >= 0 ? 'text-success' : 'text-danger' }}">{{ $stream->views_growth }}</td>
				                <td class="stat-val {{ $stream->followers_growth >= 0 ? 'text-success' : 'text-danger' }}">{{ $stream->followers_growth }}</td>
				                <td class="stat-val {{ $stream->avg_growth >= 0 ? 'text-success' : 'text-danger' }}">{{ $stream->avg_viewers }}</td>
				                <td class="stat-val {{ $stream->peak_growth >= 0 ? 'text-success' : 'text-danger' }}">{{ $stream->peak_viewers }}</td>
				                <td>{{ $stream->category }}</td>
				                <td>{{ $stream->title }}</td>
				            </tr>
				        @endforeach
			        </tbody>
			    </table>
			</div>        
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $(this).toggleClass('active');
            });
            //format large nu,bers adding spaces and commas for easier readability
            $('.stat-val').each(function(i, obj){
            	$(this).html($(this).html().toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            })
        });
		google.load("visualization", "1", {
	        "packages": ["corechart"]
	    });	      	
		google.setOnLoadCallback(drawChart);

	    function drawChart() {

			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Date');
			data.addColumn('number', 'Average Viewers');
			data.addColumn('number', 'Peak Viewers');

		    var options = {
		        vAxis: {
		            title: "Viewers"
		        },
		        hAxis: {
		            title: "Date"
		        },
		        legend: "bottom",
		        height: 450
		    };
		    
		    for(var i = {!! $streams !!}.length - 1; i >= 0; i--){
		    	data.addRow([{!! $streams !!}[i]['stream_start'], {!! $streams !!}[i]['avg_viewers'], {!! $streams !!}[i]['peak_viewers']]);
		    }
			
			//console.log({!! $streams !!});

	        var chart = new google.visualization.LineChart(document.getElementById("broadcast-history"));
	        chart.draw(data, options);
		}

		function showStream(){
			$("#stream-embed").toggleClass("hide");
		}
    </script>
@stop