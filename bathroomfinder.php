<!doctype html>
<html>

<head>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-109354643-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-109354643-1');
</script>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="google-signin-client_id" content="166824050890-c69fisap792n0cqos0rsdicad9sff5k6.apps.googleusercontent.com">
	<title>Bathroomfinder</title>
	<link href="./img/my head.svg" rel="shortcut icon"/>
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<link href="libraries/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css">
	<script src="libraries/js/jquery-1.11.3.min.js"></script>
	<script src="libraries/js/bootstrap.js"></script>
	<script src="libraries/js/jquery.waypoints.min.js"></script>
	<link href="https://cdn.rawgit.com/michalsnik/aos/2.1.1/dist/aos.css" rel="stylesheet">
	<script src="https://cdn.rawgit.com/michalsnik/aos/2.1.1/dist/aos.js"></script>
	<script src="js/scrolling.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
	<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"/>
	<!-- Table Sorter Library -->
	<!--<script src="/~bayrunsp9/boostrap_plugins/tablesorter.min.js" type="text/javascript"></script>-->
	<script type="text/javascript" src="/libraries/js/jquery.tablesorter.min.js"></script>
	<script src="https://apis.google.com/js/platform.js" async defer></script>
	<script src="js/bathroomfinder.js"></script>
	<link href="css/styles.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div id="bathroomfinder-wrapper">
	<!--Full site nav -->
<nav class="navbar navbar-custom navbar-fixed-top">
    <div class="container col-sm-12 col-md-8 col-md-offset-2">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
        <a class="navbar-brand" href="/">Paul Bayruns</a> </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav navbar-right">
          <li><a href="./index">ABOUT ME</a></li>
          <li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#">PROJECTS <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li class="active"><a class="active">BATHROOM FINDER</a></li>
              <li><a href="achievement-viewer">ACHIEVEMENT VIEWER</a></li>
              <li><a href="dropzone-HQ">DROPZONE HQ</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
