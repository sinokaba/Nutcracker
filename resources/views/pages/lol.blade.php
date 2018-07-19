@extends('layout')

@section('content')
	<script type="text/javascript">
      // Load the Visualization API.
      google.load('visualization', '1', {'packages':['corechart']});
       
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);
       
      function drawChart() {
        /*
        var jsonData = $.ajax({
            url: "getData.php",
            dataType:"json",
            async: false
            }).responseText;
      
        console.log(jsonData);
        */
        var chartData = new google.visualization.DataTable();
        chartData.addColumn('string', 'Time');
        chartData.addColumn('number', 'Total Viewers');
        chartData.addColumn('number', 'Twitch English Viewers');
        chartData.addColumn('number', 'Twitch Foreign Viewers');
        chartData.addColumn('number', 'Youtube Viewers');

        var options = {
          title: "Viewership",
          vAxis: {title: "Live Viewers"},
          hAxis: {title: "Time", textPosition: "none"}
        };

        getData();
        function getData(){
        	//console.log("called");
            var jsonData = $.ajax({
									url: "/getData.php",
									data: {action: 'lcs'},
									type: 'post',
									dataType:"json",
									success: function(res) {
										console.log(typeof(res));
										console.log(res);
										if(typeof(res) == String){
											//$("#curve_chart").html(res);
                      if(chartData.getNumberOfRows <= 0){
                        $("#curve_chart").html(res);                        
                      }
											clearInterval(update);
										}
										else{
											chartData.addRow(res);
											chart.draw(chartData, options);
										}
									},
	                error: function(XMLHttpRequest, textStatus, errorThrown) { 
	                	console.log(XMLHttpRequest.responseText + "\n");
	                    console.log("Status: " + textStatus + "\n" + "Error: " + errorThrown); 
	                }     
	                });
        }        
        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(chartData, options);

        var update = setInterval(getData, 1000 * 120);
      }

    </script>
    <!--Div that will hold the column chart-->
    <div id="curve_chart" style="width: 900px; height: 500px"></div>
@stop