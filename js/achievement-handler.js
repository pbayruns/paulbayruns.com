// JavaScript Document
"use strict()";
var achievementData;
var achievementPercentageData;

var achievementMap;
var achievementPercentageMap;
var achievementNameMap;

var allGameNamesJSON;
var allGamesToAppID;
var gameName;

var defaultGame = "The Elder Scrolls V: Skyrim";

var tempCTX = document.getElementById("achievementChartCanvas").getContext("2d");
var achievementChart = new Chart(tempCTX, {
		type: 'bar',
		data: {
		labels: ['Achievements will be displayed here.'],
		datasets: [{
			label: '',
			borderWidth: 1,
			data: [0],
			backgroundColor: ['#fff']
		}]
	},
		options: {
				xAxes: [{
					ticks: {
						autoSkip: false,
						maxTicksLimit: 20
					}
				}]
			}
	});
tempCTX = null;
var labels;
var values;
var barColors;
var bars;

$('#show-achievements').on('click', function (e) {
	"use strict";
	//your awesome code here
	//var appID = "72850";
	gameName = document.getElementById('query').value;
	var appID = allGamesToAppID[gameName];
	repaint(appID);
	//getachievementdata(appID);
	//buildAchievementStructures();
	//the array of names for the bar graph bins
	//displayGraph(labels, values, barColors);
});

function getachievementdata(appid) {
	"use strict";
	var key = "36DCB86586A035DF3B378C37C5A52D0E";
	var nameURL = "/ISteamUserStats/GetSchemaForGame/v0002/" +
		"?key=" + key + "&appid=" + appid + "&l=english";
	var percentURL = "/ISteamUserStats/GetGlobalAchievementPercentagesForApp/" +
		"v0002/?gameid=" + appid + "&format=json";

	var proxyURL = 'php/steamAPI.php?yws_path=';
	nameURL = proxyURL + encodeURIComponent(nameURL);
	percentURL = proxyURL + encodeURIComponent(percentURL);
	$.ajax({
		url: nameURL,
		type: "GET",
		dataType: "json",
		success: function (data) {
			achievementData = data;
		}
	});

	$.ajax({
		url: percentURL,
		type: "GET",
		dataType: "json",
		success: function (data) {
			achievementPercentageData = data;
		}
	});

}

function buildAchievementStructures() {
	"use strict";
	//create a map where internal achiev. name is the key and percent achieved is the value
	achievementPercentageMap = {};
	//create a map where API name is the key and display name is the value
	achievementNameMap = {};
	achievementMap = {};

	for (var i = 0; i < achievementData.game.availableGameStats.achievements.length; i++) {
		var achievement = achievementData.game.availableGameStats.achievements[i];
		achievementNameMap[achievement.name] = achievement.displayName;
		achievementMap[achievement.displayName] = achievement;
	}

	bars = {};
	labels = [];
	values = [];
	barColors = [];
	for (i = 0; i < achievementPercentageData.achievementpercentages.achievements.length; i++) {
		var nextAchievement = achievementPercentageData.achievementpercentages.achievements[i];
		achievementPercentageMap[nextAchievement.name] = nextAchievement.percent;
		var nextLabel = achievementNameMap[nextAchievement.name];
		var nextPerc = +((nextAchievement.percent).toFixed(1));
		var bar = {
			value: nextPerc,
			label: nextLabel
		};
		bars[i] = bar;
		labels[i] = nextLabel;
		values[i] = nextPerc;
		barColors[i] = '#000';
	}
}

function displayGraph(graphLabels, graphPercs, graphColors) {
	"use strict";
	var barChartData = {
		labels: graphLabels,
		datasets: [{
			bars: bars,
			label: '% of users with achievement',
			borderWidth: 1,
			data: graphPercs,
			backgroundColor: graphColors
		}]
	};
	barChartData = AddBackgroundColors(barChartData);

	var canvas = document.getElementById("achievementChartCanvas");
	var ctx = canvas.getContext("2d");
	//var ctx = canvas.getContext("2d");
	//ctx.clearRect(0, 0, canvas.width, canvas.height);
	achievementChart.destroy();
	achievementChart = new Chart(ctx, {
		type: 'bar',
		data: barChartData,
		options: {
			tooltips: {
				callbacks: {
					label: function (tooltipItem) {
						return "" + Number(tooltipItem.yLabel) + "% of players have this achievement.";
					}
				}
			},
			responsive: true,
			legend: {
				position: 'top',
			},
			title: {
				display: false,
			},
			barPercentage: 1.0,
			categoryPercentage: 1.0,
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}],
				xAxes: [{
					ticks: {
						autoSkip: false,
						maxTicksLimit: 20
					}
				}]
			}
		}
	});
	
	canvas.onmousemove = function(evt) {
    var el = achievementChart.getElementsAtEvent(evt);
		if(el[0] && el[0]._index){
			var currentName = labels[el[0]._index];
			var currAchieve = achievementMap[currentName];
			var percentage = +(achievementPercentageMap[currAchieve.name].toFixed(1));
			var description = currAchieve.description;
			var icon = currAchieve.icon;
			var hidden = currAchieve.hidden;
			displayCurrentAchievement(currentName, description, icon, hidden, percentage);
		}
		
    //do something with the el object to display other information
    //elsewhere on the page
};
	
}

