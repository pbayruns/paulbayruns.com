<?php

//reset the connection stored in SESSION
$_SESSION[ "connection" ] = null;


//Initialize the connection to the database
function initConnection() {

	if ( $_SESSION[ "connection" ] !== null ) {
		return $_SESSION[ "connection" ];
	}
	/*** mysql server login info ***/
	if(isset($_ENV["RDS_HOSTNAME"])){
		$hostname = $_ENV["RDS_HOSTNAME"];
		$username = $_ENV["RDS_USERNAME"];
		$password = $_ENV["RDS_PASSWORD"];
		$dbname = $_ENV["RDS_DB_NAME"];
	}else{
		$hostname = 'localhost';
		$username = 'root';
		$password = '';
		$dbname = 'bathroomfinder';
	}

	//try to connect to the database
	try {
		$_SESSION[ "connection" ] = new PDO( "mysql:host=$hostname;dbname=$dbname",
			$username, $password );
		return $_SESSION[ "connection" ];
	} //if connecting fails, report an error.
	catch ( PDOException $e ) {
		die( 'PDO error: cannot connect: ' . $e->getMessage() );
	}
}

//Get data from the DB based on what function was requested.
//To do so, check the 'fn' value of the GET request.
//if 'fn' of GET was set, we have a get request.
if ( isset( $_GET[ 'fn' ] ) ) {
	switch ( $_GET[ 'fn' ] ) {

		//if all pins info was requested
		case 'allPinInfo':
			//get all of the pin info
			$allPinInfo = getMarkersAndWindowContent();
			//echo the info back as JSON
			echo json_encode( $allPinInfo );
			break;

			//if all comments were requested
		case 'getAllComments':
			//get the comments' data
			//based on the given bathroom ID
			$comments = getCommentsForBathroom( $_GET[ 'id' ] );
			//form the data into an HTML list of comments
			$commentsHTML = makeCommentList( $comments );
			//echo this list
			echo json_encode( $commentsHTML );
			break;

			//if bathroom header info was requested
		case 'getBathroomHeaderInfo':
			//get the bathroom header data 
			//based on the given bathroom ID
			$headerAndRatings = getBathroomHeaderInfo( $_GET[ 'id' ] );
			//echo the data back as JSON
			echo json_encode( $headerAndRatings );
			break;

			//if the list of bathrooms as a table was requested
		case 'getBathroomTable':
			$tableHTML = getAllBathroomsTable();
			echo $tableHTML;
			break;

			//if filtered bathroom pin info was requested
		case 'getAllBathroomsPinInfoFiltered':
			$buildings = $_GET[ 'building' ];
			$floors = $_GET[ 'floor' ];
			$genders = $_GET[ 'gender' ];
			$overallRatings = $_GET[ 'overallRating' ];
			$cleanlinessRatings = $_GET[ 'cleanlinessRating' ];
			$crowdednessRatings = $_GET[ 'crowdednessRating' ];
			$allPins = getBathroomPinsFiltered( $buildings, $floors, $genders, $overallRatings, $cleanlinessRatings, $crowdednessRatings );
			echo json_encode( $allPins );
			break;
	}
} else { //otherwise, we have a post request
	//if a comment was requested to be posted
	if ( $_POST[ 'fn' ] == 'postComment' ) {
		//grab all of the attributes from the POST request
		$bathroomID = $_POST[ 'bathroomID' ];
		$username = $_POST[ 'username' ];
		$uniqueID = $_POST[ 'uniqueID' ];
		$userImg = $_POST[ 'userImg' ];
		$commentText = $_POST[ 'commentText' ];
		$overallRate = $_POST[ 'overallRating' ];
		$cleanRate = $_POST[ 'cleanlinessRating' ];
		$crowdRate = $_POST[ 'crowdednessRating' ];
		//supply all of this data to the post comments function
		postComment( $bathroomID, $username, $uniqueID, $userImg, $commentText, $overallRate, $cleanRate, $crowdRate );
		//echo back the comment text that was posted
		echo $commentText;
	}
}

