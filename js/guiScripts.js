function applyFilter() {
	var building = $('#buildingFilter').selectpicker('val');
	var floor = $('#floorFilter').selectpicker('val');
	var gender = $('#genderFilter').selectpicker('val');
	var overallRating = $('#overallRatingFilter').selectpicker('val');
	var cleanlinessRating = $('#cleanlinessFilter').selectpicker('val');
	var crowdednessRating = $('#crowdednessFilter').selectpicker('val'); //TODO finish db query
	
	
	if(gender !== null && gender !== undefined){
        for(var i = 0; i < gender.length; i++){
                if(gender[i] === "Male"){
                        gender[i] = 'M';
                }else if(gender[i] === "Female"){
                        gender[i] = 'F';
                }else if(gender[i] === "Gender Neutral"){
                        gender[i] = 'N';
                }
        }
	}if(overallRating !== null && overallRating !== undefined){
        for(var i = 0; i < overallRating.length; i++){
                if(overallRating[i] === "Very High"){
                        overallRating[i] = 5;
                }else if(overallRating[i] === "High"){
                        overallRating[i] = 4;
                }else if(overallRating[i] === "Average"){
                        overallRating[i] = 3;
                }else if(overallRating[i] === "Low"){
                        overallRating[i] = 2;
                }else if(overallRating[i] === "I REALLY HAVE TO GO"){
                        overallRating[i] = 1;
                }
        }
	}if(cleanlinessRating !== null && cleanlinessRating !== undefined){
        for(var i = 0; i < cleanlinessRating.length; i++){
                if(cleanlinessRating[i] === "Spotless"){
                        cleanlinessRating[i] = 5;
                }else if(cleanlinessRating[i] === "Clean"){
                        cleanlinessRating[i] = 4;
                }else if(cleanlinessRating[i] === "Average"){
                        cleanlinessRating[i] = 3;
                }else if(cleanlinessRating[i] === "Not Very Clean"){
                        cleanlinessRating[i] = 2;
                }else if(cleanlinessRating[i] === "Disgusting"){
                        cleanlinessRating[i] = 1;
                }
        }
	}
	if(crowdednessRating !== null && crowdednessRating !== undefined){
	for(var i = 0; i < crowdednessRating.length; i++){
                if(crowdednessRating[i] === "Very Empty"){
                        crowdednessRating[i] = 5;
                }else if(crowdednessRating[i] === "Slightly Empty"){
                        crowdednessRating[i] = 4;
                }else if(crowdednessRating[i] === "Average"){
                        crowdednessRating[i] = 3;
                }else if(crowdednessRating[i] === "Slightly Crowded"){
                        crowdednessRating[i] = 2;
                }else if(crowdednessRating[i] === "Very Crowded"){
                        crowdednessRating[i] = 1;
                }
        }
	}
	console.log('starting ajax call');
	$.ajax({
                   url: "php/bathroomfinder-backend.php",
                   async: false,
                   type: "GET",
                   data: {fn: 'getAllBathroomsPinInfoFiltered',
                          building: building,
                          floor: floor,
                          gender: gender,
                          overallRating: overallRating,
                          cleanlinessRating: cleanlinessRating,
                          crowdednessRating: crowdednessRating
                        },
                   dataType: "json",
                   success: function(data){
                        setMapData(data);
					   	refreshMap();
                 }
        });
	console.log('ajax call finished \n');
}

$('#disclaimerModal').modal({
	show: true,
	backdrop: 'static',
	keyboard: false
});

$('#clearFilters').on('click', function (e) {
	$('.selectpicker').selectpicker('deselectAll');
});

function displayBR(brID) {
	var elems = document.querySelectorAll(".bathroom-clickable");
	[].forEach.call(elems, function (el) {
		el.classList.remove("active");
	});
	document.getElementById('BR' + brID).classList.add('active');
	$.ajax({
		url: "php/bathroomfinder-backend.php",
		async: false,
		type: "GET",
		data: {
			fn: 'getAllComments',
			id: brID
		},
		dataType: "JSON",
		success: function (data) {
			document.getElementById('commentCount').innerHTML = data[0];
			document.getElementById('bathroom-comments').innerHTML = data[1];
		}
	});
	$.ajax({
		url: "php/bathroomfinder-backend.php",
		async: true,
		type: "GET",
		data: {
			fn: 'getBathroomHeaderInfo',
			id: brID
		},
		dataType: "JSON",
		success: function (data) {
			$('#activeBathroomID').text(data[0]);
			$('#activeBathroomTitle').text(data[1]);
			$('#activeBathroomLocation').text(data[2]);
			$('#overallStars').val(parseInt(data[3]));
			$('#cleanStars').val(parseInt(data[4]));
			$('#crowdedStars').val(parseInt(data[5]));

			$('.average-rating').rating('refresh', {
				readonly: true,
				showClear: false,
				showCaption: false,
				size: 'xs'
			});
			$('#bathroomInfoModal').modal('show');
		}
	});
};

function initializeTable() {
	$.ajax({
		url: "php/bathroomfinder-backend.php",
		async: true,
		type: "GET",
		data: {
			fn: 'getBathroomTable'
		},
		dataType: "text",
		success: function (data) {
			document.getElementById('bathroomsTable').innerHTML = data;
			$("#bathroomsTable").tablesorter(); 
		}
	});

}

function onSignIn(googleUser) {
	var profile = googleUser.getBasicProfile();

	console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
	console.log('Name: ' + profile.getName());
	console.log('Image URL: ' + profile.getImageUrl());
	console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
	$.ajax({
		url: "php/bathroomfinder-backend.php",
		async: true,
		type: "GET",
		data: {
			fn: 'getCommentEntry',
			userID: profile.getId()
		},
		dataType: "JSON",
		success: function (data) {
			$('#comment-entry').innerHTML = data;
		}
	});
}
