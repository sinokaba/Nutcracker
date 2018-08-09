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
            <div class="sidebar-header">
                <h3>{{ $chan->platform === 0 ? 'Twitch Streamer' : 'Youtube Streamer' }}</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="#"><h4>Status: Offline</h4></a>
                </li>            	
                <li>
                    <a href="#">Rank: #1</a>
                </li>
                <li>
                    <a href="#">Rating</a>
                </li>
                <li>
                    <a href="#">Stream</a>
                </li>                             
                <li>
                    <a href="#past-stream-dates" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Last Streams</a>
                    <ul class="collapse list-unstyled" id="past-stream-dates">
                        <li>
                            <a href="#">8/7/2018 2:30pm</a>
                        </li>
                        <li>
                            <a href="#">8/5/2018 5:30pm</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">Last Updated: 8/8/2018</a>
                </li>
            </ul>

            <ul class="list-unstyled CTAs">
                <li>
                    <a href="{{ $chan->url }}" class="download">Channel Link</a>
                </li>
                <li>
                    <a href="https://bootstrapious.com/p/bootstrap-sidebar" class="article">Favourite</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content Holder -->
        <div id="content">
        	<div class="p-5 my-1 channel-header text-center">
	            <h1 class="text-center" style="margin-bottom: 0px">
					<button type="button" id="sidebarCollapse" class="sidebar-btn">
					    <span></span>
					    <span></span>
					    <span></span>
					</button>
	            	{{ $chan->channel_name }}
	            </h1>
        	</div>

        	<hr class="feauturette-line">

			<div class="card-group">
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Creation Date</h5>
			            <p class="card-text">{{ $chan->creation }} </p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Followers</h5>
			            <p class="card-text">{{ $chan->followers }}</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Total Views</h5>
			            <p class="card-text">{{ $chan->total_views }}</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			</div>
			<div class="card-group">
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Peak Viewers</h5>
			            <p class="card-text">1000</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Average Viewers</h5>
			            <p class="card-text">2123</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Chat Activty</h5>
			            <p class="card-text">80%</p>
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
			            <p class="card-text">15</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Hours streamed this week</h5>
			            <p class="card-text">10</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			    <div class="card">
			        <div class="card-body">
			            <h5 class="card-title">Most hours streamed a day</h5>
			            <p class="card-text">24}</p>
			        </div>
			        <div class="card-footer">
			            <small class="text-muted">Last updated 3 mins ago</small>
			        </div>
			    </div>
			</div>		
            <p>Stream Category breakdown</p>

			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
			    <h1 class="h2">Broadcast History</h1>
			    <div class="btn-toolbar mb-2 mb-md-0">
			        <div class="btn-group mr-2">
			            <button class="btn btn-sm btn-outline-secondary">Share</button>
			            <button class="btn btn-sm btn-outline-secondary">Export</button>
			        </div>
			        <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
			        <span data-feather="calendar"></span>
			        This week
			        </button>
			    </div>
			</div>
			<div class="my-4 w-100" id="broadcast-history" width="900" height="300"></div>
			<h2>Growth</h2>
			<div class="table-responsive">
			    <table class="table table-striped table-sm">
			        <thead>
			            <tr>
			                <th>Date</th>
			                <th>Views</th>
			                <th>Followers</th>
			                <th>Average Viewers</th>
			                <th>Peak Viewers</th>
			                <th>Category</th>
			            </tr>
			        </thead>
			        <tbody>
			            <tr>
			                <td>1,001</td>
			                <td>Lorem</td>
			                <td>ipsum</td>
			                <td>dolor</td>
			                <td>sit</td>
			                <td>idk</td>
			            </tr>
			            <tr>
			                <td>1,002</td>
			                <td>amet</td>
			                <td>consectetur</td>
			                <td>adipiscing</td>
			                <td>elit</td>
			                <td>idk</td>
			            </tr>
			            <tr>
			                <td>1,003</td>
			                <td>Integer</td>
			                <td>nec</td>
			                <td>odio</td>
			                <td>Praesent</td>
			                <td>idk</td>
			            </tr>
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
		    
		    for(var i = 0; i < {!! $streams !!}.length; i++){
		    	data.addRow([{!! $streams !!}[i]['stream_start'], {!! $streams !!}[i]['avg_viewers'], {!! $streams !!}[i]['peak_viewers']]);
		    }
			
			//console.log({!! $streams !!});

	        var chart = new google.visualization.LineChart(document.getElementById("broadcast-history"));
	        chart.draw(data, options);
		}
    </script>
@stop