//Post a comment to the database
function postComment( $bathroomID, $username, $uniqueID, $userImg, $commentText, $overallRate, $cleanRate, $crowdRate ) {
	try {
		initConnection();
		//insert all of the data into the comment table
		$stmt = $_SESSION[ "connection" ]->prepare( "INSERT INTO 
            comment (bathroom_id, comment, userID, name, user_photo, overall_rating, cleanliness_rating, crowdedness_rating) 
            VALUES (:bathroom_id, :comment, :userID, :name, :user_photo, :overall_rating, :cleanliness_rating, :crowdedness_rating)" );
		//bind the parameters to prevent security vulnerabilities
		$stmt->bindParam( ':bathroom_id', $bathroomID );
		$stmt->bindParam( ':comment', $commentText );
		$stmt->bindParam( ':userID', $uniqueID );
		$stmt->bindParam( ':name', $username );
		$stmt->bindParam( ':user_photo', $userImg );
		$stmt->bindParam( ':overall_rating', $overallRate );
		$stmt->bindParam( ':cleanliness_rating', $cleanRate );
		$stmt->bindParam( ':crowdedness_rating', $crowdRate );

		//execute the statement to perform the INSERT
		$stmt->execute();
		// release the statement
		$stmt = null;
		return true; //successful
	} //if anything goes wrong, die with an error message
	catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function getAllBathroomsTable() {
	try {
		initConnection();
		//Forming Query
		$sqlQuery = "SELECT * FROM bathroom ORDER BY building ASC";
		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		// run query
		$stmt->execute();
		$rowcount = $stmt->rowCount();
		// get all the results from database into array of objects
		$result = $stmt->fetchAll( PDO::FETCH_OBJ );
		// $result now has all the information in it
		// Return the number of rows in result set
		$table = tableAsHTML( $result );

		// release the statement
		$stmt = null;
		return $table;
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function tableAsHTML( $result ) {
	$hStart = "<th class=\"clickable\">";
	$hEnd = "&nbsp; <i class=\"fa fa-sort\" aria-hidden=\"true\"></i></th>";
	$table = "<thead>" .$hStart . "Building" . $hEnd . "" .$hStart . "Floor" . $hEnd . "" .$hStart . "Gender" . $hEnd .
	"" .$hStart . "Stall Ct." . $hEnd . "" .$hStart . "Urinal Ct." . $hEnd .
	"" .$hStart . "Avg. Overall" . $hEnd . "" .$hStart . "Avg. Clean." . $hEnd .
	"" .$hStart . "Avg. Crowd." . $hEnd . "</thead>";
	$count = 0;
	foreach ( $result as $row ) {

		$building = $row->building;
		$floor = $row->floor;
		$gender = $row->sex;
		$stallCount = $row->stall_count;
		$urinalCount = $row->urinal_count;
		$overallRating = $row->average_overall_rating;
		$cleanRating = $row->average_cleanliness_rating;
		$crowdRating = $row->average_crowdedness_rating;
		$tableRow = "<tr onclick=\"displayBR(" . $row->bathroom_id . ")\" class=\"bathroom-clickable clickable\" id=\"BR" . $row->bathroom_id . "\"><td>" . $building . "</td><td>" . $floor . "</td><td>" . $gender .
		"</td><td>" . $stallCount . "</td><td>" . $urinalCount . "</td><td>"
		. $overallRating . "</td><td>" . $cleanRating . "</td><td>" . $crowdRating . "</td></tr>";
		$table = $table . $tableRow;
		$count++;
	}
	return $table;
}

function getBathroomHeaderInfo( $bathroom_id ) {
	try {
		initConnection();
		//Forming Query
		$sqlQuery = "SELECT * FROM bathroom WHERE bathroom_id = :bathroomid";
		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		//set the wildcars in the query
		$stmt->bindParam( ":bathroomid", $bathroom_id, PDO::PARAM_INT );
		// run query
		$stmt->execute();
		// get the single row from database into array of objects
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		// $row now has all the ratings in it

		$bathroomTitle = '';
		$sex = $row->sex;
		if ( $sex === 'M' ) {
			$bathroomTitle = "Men's Bathroom";
		} else if ( $sex === 'F' ) {
			$bathroomTitle = "Women's Bathroom";
		} else {
			$bathroomTitle = "Gender Neutral Bathroom";
		}

		$building = $row->building;
		$floor = $row->floor;
		$bathroomLocation = $building . ", Floor " . $floor;

		$overallRating = $row->average_overall_rating;
		$cleanRating = $row->average_cleanliness_rating;
		$crowdedRating = $row->average_crowdedness_rating;

		// release the statement
		$stmt = null;
		return array( $row->bathroom_id, $bathroomTitle, $bathroomLocation, $overallRating, $cleanRating, $crowdedRating );
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function getCommentsForBathroom( $bathroom_ID ) {
	try {
		initConnection();
		//Forming Query
		$sqlQuery = "SELECT * FROM comment WHERE bathroom_id = :bathroomid";
		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		//set the wildcars in the query
		$stmt->bindParam( ":bathroomid", $bathroom_ID, PDO::PARAM_INT );
		// run query
		$stmt->execute();
		$rowcount = $stmt->rowCount();
		// get all the results from database into array of objects
		$result = $stmt->fetchAll( PDO::FETCH_OBJ );
		// $result now has all the information in it
		// Return the number of rows in result set
		$commentsArray = new SplFixedArray( $rowcount );
		$count = 0;
		foreach ( $result as $row ) {
			$overallRating = $row->overall_rating;
			$cleanRating = $row->cleanliness_rating;
			$crowdedRating = $row->crowdedness_rating;
			$commentData = array( $row->name, $row->userID, $overallRating, $cleanRating, $crowdedRating, $row->user_photo, $row->date, $row->comment );
			$commentsArray[ $count ] = $commentData;
			$count++;
		}

		// release the statement
		$stmt = null;
		return $commentsArray;
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function getBathroomPinsFiltered($buildings, $floors, $genders, $overallRatings, $cleanlinessRatings, $crowdednessRatings) {

	try {
		initConnection();
		
		//Forming Query
		$sqlQuery ="SELECT * FROM (SELECT pin_id FROM bathroom WHERE building IS NOT NULL";
		if(isset($buildings) && !empty($buildings))
		{
			$buildingQuery = " AND building IN (". "'" . implode("','", (array)$buildings) . "'" .")";
			$sqlQuery = $sqlQuery . $buildingQuery;
		}
		if(isset($floors) && !empty($floors))
		{
			$floorQuery = " AND floor IN (". "'" . implode("','", (array)$floors) . "'" .")";
			$sqlQuery = $sqlQuery . $floorQuery;
		}
		if(isset($genders) && !empty($genders))
		{
			$genderQuery = " AND sex IN (". "'" . implode("','", (array)$genders) . "'" .")";
			$sqlQuery = $sqlQuery . $genderQuery;
		}
		$sqlQuery = $sqlQuery . ") t1 LEFT JOIN (SELECT * FROM pin) t2 ON t1.pin_id = t2.pin_id";
		/*
		$overallRatingQuery = "AND WHERE building IN ({implode(',', $overallRatings)})}";
		$cleanlinessRatingQuery = "AND WHERE building IN ({implode(',', $cleanlinessRatings)})}";
		$crowdednessRatingQuery = "AND WHERE building IN ({implode(',', $crowdednessRatings)})}";
		*/

		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		// run query
		
		$stmt->execute();
		$rowcount = $stmt->rowCount();
		// get all the results from database into array of objects
		$result = $stmt->fetchAll( PDO::FETCH_OBJ );
		
		// $result now has all the information in it
		// Return the number of rows in result set
		//$rowcount = mysqli_num_rows($result);
		$pinsArray = new SplFixedArray( $rowcount );
		$windowContentArray = new SplFixedArray( $rowcount );
		$count = 0;
		foreach ( $result as $row ) {
			$markerTitle = "$row->location Bathrooms";
			$markerLat = "$row->latitude";
			$markerLong = "$row->longitude";
			$markerArray = array( $markerTitle, $markerLat, $markerLong );
			$pinsArray[ $count ] = $markerArray;
			$windowContentArray[ $count ] = getFilteredInfoWindowContent( $row->pin_id, $buildings, $floors, $genders);
			$count++;
		}

		// release the statement
		$stmt = null;
		return array( $pinsArray, $windowContentArray );
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function getFilteredInfoWindowContent($pin_id, $buildings, $floors, $genders) {
	try {
		initConnection();
		//Forming Query
		$sqlQuery = "SELECT * FROM bathroom WHERE pin_id = :pinID";
		if(isset($buildings) && !empty($buildings))
		{
			$buildingQuery = " AND building IN (". "'" . implode("','", (array)$buildings) . "'" .")";
			$sqlQuery = $sqlQuery . $buildingQuery;
		}
		if(isset($floors) && !empty($floors))
		{
			$floorQuery = " AND floor IN (". "'" . implode("','", (array)$floors) . "'" .")";
			$sqlQuery = $sqlQuery . $floorQuery;
		}
		if(isset($genders) && !empty($genders))
		{
			$genderQuery = " AND sex IN (". "'" . implode("','", (array)$genders) . "'" .")";
			$sqlQuery = $sqlQuery . $genderQuery;
		}
		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		//set the wildcars in the query
		$stmt->bindParam( ":pinID", $pin_id, PDO::PARAM_INT );
		//run the query
		$stmt->execute();
		// get all the results from database into array of objects
		$result = $stmt->fetchAll( PDO::FETCH_OBJ );
		// $result now has all the information in it
		$listItems = "";
		foreach ( $result as $row ) {
			$bathroomID = $row->bathroom_id;
			$item = "<li onclick=\"displayBR(" . $bathroomID . ")\" class=\"list-group-item clickable bathroom-clickable\" id=\"BR" . $bathroomID . "\">";
			$genderModifier = "";
			if ( $row->sex === 'M' ) {
				$genderModifier = "Men's";
			} else if ( $row->sex === 'F' ) {
				$genderModifier = "Women's";
			} else {
				$genderModifier = "Gender Neutral";
			}
			$item = $item . "$row->building " . $genderModifier . " Bathroom, Floor " . $row->floor;
			$item = $item . "</li>";
			$listItems = $listItems . $item;
		}
		//release the statement since we are done with it
		$stmt = null;

		$bathroomList = "<div class=info_content><ul class=\"list-group\">" . $listItems . "</ul></div>";
		$bathroomListArray = array( $bathroomList );
		return $bathroomListArray;
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function getMarkersAndWindowContent() {

	try {
		initConnection();
		//Forming Query
		$sqlQuery = "SELECT * FROM pin";
		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		// run query
		$stmt->execute();
		$rowcount = $stmt->rowCount();
		// get all the results from database into array of objects
		$result = $stmt->fetchAll( PDO::FETCH_OBJ );
		// $result now has all the information in it
		// Return the number of rows in result set
		// $rowcount = mysqli_num_rows($result);
		$pinsArray = new SplFixedArray( $rowcount );
		$windowContentArray = new SplFixedArray( $rowcount );
		$count = 0;
		foreach ( $result as $row ) {
			$markerTitle = "$row->location Bathrooms";
			$markerLat = "$row->latitude";
			$markerLong = "$row->longitude";
			$markerArray = array( $markerTitle, $markerLat, $markerLong );
			$pinsArray[ $count ] = $markerArray;
			$windowContentArray[ $count ] = getInfoWindowContent( $row->pin_id );
			$count++;
		}

		// release the statement
		$stmt = null;
		return array( $pinsArray, $windowContentArray );
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

function getInfoWindowContent( $pin_id ) {
	try {
		initConnection();
		//Forming Query
		$sqlQuery = "SELECT * FROM bathroom WHERE pin_id = :pinID";
		// prepare to execute (this is a security precaution)
		$stmt = $_SESSION[ "connection" ]->prepare( $sqlQuery );
		//set the wildcars in the query
		$stmt->bindParam( ":pinID", $pin_id, PDO::PARAM_INT );
		//run the query
		$stmt->execute();
		// get all the results from database into array of objects
		$result = $stmt->fetchAll( PDO::FETCH_OBJ );
		// $result now has all the information in it
		$listItems = "";
		foreach ( $result as $row ) {
			$bathroomID = $row->bathroom_id;
			$item = "<li onclick=\"displayBR(" . $bathroomID . ")\" class=\"list-group-item clickable bathroom-clickable\" id=\"BR" . $bathroomID . "\">";
			$genderModifier = "";
			if ( $row->sex === 'M' ) {
				$genderModifier = "Men's";
			} else if ( $row->sex === 'F' ) {
				$genderModifier = "Women's";
			} else {
				$genderModifier = "Gender Neutral";
			}
			$item = $item . "$row->building " . $genderModifier . " Bathroom, Floor " . $row->floor;
			$item = $item . "</li>";
			$listItems = $listItems . $item;
		}
		//release the statement since we are done with it
		$stmt = null;

		$bathroomList = "<div class=info_content><ul class=\"list-group\">" . $listItems . "</ul></div>";
		$bathroomListArray = array( $bathroomList );
		return $bathroomListArray;
	} catch ( PDOException $e ) {
		die( 'PDO error fetching data": ' . $e->getMessage() );
	}
}

//Functions for listing all of the comments
function makeCommentList( $comments ) {
	$numComments = count( $comments );
	$out = "";

	if ( isset( $comments ) && $numComments > 0 ) {
		foreach ( $comments as $comment ) {
			$out = $out . makeComment( $comment );
		}
	}
	return array( $numComments, $out );
}

function getRandomIcon() {
	$iconNum = rand( 3, 8 ); //one for every anonymous icon
	return "http://elvis.rowan.edu/~bayrunsp9/Images/pin" . ( string )( $iconNum );
}

function makeComment( $comment ) {
	$username = $comment[ 0 ];
	$uniqueID = $comment[ 1 ];
	$userOverallRating = $comment[ 2 ];
	$userCleanRating = $comment[ 3 ];
	$userCrowdedRating = $comment[ 4 ];
	if ( !isset( $comment[ 5 ] ) || $comment[ 5 ] !== '' ) {
		$userPhoto = $comment[ 5 ];
	} else {
		$userPhoto = getRandomIcon();
	}
	$commentDate = $comment[ 6 ];
	$commentText = $comment[ 7 ];


	$comHTML = "
	<article class=\"row\"> 
		<div class=\"col-md-2 col-sm-2 hidden-xs\"> 
			<figure class=\"thumbnail\"> 
				<img class=\"img-responsive\" src=\"" . $userPhoto . "\" /> 
				<figcaption class=\"text-center\">" . $username . "</figcaption> 
			</figure> 
		</div> 
		<div class=\"col-md-10 col-sm-10\"> 
			<div class=\"panel panel-default arrow left\"> 
				<div class=\"panel-body\"> 
					<header class=\"text-left\"> 
						<div class=\"comment-user\"><i class=\"fa fa-user\"></i>" . $uniqueID . "</div> 
						<time class=\"comment-date\" datetime=\"" . $commentDate . "\"><i class=\"fa fa-clock-o\"></i>" . $commentDate . "</time> 
					</header> 
					<div class=\"comment-post\"> 
						<p> " . $commentText . " </p> 
					</div> 
				</div> 
			</div> 
		</div> 
	</article>";

	return $comHTML;
}
$_SESSION[ "connection" ] = null;
?>