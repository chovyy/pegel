<html>
<head>
<title>Pegel</title>
<style type="text/css">
#range {
    margin: 2em;	
}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
<script src="//code.jquery.com/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="//code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
<script src="//cdn.jsdelivr.net/npm/chart.js@^3/dist/chart.min.js" type="text/javascript"></script>	
<script src="https://cdn.jsdelivr.net/npm/moment@^2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@^1"></script>	
</head>
<body>

<canvas id="myChart" width="400" height="200"></canvas>
<div id="range"></div>
<script>

const MAX_SIZE = 1000;
var DATA;
var CHART;

const pegelUrl = 'https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/HAMBURG%20ST.%20PAULI/W/measurements.json?start=P15D';
$.getJSON(pegelUrl, function (data) {
	DATA = data;
		console.log('Count: ' + data.length);
		console.log('First: ' + data[0].timestamp);
		console.log('Last: ' + data[data.length - 1].timestamp);
	const reduced = reduce(data, MAX_SIZE);
		console.log('Reduced Count: ' + reduced.length);
	CHART = drawGraph(reduced);
	initSlider($('#range'), data.length);
});

function drawGraph(data) {
    const ctx = document.getElementById('myChart').getContext('2d');
    const chart = new Chart(ctx, {
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
    return chart;
}

function updateChart(chart, data) {
	chart.data.datasets[0].data = data;
	chart.update();
}

function initSlider($slider, max) {
    $slider.slider({
        range: true,
        min: 0,
        max: max,
        values: [ 0, max],
        stop: function(event, ui) {
            console.log(ui.values[0] + '-' + ui.values[1]);
            const slice = DATA.slice(ui.values[0], ui.values[1] + 1);
            const reduced = reduce(slice, MAX_SIZE);
            updateChart(CHART, reduced);
        }
    });
}

function reduce(data, maxSize) {
	const step = Math.ceil(data.length / maxSize);
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