<html>
<head>
<title>Pegel</title>
<style type="text/css">
</style>
<script src="//code.jquery.com/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="//cdn.jsdelivr.net/npm/chart.js@^3/dist/chart.min.js" type="text/javascript"></script>	
<script src="https://cdn.jsdelivr.net/npm/moment@^2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@^1"></script>	
</head>
<body>

<canvas id="myChart" width="400" height="200"></canvas>
<pre id="data"></pre>
<script>

const MAX_SIZE = 1000;

const pegelUrl = 'https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/HAMBURG%20ST.%20PAULI/W/measurements.json?start=P15D';
$.getJSON(pegelUrl, function (data) {
	console.log('Count: ' + data.length);
	console.log('First: ' + data[0].timestamp);
	console.log('Last: ' + data[data.length - 1].timestamp);
	const reduceStep = Math.ceil(data.length / MAX_SIZE);
	console.log('ReduceStep: ' + reduceStep);
	const reduced = reduce(data, reduceStep);
	console.log('Reduced Count: ' + reduced.length);
	drawGraph(reduced);
	console.log('First: ' + reduced[0].timestamp);
	console.log('Last: ' + reduced[reduced.length - 1].timestamp);
});

function drawGraph(data) {
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                label: 'Pegel',
                data: data,
                cubicInterpolationMode: 'monotone',
                tension: 0.4,
                borderColor: [ 'red' ]
            }]
        },
        options: {
        	parsing: {
                xAxisKey: 'timestamp',
                yAxisKey: 'value'
            },
            elements: {
                point:{
                    radius: 0
                }
            },
            scales: {
                x: {
                    type: 'timeseries',
                    time: {
                        unit: 'day'
                    }
                }
            }
        }
    });
}

function reduce(data, step) {
	const reduced = [];
	for (let i = 0; i < data.length; i += step) {
		reduced.push(data[i]);
	}
	if (reduced[reduced.length - 1].timestamp != data[data.length - 1].timestamp) {
		reduced.push(data[data.length - 1]);
	}
	return reduced;
}


</script>

</body>
</html>