function displayCurrentAchievement(name, desc, icon, hidden, percentage){
	"use strict";
	var achieveInfoBox = document.getElementById("achievement-infobox");
				achieveInfoBox.innerHTML =
					"<img src=" + icon + " alt=achieveIcon " +
					"</img>" + "<br />\n" +
					"<h3>" + name + "</h3>" +
					"<br />\n" +
					"Description: " + desc + "<br />\n" +
					percentage + "% of players who own " + gameName +
					" have gotten this achievement." + "<br />\n";
				if (hidden === '1') {
					achieveInfoBox.innerHTML += "<i>shhh! (This is a hidden achievement)</i>" +
						"<br />\n";
				}
}

function AddBackgroundColors(chartConfig) {
	"use strict";
	var min = 1; // Min value
	var max = 100; // Max value
	var datasets;
	var dataset;
	var value;
	var range = (max - min);
	var percentage;
	var backgroundColor;
	var rainbow = new Rainbow();
	rainbow.setSpectrum('#CFB53B', '#E6E8FA', '#8C7853');
	rainbow.setNumberRange(0, 100);
	//Make sure the data exists
	if (chartConfig &&
		chartConfig.datasets) {
		// Loop through all the datasets
		datasets = chartConfig.datasets;
		for (var i = 0; i < datasets.length; i++) {
			dataset = datasets[i];
			for (var ndx = 0; ndx < dataset.data.length; ndx++) {
				value = dataset.data[ndx];
				percentage = value / range * 100;
				backgroundColor = '#' + rainbow.colourAt(percentage);
				dataset.backgroundColor[ndx] = backgroundColor;
			}
		}
	}
	// Return the chart config object with the new background colors
	return chartConfig;
}

function initializeIDDictionary() {
	"use strict";
	allGamesToAppID = {};
	for (var i = 0; i < allGameNamesJSON.applist.apps.app.length; i++) {
		var nextApp = allGameNamesJSON.applist.apps.app[i];
		allGamesToAppID[nextApp.name] = nextApp.appid; //.set(nextApp.name, nextApp.appid);
	}
	initializeTypeahead();
}

function initializeTypeahead() {
	"use strict";
// Defining the local dataset
	var names = Array.from(Object.keys(allGamesToAppID));
    
    // Constructing the suggestion engine
    var availableNames = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
		limit: 10,
        local: names
    });
    
    // Initializing the typeahead
    $('.typeahead').typeahead({
        hint: true,
        highlight: true, /* Enable substring highlighting */
        minLength: 3 /* Specify minimum characters required for showing suggestions */
    },
    {
        name: 'games',
        source: availableNames
    });
}

function setup() {
	"use strict";
	var allGameNamesURL = "/ISteamApps/GetAppList/v0001/";
	var proxyURL = 'php/steamAPI.php?yws_path=';
	allGameNamesURL = proxyURL + encodeURIComponent(allGameNamesURL);
	gameName = defaultGame;
	setQueryText(defaultGame);
	$.ajax({
		url: allGameNamesURL,
		type: "GET",
		dataType: "json",
		success: function (data) {
			allGameNamesJSON = data;
			initializeIDDictionary();
		}
	});

}

function setQueryText(text){
	"use strict";
	var query = document.getElementById('query');
	query.value = text;
}

function repaint(appidcode) {
	"use strict";
	$(document).ajaxStart(function () {
		$("#loading").show();
	});
	getachievementdata(appidcode);
	$(document).ajaxStop(function () {
		checkAppData();
		buildAchievementStructures();
		displayGraph(labels, values, barColors);
		updateGameNameDisplay(gameName);
		$("#loading").hide();
	});
}

function updateGameNameDisplay(name) {
	"use strict";
	var gamenameheader = document.getElementById("game-name-header");
	gamenameheader.innerHTML = "Achievements for <b>" + name + "</b>";
	console.log(document.getElementById("game-name-header").innerHTML);
}

function checkAppData() {
	"use strict";
	if (typeof achievementData === 'undefined' ||
		typeof achievementPercentageData === 'undefined' ||
		typeof achievementData.game === 'undefined' ||
		typeof achievementData.game.availableGameStats === 'undefined' ||
		typeof achievementData.game.availableGameStats.achievements === 'undefined' ||
		achievementData.game.availableGameStats.achievements.length === 0) {
		alertDefault();
	}
}

function alertDefault() {
	"use strict";
	alert("Is this a soundtrack or app with no achievements? There are " +
		"no achievements available for this game, or there was " +
		"a problem processing this app ID. Try a different app. In the " +
		"meantime, here are the stats for skyrim.");
	gameName = "The Elder Scrolls V: Skyrim";
	//repaint("72850");
}
