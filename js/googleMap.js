jQuery(function ($) {
	// Asynchronously Load the map API 
	var script = document.createElement('script');
	script.src = "https://maps.googleapis.com/maps/api/js?callback=initialize&key=AIzaSyC-d9O9RHwzX8QBL6TN7UX-A-KPZrj6l7A&libraries=places";
	document.body.appendChild(script);
});


//directory for the pin icons, and alternate images
//to display when they are in different active states.
var iconsDir = "http://elvis.rowan.edu/~bayrunsp9/Images/";
var defaultIcon = iconsDir + "pin_default.png";
var clickedIcon = iconsDir + "pin_clicked.png";
var mouseoverIcon = iconsDir + "pin_mouseover.png";

var pinsGPSData = []; //an array that stores the GPS data for each pin
var infoWindowHTML = []; //an array that stores the info window html for each pin

function setMapData(allPinInfo){
	pinsGPSData = []; 
	infoWindowHTML = []; 
	//grab the JSON that contains the GPS and name info for the pins
	var pinsJSON = $.map(allPinInfo[0], function (el) {
		return el;
	});
	
	console.log(pinsJSON);
	
	//grab the JSON that contains the info window inner html for the pins
	var infoWindowContentJSON = $.map(allPinInfo[1], function (el) {
		return el;
	});
	
	var i = 0;
    var pinNdx = 0;
	
	//loop through the JSON of pin name and pin GPS data
	while (i < pinsJSON.length) {
		//the data is stored in groups of 3's
		//as name, lat, long
		//so, every 3rd index:
		if ((i % 3) === 2) {
			//each google maps pin is an array of 
			//[name, latitude, longitude]
			pinsGPSData[pinNdx] = [
				pinsJSON[i - 2], //the pin name is 2 indices back
				parseFloat(pinsJSON[i - 1]), //the pin latitude is 1 index back
				parseFloat(pinsJSON[i]) //the pin longitude is the current index
			];
			pinNdx++; //ready for next pin
		}
		i++;
	}
	
	//save the info window inner HTML into an array
	i = 0;
	while (i < infoWindowContentJSON.length) {
		infoWindowHTML[i] = [infoWindowContentJSON[i]];
		i++;
	}
}

function initialize(){

	//make an ajax call to get all of the pin info.
	//returns a JSON where:
	// data[0] = all of the GPS and name info for the pins
	// data[1] = the popup info window inner HTML for the pins
	$.ajax({
		url: "php/bathroomfinder-backend.php",
		async: false,
		type: "GET",
		data: {
			fn: 'allPinInfo'
		},
		dataType: "json",
		success: function (data) {
			//save all the pin info
			setMapData(data);
			refreshMap();
		}
	});
}

function refreshMap() {

	var map = null; //variable for the google map
	var pinObjs = []; //array for all of the pins for the map,
					  //in google maps pin object form.
	//instantiate the bounds for the google map object
	var bounds = new google.maps.LatLngBounds();
	
	//set the options for the map
	var mapOptions = {
		mapTypeId: 'roadmap',
		zoom: 16
	};

	// Instantiate the map with the options set above, and display it
	//in the HTML element map_canvas
	map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
	
	
	//array to track what pin is clicked.
	//This is needed because google maps pin objects 
	//do not store their state (either active/clicked or inactive/not clicked)
	var pinsClicked = [];
    

	//instantiate the info window for the map.
	//(it is one object that appears above a pin when you click it.
	//the same window is used for all pins, and is just closed/moved
	//and has its inner HTML changed based on which pin is clicked)
	var infoWindow = new google.maps.InfoWindow();
	var i;

	//event listener for clicking the map
	//when the background is clicked, loop through and set 
	//all pins back to the default "unclicked" icon
	google.maps.event.addListener(map, "click", function (event) {
		for (var i = 0; i < pinsClicked.length; i++) {
			pinsClicked[i] = false;
			pinObjs[i].setIcon(defaultIcon);
		}
		infoWindow.close();
	});
	
	//event listener for closing a pin infowindow
	//when a pin info window is closed, loop through
	//and set all pins back to the default "unclicked" icon
	google.maps.event.addListener(infoWindow, 'closeclick', function () {
		for (var i = 0; i < pinsClicked.length; i++) {
			pinsClicked[i] = false;
			pinObjs[i].setIcon(defaultIcon);
		}
	});
	
	var pin; //for temporarily storing the current pin in the loop.
	//Loop through our array of pin data & create a google maps pin from it.
	//Then place that google maps pin on the map. 
	for (i = 0; i < pinsGPSData.length; i++) {
		//store the latitude and longitude for this pin in a position object
		var position = new google.maps.LatLng(pinsGPSData[i][1], pinsGPSData[i][2]);
		bounds.extend(position); //extend the bounds of the map to fit the new pin
		//create a new pin with the default icon
		//grab its title from its data in the pin array
		pin = new google.maps.Marker({
			position: position,
			map: map,
			icon: "http://elvis.rowan.edu/~bayrunsp9/Images/pin_default.png",
			title: pinsGPSData[i][0]
		});
		pinObjs.push(pin); //add this pin to the list of all pins
		pinsClicked.push(false); //track this pin as unclicked in the click tracking array
	}
	
	//Loop through the array of google maps pin objects and set event listeners
	for (var index = 0; index < pinObjs.length; index++) {
		//the current pin we're adding listeners to
		var currPin = pinObjs[index];
		
		//event listener for clicking map pins
		//when an pin is clicked, display its corresponding info window.
		google.maps.event.addListener(currPin, 'click', (function (currPin, index) {
			return function () {
					//close the active infowindow
					infoWindow.close();
					//mark that this pin has been clicked in the tracking array
					pinsClicked[index] = true;
					//change this pin's icon to the clicked icon
					currPin.setIcon(clickedIcon);
					//set the content of the info window to the
					//HTML that goes along with this pin
					infoWindow.setContent(infoWindowHTML[index][0]);
					//open the infowindow on the map at this pin
					infoWindow.open(map, currPin);
            }
		})(currPin, index));
		
		//event listener for mousing over map pins
		//when an icon is moused over, set its icon 
		//to the mouseover icon.
		google.maps.event.addListener(currPin, 'mouseover', (function (currPin, index) {
			return function () {
				currPin.setIcon(mouseoverIcon);
			};
		})(currPin, index));
		
		//event listener for mousing out of map pins
		//when the mouse leaves a map pin, 
		//change its icon to default if it's NOT the active "clicked" pin
		//or change its icon to clicked if it IS the active "clicked" pin
		google.maps.event.addListener(currPin, 'mouseout', (function (currPin, index) {
			return function () {
				if (!pinsClicked[index]) { //if this pin isn't the clicked pin
					currPin.setIcon(defaultIcon); //set its icon to default
				} else { //it is the clicked pin
					currPin.setIcon(clickedIcon); //set its icon to clicked
				}
			};
		})(currPin, index));

		// Automatically center the map fitting all pins on the screen
		map.fitBounds(bounds);
	}

	// Override our map zoom level once our fitBounds function runs (Make sure it only runs once)
	var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function (event) {
		this.setZoom(16);
		google.maps.event.removeListener(boundsListener);
	});
}