<!--End nav -->
	<!-- Disclaimer modal -->
	<div id="disclaimerModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title">Bathroomfinder is not officially affiliated with Rowan University.</h3>
				</div>
				<div class="modal-body">
					<p style="color:#000">We take no responsibility for any damages that occur to you as a result of using our application. This site is provided &quot;as is&quot; and we provide no guarantee or warranty for its services.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-lg btn-primary col-xs-10 col-xs-offset-1" data-dismiss="modal"> <span class="glyphicon glyphicon-ok-sign pull-left"></span>Accept </button>
				</div>
			</div>
		</div>
	</div>

	<!--Bathroom info modal-->
	<div id="bathroomInfoModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title" id="bathroom-info-header">Bathroom</h3>
					<p id="commentCount"></p>
				</div>
				<div class="modal-body" id="bathroom-info-body">
					<div class="g-signin2" data-onsuccess="onSignIn"></div>
					<p style="color:#000">Sign in with google to leave a comment.</p>
				</div>
				<div class="modal-footer" id="bathroom-comments">

				</div>
			</div>
		</div>
	</div>
	<!-- Login error modal -->
	<div id="loginErrorModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="loginErrorModalLabel">Only google accounts on the Rowan University google domain can access full site functionality.</h3>
				</div>
				<div class="modal-body">
					<p style="color:#000">Since BathroomFinder is only on Rowan's campus, you need a .rowan.edu email address tied to your google account to leave a comment and rating. You can still view all bathrooms, though! Since the sign-in authenticates with google, you'll need to sign out with google, or run this in an incognito window (Ctrl-Shift-N on Google Chrome, or Ctrl-Shift-P on Mozilla Firefox) to try a valid rowan login.</p>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Dismiss</button>
				</div>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row text-center" id="mapDisplay">
			<div class="col-xs-10 col-xs-offset-1">
				<h1 id="bathroomfinder-title">Rowan University Bathroom Finder</h1>
				<h4>Click a mapmarker, then click a bathroom from the popup list to view its info.</h4>
			</div>
			<div class="row">
			<div class="col-xs-8 col-xs-offset-2" id="map_wrapper">
				<div id="map_canvas" class="mapping"></div>
			</div>
			</div>
			<div class ="row" id="mapViewToggle">
					<button id="refreshMap" type="button" onClick="initialize()" class="btn btn-lg btn-transparent map-button"> <span class="v-centered glyphicon glyphicon-refresh"></span>Refresh Map (clears selection)</button>
				</div>
		</div>
		<div class="row text-center" id="filterOptions">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">
				<h3>Filter Bathrooms</h3>
				<div id="filter-buttons">
				<button id="filterToggle" type="button" onClick="applyFilter()" class="btn btn-transparent filter-button"> <span class="v-centered glyphicon glyphicon-cog"></span>Apply Filters</button>
				<button id="clearFilters" type="button" onClick="clearFilter()" class="btn btn-transparent filter-button"> <span class="v-centered glyphicon glyphicon-remove-sign"></span>Clear Filters</button>
				</div>
			</div>
			<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
			<form>
			  <div class="input-group">
					<span class="input-group-addon">Building</span>
				<select class="selectpicker filterPicker" data-actions-box="true" data-width="auto" data-size="5" data-selected-text-format="count > 2" data-live-search="true" id="buildingFilter" multiple>
					<option data-tokens="Robinson Hall, Hall, Robinson, Computer Science, CS Lab, Math, Geography">Robinson Hall</option>
					<option data-tokens="Westby Hall, Westby, Hall, Art, Gallery">Westby Hall</option>
					<option data-tokens="Campbell Library, Campbell, Library">Campbell Library</option>
					<option data-tokens="Chamberlain Student Center, Chamberlain, Student Center, Profs, Pool, Billiards Profs Place, Pit, The Pit, Owl's Nest, Owls Nest">Chamberlain Student Center</option>
					<option data-tokens="Wilson Hall, Wilson, Hall, Music, Pfleger, Concert, Auditorium, Play, Musical">Wilson Hall</option>
					<option data-tokens="Business Building, Business Hall, Hall, Rohrer College of Business, Rohrer, College of Business, Business, Building, New Construction">Business Building</option>
					<option data-tokens="Science Building, Science, Building, Hall, Science Hall, Physics, Bio, Biology, Chem, Chemistry">Science Building</option>
					<option data-tokens="Savitz Hall, Savitz, Hall, Building, Bursar Office, Bursar, Housing, Housing Office">Savitz Building</option>
					<option data-tokens="Winans Hall, Winans, Hall, Wellness Center, Wellness, Center">Winans Hall</option>
					<option data-tokens="Memorial Hall, Memorial, Hall, IT, IR">Memorial Hall</option>
					<option data-tokens="Bunce Hall, Bunce, Hall, Old, Green, Auditorium, Theater, Theatre, Tohill, Play, Musical">Bunce Hall</option>
					<option data-tokens="Bozorth Hall, Bozorth, Hall, RTF, Auditorium">Bozorth Hall</option>
					<option data-tokens="Hawthorne Hall, Hawthorne, Hall">Hawthorn Hall</option>
					<option data-tokens="Engineering Building, Engineering, Building, Rowan Hall, Rowan, Engineering Hall, Hall">Engineering Building</option>
					<option data-tokens="James Hall, James, Hall, Education Hall, Education">James Hall</option>
					<option data-tokens="Esbjorson Gym, Esbjorson, Gym, Rec, Rec Center, Center, Workout, Swimming, Pool">Esbjorson Gym</option>
					<option data-tokens="Recreation Center, Recreation, Center, Rec, Rec Center, Gym, Workout, Lifitng, Running, Swimming, Pool">Recreation Center</option>
				</select>
				</div>
			  <div class="input-group">
				<span class="input-group-addon">Floor</span>
				<select class="selectpicker" data-actions-box="true" data-width="auto" data-size="5" data-selected-text-format="" id="floorFilter" multiple>
					<option>1</option>
					<option>2</option>
					<option>3</option>
					<option>4</option>
				</select>
				</div>
			  <div class="input-group">
				<span class="input-group-addon">Gender</span>
				<select class="selectpicker filterPicker" data-actions-box="true" data-width="auto" data-size="5" data-selected-text-format="count > 2" id="genderFilter" multiple>
					<option>Male</option>
					<option>Female</option>
					<option>Gender Neutral</option>
				</select>
				</div>
			  <div class="input-group">
				<span class="input-group-addon">Overall Rating</span>
				<select class="selectpicker filterPicker" data-actions-box="true" data-width="auto" data-size="5" data-selected-text-format="count > 2" id="overallRatingFilter" multiple>
					<option>Very High</option>
					<option>High</option>
					<option>Average</option>
					<option>Low</option>
					<option>I REALLY HAVE TO GO</option>
				</select>
				</div>
				<div class="input-group"><span class="input-group-addon">Cleanliness</span>
				  <select class="selectpicker filterPicker" data-actions-box="true" data-width="auto" data-size="5" data-selected-text-format="count > 2" id="cleanlinessFilter" multiple>
					<option>Spotless</option>
					<option>Clean</option>
					<option>Average</option>
					<option>Not Very Clean</option>
					<option>Disgusting</option>
				</select>
				</div>
			  <div class="input-group">
				<span class="input-group-addon">Crowdedness</span>
				<select class="selectpicker filterPicker" data-actions-box="true" data-width="auto" data-size="5" data-selected-text-format="count > 2" id="crowdednessFilter" multiple>
					<option>Very Empty</option>
					<option>Slightly Empty </option>
					<option>Average</option>
					<option>Slightly Crowded</option>
					<option>Very Crowded</option>
				</select>
				</div>
			</div>
			</form>
		</div>
		<!--End options row-->
		<!--Row for bathroom list-->
		<div class="row text-center" id="bathroomList">
			<div class="col-xs-12 col-md-10 col-md-offset-1">
				<h3>Bathrooms Matching Your Filter (List View):</h3>
				<h4>Click a row in the list to display its info and comments in a popup.</h4>
			</div>
			<div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
				<div id="bathroomsPanel" class="panel panel-default fillparentwidth fillparentheight" style="border-radius:10px">
					<div class="panel-heading"> Bathrooms </div>
					<div class="panel-body"> All bathrooms on campus matching the applied filter. </div>
					<table class="table" id="bathroomsTable">
					</table>
				</div>
				<!--End BR list panel-->
			</div>
			<!--End BR list col -->
		</div>
		<!--End BR list row-->
	</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function() 
    { 
		initializeTable();
    } 
); 
	</script>
</body>

</html>