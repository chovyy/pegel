const PEGEL_URL = 'https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/HAMBURG%20ST.%20PAULI/W/measurements.json';
const TIME_FORMAT = 'DD.MM.YYYY HH:mm';
const DEFAULT_STEP = 3;
const RANGE_MIN = 0;
var RANGE_MAX;
var RANGE_START;
var RANGE_END;
var STEP_SIZE;
var DATA;

$(document).ready(function () {
	$.getJSON(PEGEL_URL, function (data) {
		DATA = data;
		$('#total').text(data.length);
		STEP_SIZE = switchSteps();
		RANGE_MAX = data.length - 1;
		RANGE_START = getRangeStart(RANGE_MIN, RANGE_MAX);
		RANGE_END = getRangeEnd(RANGE_START + 1, RANGE_MAX);
		const reduced = reduce(data, STEP_SIZE, RANGE_START, RANGE_END);
		initSlider($('#slider'));
		updateSliderLabels(reduced);
		updateCount(reduced);
		drawChart(reduced);
	});
	
	$('input[name=step]').change(function () {
		const step = parseInt($(this).val());
		const url = new URL(window.location.href);
		url.searchParams.set('step', step);
		url.searchParams.set('rangeStart', RANGE_START);
		url.searchParams.set('rangeEnd', RANGE_END);
		window.location.href = url.href;
	});
});
window.onpopstate = function() {
	location.reload();
}

function switchSteps() {
	const options = $('#steps input').map(function () { return $(this).val() }).get();
	const url = new URL(window.location.href);
	var step = url.searchParams.get('step');
	if (!step || !options.includes(step)) {
		step = DEFAULT_STEP;
	}
	$('#' + step + 'min').attr('checked', 'checked');
	return parseInt($('input[name=step]:checked').val());
}

function getRangeStart(min, max) {
	const url = new URL(window.location.href);
	var rangeStart = url.searchParams.get('rangeStart');
	if ($.isNumeric(rangeStart)) {
		return Math.max(min, Math.min(rangeStart, max));
	}
	return min;
}

function getRangeEnd(min, max) {
	const url = new URL(window.location.href);
	var rangeEnd = url.searchParams.get('rangeEnd');
	if ($.isNumeric(rangeEnd)) {
		return Math.max(min, Math.min(rangeEnd, max));
	}
	return max;
}

function initSlider($slider) {
    $slider.slider({
        range: true,
        min: RANGE_MIN,
        max: RANGE_MAX,
        values: [ RANGE_START, RANGE_END ],
        stop: function(event, ui) {
            RANGE_START = ui.values[0];
            RANGE_END = ui.values[1];
            updateAll();
        }
    });
}

function updateAll() {
    const reduced = reduce(DATA, STEP_SIZE, RANGE_START, RANGE_END);
    updateCount(reduced);
    updateChart(reduced);
    updateSliderLabels(reduced);
    updateUrl(STEP_SIZE, RANGE_START, RANGE_END);
}

function updateCount(data) {
	$('#count').text(data.length);
}

function updateSliderLabels(data) {
	$('#first').text(formatDate(data[0].timestamp));
	$('#last').text(formatDate(data[data.length - 1].timestamp));
}

function updateUrl(step, rangeStart, rangeEnd) {
	const url = new URL(window.location.href);
	url.searchParams.set('step', step);
	url.searchParams.set('rangeStart', rangeStart);
	url.searchParams.set('rangeEnd', rangeEnd);
	history.pushState(null, null, url.href);
}

function formatDate(timestamp) {
	const date = new Date(timestamp);
	return moment(date).format('DD.MM.YYYY HH:mm');
}

function reduce(data, step, rangeStart, rangeEnd) {
    const slice = DATA.slice(rangeStart, rangeEnd + 1);
	if (step > 1 && step < slice.length) {
        const reduced = [];
        for (let i = 0; i < slice.length; i += step) {
        	reduced.push(slice[i]);
        }
        if (reduced[reduced.length - 1].timestamp != slice[slice.length - 1].timestamp) {
        	reduced.push(slice[slice.length - 1]);
        }
        return reduced;
	}
	return slice;
}