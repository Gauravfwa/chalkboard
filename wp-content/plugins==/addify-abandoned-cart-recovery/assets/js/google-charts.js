jQuery(document).ready(function(){
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChartAmount);
	google.charts.setOnLoadCallback(drawChartQuantity);
	google.charts.setOnLoadCallback(drawChart);

	// Draw the chart and set the chart values
	function drawChartAmount() {
		var data = google.visualization.arrayToDataTable([
			['Total', 'Abandoned Carts Amount'],
			['Recovered', parseFloat( afacr_var.total_recovered_amount ) ],
			['Abandoned', parseFloat( afacr_var.total_abandoned_amount )],
		]);

		// Optional; add a title and set the width and height of the chart
		var options = {
			'title':'Abandoned Carts Amount', 
			'width':'100%', 
			'height':'300', 
			is3D: false,
		};

		console.log( data );

		// Display the chart inside the <div> element with id="piechart"
		if ( document.getElementById('piechart-amount') ) {
			var chart = new google.visualization.PieChart(document.getElementById('piechart-amount'));
			chart.draw(data, options);
		}
	  
	}
	 // Draw the chart and set the chart values
	function drawChartQuantity() {
		var data = google.visualization.arrayToDataTable([
		  ['Total', 'Abandoned Carts Quantity'],
		  ['Recovered', parseFloat( afacr_var.total_recovered_carts )],
		  ['Abandoned', parseFloat( afacr_var.total_abandoned_carts )],
		]);

		// Optional; add a title and set the width and height of the chart
		var options = {
			'title':'Abandoned Carts Quantity', 
			'width':'100%', 
			'height':'300', 
			is3D: false,
			
		};

		// Display the chart inside the <div> element with id="piechart"
		if ( document.getElementById('piechart-quantity') ) {
			var chart = new google.visualization.PieChart(document.getElementById('piechart-quantity'));
			chart.draw(data, options);
		}
	}

	function drawChart() {

		var data = google.visualization.arrayToDataTable( JSON.parse( afacr_var.monthly_report ) );

		var options = {
			title: 'Abandoned Cart Recovery Performance',
			'width':'100%', 
			'height':'400',
			curveType: 'function',
			legend: { position: 'bottom' }
		};

		// Display the chart inside the <div> element with id="piechart"
		if ( document.getElementById('curve_chart') && JSON.parse( afacr_var.monthly_report ).length > 1 ) {
			var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
			chart.draw(data, options);
		}
		
	}
});
