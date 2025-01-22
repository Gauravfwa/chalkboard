<?php

/*

Plugin Name: Clarendon Google Classroom API Integration

Version: 1.1

Description: Google Classroom API Integration Developed by Clarendon Technologies Inc.

Author: David MacNeill

License: GPL

*/

?>
<?php
define("CLARENDON_CC_DIR", $_SERVER['DOCUMENT_ROOT']. '/wp-content/plugins/clarendon-gapi/' );

//Admin Menu
function gapi_menu() {

	//$page = add_menu_page( "Conditions", "Conditions", 'manage_options', "condition_control", 'clarendon_condition_control_admin_view' );
	//add_action('admin_print_styles-' . $page, 'clarendon_condition_control_admin_style');
}

function clarendon_condition_gapi_admin_style() {

	//$src = CLARENDON_CC_DIR . 'clarendon_cc_admin.css';

	//wp_register_style('clarendon_cc-admin-style',$src); 

	//wp_enqueue_style('clarendon_cc-admin-style');

}

function clarendon_gapi_admin_view()
{
	global $wpdb;
        
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */


function clarendon_gapi_authenticate()
{
	global $wpdb;

	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

	// ini_set('display_errors','Off');
	// ini_set('error_reporting', E_ALL );


	require CLARENDON_CC_DIR . 'gapi/vendor/autoload.php';

    $client = new Google_Client();
    $client->setApplicationName('Chalkboard Publishing');
    $client->setScopes(array(Google_Service_Classroom::CLASSROOM_COURSES,Google_Service_Classroom::CLASSROOM_ROSTERS,Google_Service_Classroom::CLASSROOM_TOPICS,Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS,Google_Service_Classroom::CLASSROOM_COURSEWORK_ME));
    $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/clarendon-gapi/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
	$currentToken = get_user_meta(get_current_user_id(), "classroom_token", true);	

	$forceAuth = get_user_meta(get_current_user_id(), "force_auth", true);	

	if (isset($currentToken) && $currentToken!= "") {
		$client->setAccessToken($currentToken);
		if ($client->isAccessTokenExpired()) {
			if ($client->getRefreshToken()) {
					$currentToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
					update_user_meta(get_current_user_id(), "classroom_token", $currentToken);
				}
		}
	}	


	if (isset($_GET['clear'])) {
		delete_user_meta(get_current_user_id(), "classroom_token");
	}
	else if (isset($currentToken) && $currentToken != ""){
		
		$accessToken = $currentToken;
		$client->setAccessToken($accessToken);
		$service = new Google_Service_Classroom($client);	
		$optParams = array(
		  'pageSize' => 10,
		  // 'studentId' => 'me'
		);
		$results = $service->courses->listCourses($optParams);


		$userProfiles = $service->userProfiles->get("me");

		// print_r($userProfiles);



		if (count($results->getCourses()) == 0) {
		  print "No courses found.\n";
		} else {
		  // print "Courses:\n";
		  foreach ($results->getCourses() as $course) {


		  	foreach ($currentCourseWork = $service->courses_courseWork->listCoursesCourseWork($course->getId()) as $cw) {
		  		# code...


					$studentSub = $service->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions($course->getId(), $cw->getId());


					foreach ($studentSub->studentSubmissions as $sub) {


						$submission = $service->courses_courseWork_studentSubmissions->turnIn($course->getId(), $cw->getId(), $sub->getId(), new Google_Service_Classroom_TurnInStudentSubmissionRequest());


						// print_r($submission);

					    $client2 = new Google_Client();
					    $client2->setApplicationName('Chalkboard Publishing');
					    $client2->setScopes(array(Google_Service_Classroom::CLASSROOM_COURSES,Google_Service_Classroom::CLASSROOM_ROSTERS,Google_Service_Classroom::CLASSROOM_TOPICS,Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS,Google_Service_Classroom::CLASSROOM_COURSEWORK_ME));
					    $client2->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/clarendon-gapi/credentials.json');
					    $client2->setAccessType('offline');
					    $client2->setPrompt('select_account consent');
						// $currentToken = get_user_meta(get_current_user_id(), "classroom_token", true);	


						$teacherToken = get_user_meta(690, "classroom_token", true);

						// print_r($teacherToken);

						if(isset($teacherToken) && $teacherToken != ""){


							$accessTeacherToken = $teacherToken;


							$client2->setAccessToken($accessTeacherToken);

							// $client->setAccessToken($accessTeacherToken);

							$TeachService = new Google_Service_Classroom($client2);	

							$subset = new Google_Service_Classroom_StudentSubmission();
							$subset->setAssignedGrade(48);
							$subset->setDraftGrade(48);
							$subset->setState('TURNED_IN');  // Worked with no apparent effect
							$opt = array('updateMask' => 'assignedGrade,draftGrade');


							$userProfiles2 = $TeachService->userProfiles->get("me");


							$retval = $TeachService->courses_courseWork_studentSubmissions->patch($course->getId(), $cw->getId(), $sub->getId(), $subset, $opt);							


						}




					}


		  	}


		  	// $currentCourseWork = $service->courses_courseWork->listCoursesCourseWork($course->getId());

		   //  //print_r($currentCourseWork);
		   //  count($currentCourseWork->)

		  	// echo "here " . $currentCourseWork->getDescription();

		  	// echo $course->getId();

		  	// $service->courses_courseWork->listCoursesCourseWork($course->getId());


		  	// $currentCourseWork = $service->courses_courseWork->listCoursesCourseWork($course->getId());
			
			// echo "<a href=\"/index.php/authorize/?add=true\" class=\"nectar-button large\">" . $course->getName() . " &check;</a> ";
			// $topicId = "";
			
			// foreach ($service->courses_topics->listCoursesTopics($course->getId()) as $top) {
			// 	if ($top->name == "Reading") {
			// 		$topicId = $top->topicId;
			// 	}
			// }
			// if ($topicId == "")
			// {
			// 	$topic = new Google_Service_Classroom_Topic();
			// 	$topic->courseId = $course->getId();
			// 	$topic->name = "Reading";
			// 	$topic = $service->courses_topics->create($course->getId(), $topic);
			// 	$topicId = $topic->topicId;
			// }
			// else {

			// }
			
			// $currentCourseWork = $service->courses_courseWork->listCoursesCourseWork($course->getId());
			
			// $found = false;
			// if (count($currentCourseWork) > 0) $found = true;
			
			// if (!$found)
			// {
			// 	$courseWork = new Google_Service_Classroom_CourseWork();
			// 	$courseWork->courseId = $course->getId();
			// 	$courseWork->title = 'Colour Match Up';
			// 	$courseWork->workType = 'ASSIGNMENT';
			// 	$courseWork->state = 'PUBLISHED';
			// 	$courseWork->description = 'Match the colours with the shapes.';
			// 	$material = new Google_Service_Classroom_Material();
			// 	$link = new Google_Service_Classroom_Link();
			// 	$link->setTitle("My Link");
			// 	$link->setUrl("http://localhost:4444/index.php/colouring-app/");
			// 	$material->setLink($link);
			// 	$courseWork->materials = array($material);
			// 	$courseWork->topicId = $topicId;
			// 	$courseWork = $service->courses_courseWork->create($course->getId(), $courseWork);
			// }			
		  }
		}		  
	}
	else if (isset($_GET['code'])) {
		$authCode=$_GET['code'];
		$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
	    
		

		if (!isset($currentToken) || empty($currentToken))
		{
			add_user_meta(get_current_user_id(), "classroom_token", $accessToken);
		}
		else {
			update_user_meta(get_current_user_id(), "classroom_token", $accessToken);
		}
		$client->setAccessToken($accessToken);	

		$service = new Google_Service_Classroom($client);

		// Print the first 10 courses the user has access to.
		$optParams = array(
		  'pageSize' => 10
		);
		$results = $service->courses->listCourses($optParams);


		echo "<script type=\"text/javascript\">";
		echo "window.location='/index.php/classroom-auth/';";
		echo "</script>";


		if (count($results->getCourses()) == 0) {
		  print "No courses found.\n";
		} else {
		  //print "Courses:\n";
		  foreach ($results->getCourses() as $course) {
			 echo "<a href=\"/index.php/authorize/?add=true\" class=\"nectar-button large\">" . $course->getName() . "</a>";
			//printf("%s (%s)\n", $course->getName(), $course->getId());
			
			/*
			
			$courseWork = {
			  'title': 'Building With Binary',
			  'description': 'Learn Binary Code in a new and exciting way.',
			  'materials': [
				 {'link': { 'url': 'https://www.clarendontech.com' }}
			],
			  'workType': 'ASSIGNMENT',
			  'state': 'PUBLISHED',
			}
			*/
			//$courseWork = $service->courses->courseWork->create(courseId=$course->getId(), body=$courseWork).execute();
			//printf("Assignment created with %s\n", $courseWork->getId());
		  }
		}		
	}
	else
	{
    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.

		// If there is no previous token or it's expired.
		if ($client->isAccessTokenExpired()) {

			update_user_meta(get_current_user_id(), "force_auth", 'false');

			// get_current_user_id(), "force_auth", true


			// Refresh the token if possible, else fetch a new one.
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			} else {
				// Request authorization from the user.
				$authUrl = $client->createAuthUrl();
				echo "<script type=\"text/javascript\">";
				echo "window.location='$authUrl';";
				echo "</script>";
				die();
				//header("Location: $authUrl");
				//printf("Open the following link in your browser:\n%s\n", $authUrl);
				
				/*
				print 'Enter verification code: ';
				//$authCode = trim(fgets(STDIN));

				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				$client->setAccessToken($accessToken);

				// Check to see if there was an error.
				if (array_key_exists('error', $accessToken)) {
					throw new Exception(join(', ', $accessToken));
				}*/
			}
			// Save the token to a file.
			/*
			if (!file_exists(dirname($tokenPath))) {
				mkdir(dirname($tokenPath), 0700, true);
			}
			file_put_contents($tokenPath, json_encode($client->getAccessToken()));
			*/
		}
	}	
	
}

?>
<?php

add_action('admin_menu','gapi_menu');

add_shortcode('ClassroomAPI', 'clarendon_gapi_add_to_classroom');


add_shortcode('Add_To_Google_Classroom', 'addToClassroomButton');

function initializeGoogleAndMicrosoft() {
	checkMicrosoftAccess();
	checkBrightspaceAccess();
	//addToTeamsButton();
	//addToBrightspace();
	checkGoogleAccess();
}

add_shortcode('CheckGoogleAccess', 'initializeGoogleAndMicrosoft');

function addToClassroomButton() {

	require CLARENDON_CC_DIR . 'gapi/vendor/autoload.php';

	// $_SESSION["isTeach"] = "";


	// if(is_user_logged_in()){
	// 	if(!isset($_COOKIE["wordpress_appView"]) && !isset($_GET['source']) && !isset($_COOKIE["isTeach"])){
			
	// 		echo "<style>.ld-tabs{display: none;} .wpProQuiz_content{display: none;}</style>";
	// 	}
	// }else{
	// 	echo "<style>.ld-tabs{display: none;} .wpProQuiz_content{display: none;}</style>";
	// }	

    $client = new Google_Client();
    $client->setApplicationName('Chalkboard Publishing');
    $client->setScopes(array(Google_Service_Classroom::CLASSROOM_COURSES,Google_Service_Classroom::CLASSROOM_ROSTERS,Google_Service_Classroom::CLASSROOM_TOPICS,Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS,Google_Service_Classroom::CLASSROOM_COURSEWORK_ME));
    $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/clarendon-gapi/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
	$currentToken = get_user_meta(get_current_user_id(), "classroom_token", true);	
	$teamsToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);
	$d2lToken = get_user_meta(get_current_user_id(), "d2l_token", true);

	if (isset($currentToken) && $currentToken!= "") {
		$client->setAccessToken($currentToken);
		if ($client->isAccessTokenExpired()) {

			$isError = true;

			if ($client->getRefreshToken()) {
					$currentToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
					update_user_meta(get_current_user_id(), "classroom_token", $currentToken);
				}
		}
	}

	if (isset($currentToken) && $currentToken != ""){
		$accessToken = $currentToken;
		$client->setAccessToken($accessToken);
		$service = new Google_Service_Classroom($client);	
		$optParams = array(
		  'pageSize' => 10
		);
		// $results = $service->courses->listCourses($optParams);	
		
		  $isTeacher = false;

		  $hasCourse = false;

		try {

		  $results = $service->courses->listCourses($optParams);	

		  foreach ($results->getCourses() as $course) {
						//if (strpos( $course->getName(), "Kindergarten") === false) continue;

		  	$hasCourse = true;

			$userProfiles = $service->userProfiles->get("me");
			$teachers = $service->courses_teachers->listCoursesTeachers($course->getId());
			foreach ($teachers->getTeachers() as $teacher) {	
				if ($teacher->getUserId() == $userProfiles->getId()) {
					$isTeacher = true;
					// echo "is teacher ";							
				}
			}			

		  }

		} catch (Exception $e) {

			// $currentToken = "";

		    // echo 'Caught exception: ',  $e->getMessage(), "\n";

		    // echo "<p style='font-weight: bold; text-align: center; color: red;'>There was a problem with google classroom, please <a href='/my-account/'>click here</a> to go to your account and click 'Re-Authorize Google Classroom'</p>";

		    echo "<p style='font-weight: bold; text-align: center; color: red;'>Sorry, we were unable to connect to your Google Classroom account. Please <a href='/my-account/?reauthorize=true'> Click Here </a> to Re-authorize Google Classroom.</p>";


		    // die();
		}		  





		if (!isset($teamsToken) || $teamsToken == "")
		{
			if(is_user_logged_in() && (($isTeacher && $hasCourse) || isset($_COOKIE["wordpress_appView"])))  {

				//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";

			}

			if(is_user_logged_in() && !$isTeacher && $hasCourse){

				//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Student</strong></p>";

				// echo "<style>.ld-tabs{display: block!important;} .wpProQuiz_content{display: block!important;}</style>";

			}
		}

		if(is_user_logged_in() && !$hasCourse){

			if((isset($_GET['appView']) && $_GET['appView'] == "true") ||(isset($_SESSION["isTeach"]) && $_SESSION["isTeach"]) || (isset($_COOKIE["isTeach"]))){

				$_SESSION["isTeach"] = true;

				$resource= get_the_ID();

				if (!isset($teamsToken) || $teamsToken == "")
				{
					//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";
				}


				
				// echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";


				if(!isset($_COOKIE["isTrial"]) && empty($_COOKIE["isTrial"]) && !isset($_GET['trialView']) && empty($_GET["trialView"]))
				{
				}				

					// echo "<a href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";

				echo "<a href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton height-50\"><img src='https://chalkboardpublishing.com/wp-content/uploads/2022/04/google_classroom_logo.png'> Add to Google Classroom</a>";					
	


				// if(!isset($_GET['trialView']) && $_GET['trialView'] == "" && !isset($_COOKIE["trialView"]) && $_COOKIE['trialView'] == "")
				// {
				// 	echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";
				// }


			}
		}



		if(is_user_logged_in() && $isTeacher){
			$resource= get_the_ID();





				// echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";

				if(!isset($_COOKIE["isTrial"]) && empty($_COOKIE["isTrial"]) && !isset($_GET['trialView']) && empty($_GET["trialView"]))
				{
				}				

				// echo "<a href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";	


				echo "<a href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton height-50\"><img src='https://chalkboardpublishing.com/wp-content/uploads/2022/04/google_classroom_logo.png'> Add to Google Classroom</a>";				

				// if((isset($_GET['appView']) && $_GET['appView'] == "true") ||(isset($_SESSION["isTeach"]) && $_SESSION["isTeach"]))
				if((isset($_GET['appView']) && $_GET['appView'] == "true") ||(isset($_SESSION["isTeach"]) && $_SESSION["isTeach"]) || (isset($_COOKIE["isTeach"])))
				{


					$_SESSION["isTeach"] = true;

					echo "<script>";

					echo "jQuery(document).ready(function(){

						console.log('test 1');

						jQuery('.addToClassroomButton').show();

						setTimeout(function(){

						 jQuery('.wpProQuiz_quiz').show();

						 jQuery('.wpProQuiz_listItem').show();

						}, 2000);

					})";


					echo "</script>";


					// echo "test";	
				}



		}

	}
	else if(($_GET['source'] == "" && !isset($_GET['source'])) || $_COOKIE['isTeach'] == 'true') {
		
			if(is_user_logged_in())
			{


				$msToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);

				$gToken = get_user_meta(get_current_user_id(), "classroom_token", true);	
			
				$d2lToken = get_user_meta(get_current_user_id(), "d2l_token", true);

				if(true){

					$resource= get_the_ID();

					// echo "<a href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";	

					//if(!isset($msToken) && empty($msToken)){

						echo "<a href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton height-50\"><img src='https://chalkboardpublishing.com/wp-content/uploads/2022/04/google_classroom_logo.png'> Add to Google Classroom</a>";
					//}


					if (isset($msToken) && !empty($msToken))
					{
						echo "<p  style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in to Microsoft Teams as a <strong>Teacher</strong></p>";
					}

					else if(isset($gToken) && !empty($gToken)){
						echo "<p  style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in to Google Classroom as a <strong>Teacher</strong></p>";

					}
					else if(isset($d2lToken) && !empty($d2lToken)){
						echo "<p  style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in to D2L as a <strong>Teacher</strong></p>";

					}
				}



			}


	}


echo "<style>

	.addToClassroomButton
	{
	    background-color: #57a6d5!important;
	    border-radius: 30px!important;
	    color: #FFF!important;
	    font-weight: bold!important;
	    box-shadow: none!important;
	    font-size: 16px;
	}

	.addToClassroomButton img{
		width: 30px!important;
		margin-bottom: 0!important;
		margin-right: 10px!important;
		vertical-align: middle!important;
	}

	.height-50{
		height: 50px;
	}



</style>";

echo "<script>";

echo "function getCookie(cname) {
		  var name = cname + \"=\";
		  var decodedCookie = decodeURIComponent(document.cookie);
		  var ca = decodedCookie.split(';');
		  for(var i = 0; i <ca.length; i++) {
		    var c = ca[i];
		    while (c.charAt(0) == ' ') {
		      c = c.substring(1);
		    }
		    if (c.indexOf(name) == 0) {
		      return c.substring(name.length, c.length);
		    }
		  }
		  return \"\";
		}";


		echo "jQuery(document).ready(function(){


			var cookie = getCookie('isTeach');

			console.log(cookie);

			if(cookie == 'true')
			{

				console.log('test 2');

				jQuery('.addToClassroomButton').show();

				setTimeout(function(){

				 jQuery('.wpProQuiz_quiz').show();

				 jQuery('.wpProQuiz_listItem').show();

				}, 2000);				
			}

		})";



echo "</script>";





if(isset($_GET['appView']) && $_GET['appView'] == "true")
{

	$_SESSION["isTeach"] = true;

	echo "<script>";

	echo "function setCookie(cname, cvalue, exdays) {
			  var d = new Date();
			  d.setTime(d.getTime() + (exdays*24*60*60*1000));
			  var expires = \"expires=\"+ d.toUTCString();
			  document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
		}";


	echo "setCookie('isTeach','true',30);";	



	echo "jQuery(document).ready(function(){

		console.log('test');

		var cookie = getCookie('isTeach');

		console.log(cookie);

		setTimeout(function(){

		 jQuery('.wpProQuiz_quiz').show();

		 jQuery('.wpProQuiz_listItem').show();

		}, 2000);


		


	})";


	echo "</script>";


	// echo "test";	
}


if(isset($_GET['trialView']) && $_GET['trialView'] == "true"){

	echo "<script>";

	echo "setCookie('isTrial','true',30);";	

	echo "</script>";

}



// if(!is_user_logged_in())
// {

// 	if(isset($_GET['appView']) && $_GET['appView'] == "true")
// 	{

// 		$_SESSION["isTeach"] = true;

// 		echo "<script>";

// 		echo "function setCookie(cname, cvalue, exdays) {
// 				  var d = new Date();
// 				  d.setTime(d.getTime() + (exdays*24*60*60*1000));
// 				  var expires = \"expires=\"+ d.toUTCString();
// 				  document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
// 			}";



	


// 		echo "setCookie('isTeach','true',30);";	



// 		echo "jQuery(document).ready(function(){

// 			console.log('test');

// 			var cookie = getCookie('isTeach');

// 			console.log(cookie);

// 			setTimeout(function(){

// 			 jQuery('.wpProQuiz_quiz').show();

// 			 jQuery('.wpProQuiz_listItem').show();

// 			}, 2000);


			


// 		})";


// 		echo "</script>";


// 		// echo "test";	
// 	}

// }



}

function checkGoogleAccess() {

	// echo "herere";

	if(is_user_logged_in())
	{

		require CLARENDON_CC_DIR . 'gapi/vendor/autoload.php';

	    $client = new Google_Client();
	    $client->setApplicationName('Chalkboard Publishing');
	    $client->setScopes(array(Google_Service_Classroom::CLASSROOM_COURSES,Google_Service_Classroom::CLASSROOM_ROSTERS,Google_Service_Classroom::CLASSROOM_TOPICS,Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS,Google_Service_Classroom::CLASSROOM_COURSEWORK_ME));
	    $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/clarendon-gapi/credentials.json');
	    $client->setAccessType('offline');
	    $client->setPrompt('select_account consent');
		$currentToken = get_user_meta(get_current_user_id(), "classroom_token", true);


		if (isset($currentToken) && $currentToken!= "") {
			$client->setAccessToken($currentToken);
			if ($client->isAccessTokenExpired()) {
				if ($client->getRefreshToken()) {
						$currentToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
						update_user_meta(get_current_user_id(), "classroom_token", $currentToken);
					}
			}
		}


		$currentRedirect = get_user_meta(get_current_user_id(), "classroom_redirect", true);	
		$resource = get_the_ID();
		$permalink = get_permalink($resource);	
		if (!isset($currentRedirect) || empty($currentRedirect))
		{
			add_user_meta(get_current_user_id(), "classroom_redirect", $permalink);
		}
		else {
			update_user_meta(get_current_user_id(), "classroom_redirect", $permalink);
		}		
		if (isset($currentToken) && $currentToken != "") {
			
		}	
		else if (isset($_GET['code'])) {	
			$authCode=$_GET['code'];
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
		    
			

			if (!isset($currentToken) || empty($currentToken))
			{
				add_user_meta(get_current_user_id(), "classroom_token", $accessToken);
			}
			else {
				update_user_meta(get_current_user_id(), "classroom_token", $accessToken);
			}	
		}
		else {
			if ($client->isAccessTokenExpired()) {
				// Refresh the token if possible, else fetch a new one.
				if ($client->getRefreshToken()) {
					$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				} else {
					// Request authorization from the user.
					$authUrl = $client->createAuthUrl();
					echo "<script type=\"text/javascript\">";
					echo "window.location='$authUrl';";
					echo "</script>";
					die();

				}

			}		
		}

	}
	else if(!is_user_logged_in()){

		$current_url = "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];


		if(isset($_GET["source"]) && $_GET["source"] == "classroom")
		{

			// echo "<div style='text-align: center; margin-top: 40px; margin-bottom: 40px;'><h2>To view this assigned work, click \"Continue with Google\" below</h2>";

			echo "<div style='margin-top: 40px; margin-bottom: 40px; background-color: #EFEFEF; padding: 10px;'>";
			echo "<p style='color: red;'><strong>Your teacher has assigned this work for you to complete.</strong></p>
				<p><strong>Step 1:</strong> Click “Continue with Google” below.</p>
				<p><strong>Step 2:</strong> Sign into the same Google account that you use in your Google Classroom</p>
				<p><strong>Step 3:</strong> Complete the assignment and click “Finish Questions”. Your work will be automatically submitted to your teacher</p>

			";

		echo do_shortcode( '[nextend_social_login trackerdata="source" redirect="'.$current_url.'" align="center"]' );



		}
		else if(isset($_GET["source"]) && $_GET["source"] == "teams"){

			echo "<div style='margin-top: 40px; margin-bottom: 40px; background-color: #EFEFEF; padding: 10px;'>";
			echo "<p style='color: red;'><strong>Your teacher has assigned this work for you to complete.</strong></p>
				<p><strong>Step 1:</strong> Click <strong><em>“Continue with Microsoft Teams”</em></strong> below.</p>
				<p><strong>Step 2:</strong> Sign into the same Microsoft account that you use in your Microsoft Teams.</p>
				<p><strong>Step 3:</strong> Complete the assignment and click “Finish Questions”. Your work will be automatically submitted to your teacher.</p>

			";

		$resource= get_the_ID();

		echo "<div style='text-align: center;'><a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px; background-color: #FFF;\"><img alt=\"Share to Microsoft Teams\" src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMTAyNCAxMDI0Ij4KICAgICAgPGRlZnM+CiAgICAgICAgPGxpbmVhckdyYWRpZW50IGlkPSJwbGF0ZS1maWxsIiB4MT0iLS4yIiB5MT0iLS4yIiB4Mj0iLjgiIHkyPSIuOCI+CiAgICAgICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM1YTYyYzQiPjwvc3RvcD4KICAgICAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzM5NDBhYiI+PC9zdG9wPgogICAgICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICAgICAgPHN0eWxlPgogICAgICAgICAgLmNscy0xe2ZpbGw6IzUwNTljOX0uY2xzLTJ7ZmlsbDojN2I4M2VifQogICAgICAgIDwvc3R5bGU+CiAgICAgICAgPGZpbHRlciBpZD0icGVyc29uLXNoYWRvdyIgeD0iLTUwJSIgeT0iLTUwJSIgd2lkdGg9IjMwMCUiIGhlaWdodD0iMzAwJSI+CiAgICAgICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI1Ij48L2ZlR2F1c3NpYW5CbHVyPgogICAgICAgICAgPGZlT2Zmc2V0IGR5PSIyNSI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgICA8ZmVGdW5jQSB0eXBlPSJsaW5lYXIiIHNsb3BlPSIuMjUiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKCiAgICAgICAgPGZpbHRlciBpZD0iYmFjay1wbGF0ZS1zaGFkb3ciIHg9Ii01MCUiIHk9Ii01MCUiIHdpZHRoPSIzMDAlIiBoZWlnaHQ9IjMwMCUiPgogICAgICAgICAgCgk8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI0Ij48L2ZlR2F1c3NpYW5CbHVyPgoJICA8ZmVPZmZzZXQgZHg9IjIiIGR5PSIyNCI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjYiPjwvZmVGdW5jQT4KCiAgICAgICAgICA8L2ZlQ29tcG9uZW50VHJhbnNmZXI+CiAgICAgICAgICA8ZmVNZXJnZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlPjwvZmVNZXJnZU5vZGU+CiAgICAgICAgICAgIDxmZU1lcmdlTm9kZSBpbj0iU291cmNlR3JhcGhpYyI+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgIDwvZmVNZXJnZT4KICAgICAgICA8L2ZpbHRlcj4KICAgICAgICA8ZmlsdGVyIGlkPSJ0ZWUtc2hhZG93IiB4PSItNTAlIiB5PSItNTAlIiB3aWR0aD0iMjUwJSIgaGVpZ2h0PSIyNTAlIj4KICAgICAgICAgIDxmZUdhdXNzaWFuQmx1ciBpbj0iU291cmNlQWxwaGEiIHN0ZERldmlhdGlvbj0iMTIiPjwvZmVHYXVzc2lhbkJsdXI+CiAgICAgICAgICA8ZmVPZmZzZXQgZHg9IjEwIiBkeT0iMjAiPjwvZmVPZmZzZXQ+CiAgICAgICAgICA8ZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjIiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKICAgICAgIAoKICAgICAgICA8Y2xpcFBhdGggaWQ9ImJhY2stcGxhdGUtY2xpcCI+CiAgICAgICAgICA8cGF0aCBkPSJNNjg0IDQzMkg1MTJ2LTQ5LjE0M0ExMTIgMTEyIDAgMSAwIDQxNiAyNzJhMTExLjU1NiAxMTEuNTU2IDAgMCAwIDEwLjc4NSA0OEgxNjBhMzIuMDk0IDMyLjA5NCAwIDAgMC0zMiAzMnYzMjBhMzIuMDk0IDMyLjA5NCAwIDAgMCAzMiAzMmgxNzguNjdjMTUuMjM2IDkwLjggOTQuMiAxNjAgMTg5LjMzIDE2MCAxMDYuMDM5IDAgMTkyLTg1Ljk2MSAxOTItMTkyVjQ2OGEzNiAzNiAwIDAgMC0zNi0zNnoiIGZpbGw9IiNmZmYiPjwvcGF0aD4KICAgICAgICA8L2NsaXBQYXRoPgogICAgICA8L2RlZnM+CiAgICAgIDxnIGlkPSJzbWFsbF9wZXJzb24iIGZpbHRlcj0idXJsKCNwZXJzb24tc2hhZG93KSI+CiAgICAgICAgPHBhdGggaWQ9IkJvZHkiIGNsYXNzPSJjbHMtMSIgZD0iTTY5MiA0MzJoMTY4YTM2IDM2IDAgMCAxIDM2IDM2djE2NGExMjAgMTIwIDAgMCAxLTEyMCAxMjAgMTIwIDEyMCAwIDAgMS0xMjAtMTIwVjQ2OGEzNiAzNiAwIDAgMSAzNi0zNnoiPjwvcGF0aD4KICAgICAgICA8Y2lyY2xlIGlkPSJIZWFkIiBjbGFzcz0iY2xzLTEiIGN4PSI3NzYiIGN5PSIzMDQiIHI9IjgwIj48L2NpcmNsZT4KICAgICAgPC9nPgogICAgICA8ZyBpZD0iTGFyZ2VfUGVyc29uIiBmaWx0ZXI9InVybCgjcGVyc29uLXNoYWRvdykiPgogICAgICAgIDxwYXRoIGlkPSJCb2R5LTIiIGRhdGEtbmFtZT0iQm9keSIgY2xhc3M9ImNscy0yIiBkPSJNMzcyIDQzMmgzMTJhMzYgMzYgMCAwIDEgMzYgMzZ2MjA0YTE5MiAxOTIgMCAwIDEtMTkyIDE5MiAxOTIgMTkyIDAgMCAxLTE5Mi0xOTJWNDY4YTM2IDM2IDAgMCAxIDM2LTM2eiI+PC9wYXRoPgogICAgICAgIDxjaXJjbGUgaWQ9IkhlYWQtMiIgZGF0YS1uYW1lPSJIZWFkIiBjbGFzcz0iY2xzLTIiIGN4PSI1MjgiIGN5PSIyNzIiIHI9IjExMiI+PC9jaXJjbGU+CiAgICAgIDwvZz4KICAgICAgPHJlY3QgaWQ9IkJhY2tfUGxhdGUiIHg9IjEyOCIgeT0iMzIwIiB3aWR0aD0iMzg0IiBoZWlnaHQ9IjM4NCIgcng9IjMyIiByeT0iMzIiIGZpbHRlcj0idXJsKCNiYWNrLXBsYXRlLXNoYWRvdykiIGNsaXAtcGF0aD0idXJsKCNiYWNrLXBsYXRlLWNsaXApIiBmaWxsPSJ1cmwoI3BsYXRlLWZpbGwpIj48L3JlY3Q+CiAgICAgIDxwYXRoIGlkPSJMZXR0ZXJfVCIgZD0iTTM5OS4zNjUgNDQ1Ljg1NWgtNjAuMjkzdjE2NC4yaC0zOC40MTh2LTE2NC4yaC02MC4wMlY0MTRoMTU4LjczeiIgZmlsdGVyPSJ1cmwoI3RlZS1zaGFkb3cpIiBmaWxsPSIjZmZmIj48L3BhdGg+CiAgICA8L3N2Zz4=\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Continue with Microsoft Teams</a></div>";

		}
		else{


			echo "<div style='margin-top: 40px; margin-bottom: 40px; padding: 10px; display: flex; flex-wrap: wrap;' class='social-icon-con'>";

			echo "<h2 style='font-size: 20px; width: 100%;'>Share this activity with your students.</h2>";

			echo "<div onclick='setCookie(\"googleLogin\",\"true\",30);'>" . do_shortcode( '[nextend_social_login trackerdata="source" redirect="'.$current_url.'" align="center"]' ) . "</div>";

			$resource= get_the_ID();

			echo "<div style='text-align: center;' class='teams-con' onclick='setCookie(\"teamsLogin\",\"true\",30);'><a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px; background-color: #FFF;\"><img alt=\"Share to Microsoft Teams\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/04/teams_logo.png\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Continue with Microsoft Teams</a></div>";			

		// 	echo "<div style='margin-top: 40px; margin-bottom: 40px; background-color: #EFEFEF; padding: 10px;'>";
		// 	echo "<p style='color: red;'><strong>How to share this Lesson/Activity with your Google Classroom:</strong></p>
		// 	<ol style='padding-bottom: 20px; line-height: 1.5;'>
		// 		<li>To share this lesson/activity with Google Classroom, click \"Continue with Google\" to get started.</li>
		// 		<li>After logging in, click \"Add to Google Classroom\" to assign this lesson/activity to your students.</li>
		// 	</ol>";

		// 	echo "<p style='color: red;'><strong>How to share this Lesson/Activity with Microsoft Teams:</strong></p>
		// 	<ol style='padding-bottom: 20px; line-height: 1.5;'>
		// 		<li>To share this lesson/activity with Microsof Teams, click \"Continue with Microsoft\" to get started.</li>
		// 		<li>After logging in, click \"Add to Microsoft Teams\" to assign this lesson/activity to your students.</li>
		// 	</ol>";	


		// echo do_shortcode( '[nextend_social_login trackerdata="source" redirect="'.$current_url.'" align="center"]' );

		// $resource= get_the_ID();

		// echo "<div style='text-align: center;'><a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px; background-color: #FFF;\"><img alt=\"Share to Microsoft Teams\" src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMTAyNCAxMDI0Ij4KICAgICAgPGRlZnM+CiAgICAgICAgPGxpbmVhckdyYWRpZW50IGlkPSJwbGF0ZS1maWxsIiB4MT0iLS4yIiB5MT0iLS4yIiB4Mj0iLjgiIHkyPSIuOCI+CiAgICAgICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM1YTYyYzQiPjwvc3RvcD4KICAgICAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzM5NDBhYiI+PC9zdG9wPgogICAgICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICAgICAgPHN0eWxlPgogICAgICAgICAgLmNscy0xe2ZpbGw6IzUwNTljOX0uY2xzLTJ7ZmlsbDojN2I4M2VifQogICAgICAgIDwvc3R5bGU+CiAgICAgICAgPGZpbHRlciBpZD0icGVyc29uLXNoYWRvdyIgeD0iLTUwJSIgeT0iLTUwJSIgd2lkdGg9IjMwMCUiIGhlaWdodD0iMzAwJSI+CiAgICAgICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI1Ij48L2ZlR2F1c3NpYW5CbHVyPgogICAgICAgICAgPGZlT2Zmc2V0IGR5PSIyNSI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgICA8ZmVGdW5jQSB0eXBlPSJsaW5lYXIiIHNsb3BlPSIuMjUiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKCiAgICAgICAgPGZpbHRlciBpZD0iYmFjay1wbGF0ZS1zaGFkb3ciIHg9Ii01MCUiIHk9Ii01MCUiIHdpZHRoPSIzMDAlIiBoZWlnaHQ9IjMwMCUiPgogICAgICAgICAgCgk8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI0Ij48L2ZlR2F1c3NpYW5CbHVyPgoJICA8ZmVPZmZzZXQgZHg9IjIiIGR5PSIyNCI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjYiPjwvZmVGdW5jQT4KCiAgICAgICAgICA8L2ZlQ29tcG9uZW50VHJhbnNmZXI+CiAgICAgICAgICA8ZmVNZXJnZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlPjwvZmVNZXJnZU5vZGU+CiAgICAgICAgICAgIDxmZU1lcmdlTm9kZSBpbj0iU291cmNlR3JhcGhpYyI+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgIDwvZmVNZXJnZT4KICAgICAgICA8L2ZpbHRlcj4KICAgICAgICA8ZmlsdGVyIGlkPSJ0ZWUtc2hhZG93IiB4PSItNTAlIiB5PSItNTAlIiB3aWR0aD0iMjUwJSIgaGVpZ2h0PSIyNTAlIj4KICAgICAgICAgIDxmZUdhdXNzaWFuQmx1ciBpbj0iU291cmNlQWxwaGEiIHN0ZERldmlhdGlvbj0iMTIiPjwvZmVHYXVzc2lhbkJsdXI+CiAgICAgICAgICA8ZmVPZmZzZXQgZHg9IjEwIiBkeT0iMjAiPjwvZmVPZmZzZXQ+CiAgICAgICAgICA8ZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjIiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKICAgICAgIAoKICAgICAgICA8Y2xpcFBhdGggaWQ9ImJhY2stcGxhdGUtY2xpcCI+CiAgICAgICAgICA8cGF0aCBkPSJNNjg0IDQzMkg1MTJ2LTQ5LjE0M0ExMTIgMTEyIDAgMSAwIDQxNiAyNzJhMTExLjU1NiAxMTEuNTU2IDAgMCAwIDEwLjc4NSA0OEgxNjBhMzIuMDk0IDMyLjA5NCAwIDAgMC0zMiAzMnYzMjBhMzIuMDk0IDMyLjA5NCAwIDAgMCAzMiAzMmgxNzguNjdjMTUuMjM2IDkwLjggOTQuMiAxNjAgMTg5LjMzIDE2MCAxMDYuMDM5IDAgMTkyLTg1Ljk2MSAxOTItMTkyVjQ2OGEzNiAzNiAwIDAgMC0zNi0zNnoiIGZpbGw9IiNmZmYiPjwvcGF0aD4KICAgICAgICA8L2NsaXBQYXRoPgogICAgICA8L2RlZnM+CiAgICAgIDxnIGlkPSJzbWFsbF9wZXJzb24iIGZpbHRlcj0idXJsKCNwZXJzb24tc2hhZG93KSI+CiAgICAgICAgPHBhdGggaWQ9IkJvZHkiIGNsYXNzPSJjbHMtMSIgZD0iTTY5MiA0MzJoMTY4YTM2IDM2IDAgMCAxIDM2IDM2djE2NGExMjAgMTIwIDAgMCAxLTEyMCAxMjAgMTIwIDEyMCAwIDAgMS0xMjAtMTIwVjQ2OGEzNiAzNiAwIDAgMSAzNi0zNnoiPjwvcGF0aD4KICAgICAgICA8Y2lyY2xlIGlkPSJIZWFkIiBjbGFzcz0iY2xzLTEiIGN4PSI3NzYiIGN5PSIzMDQiIHI9IjgwIj48L2NpcmNsZT4KICAgICAgPC9nPgogICAgICA8ZyBpZD0iTGFyZ2VfUGVyc29uIiBmaWx0ZXI9InVybCgjcGVyc29uLXNoYWRvdykiPgogICAgICAgIDxwYXRoIGlkPSJCb2R5LTIiIGRhdGEtbmFtZT0iQm9keSIgY2xhc3M9ImNscy0yIiBkPSJNMzcyIDQzMmgzMTJhMzYgMzYgMCAwIDEgMzYgMzZ2MjA0YTE5MiAxOTIgMCAwIDEtMTkyIDE5MiAxOTIgMTkyIDAgMCAxLTE5Mi0xOTJWNDY4YTM2IDM2IDAgMCAxIDM2LTM2eiI+PC9wYXRoPgogICAgICAgIDxjaXJjbGUgaWQ9IkhlYWQtMiIgZGF0YS1uYW1lPSJIZWFkIiBjbGFzcz0iY2xzLTIiIGN4PSI1MjgiIGN5PSIyNzIiIHI9IjExMiI+PC9jaXJjbGU+CiAgICAgIDwvZz4KICAgICAgPHJlY3QgaWQ9IkJhY2tfUGxhdGUiIHg9IjEyOCIgeT0iMzIwIiB3aWR0aD0iMzg0IiBoZWlnaHQ9IjM4NCIgcng9IjMyIiByeT0iMzIiIGZpbHRlcj0idXJsKCNiYWNrLXBsYXRlLXNoYWRvdykiIGNsaXAtcGF0aD0idXJsKCNiYWNrLXBsYXRlLWNsaXApIiBmaWxsPSJ1cmwoI3BsYXRlLWZpbGwpIj48L3JlY3Q+CiAgICAgIDxwYXRoIGlkPSJMZXR0ZXJfVCIgZD0iTTM5OS4zNjUgNDQ1Ljg1NWgtNjAuMjkzdjE2NC4yaC0zOC40MTh2LTE2NC4yaC02MC4wMlY0MTRoMTU4LjczeiIgZmlsdGVyPSJ1cmwoI3RlZS1zaGFkb3cpIiBmaWxsPSIjZmZmIj48L3BhdGg+CiAgICA8L3N2Zz4=\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Continue with Microsoft Teams</a></div>";					

		}





		// echo do_shortcode( '[TheChamp-Login]' );



		echo "</div>";
		// die();
	}

	echo "<style>

		.social-icon-con .nsl-button-google
		{
		    background-color: #57a6d5!important;
		    border-radius: 30px!important;
		    color: #FFF!important;
		    font-weight: bold!important;
		    box-shadow: none!important;
		}

		.social-icon-con .teams-con a
		{
		    background-color: #57a6d5!important;
		    border-radius: 30px!important;
		    color: #FFF!important;
		    font-weight: bold!important;
		    box-shadow: none!important;
		    font-size: 16px!important;
		    margin-left: 20px;
		    padding: 7px 10px!important;
		}	

		.social-icon-con .nsl-button-svg-container{
			background: url('https://chalkboardpublishing.com/wp-content/uploads/2022/04/google_classroom_logo.png');
			background-size: 80%;
		    background-repeat: no-repeat;
		    background-position: center;
		    margin-left: 12px!important;			
		}

		.social-icon-con .nsl-button-svg-container svg{
			opacity: 0;
		}


	</style>";

	echo "<script>";

	echo "function setCookie(cname, cvalue, exdays) {
			  var d = new Date();
			  d.setTime(d.getTime() + (exdays*24*60*60*1000));
			  var expires = \"expires=\"+ d.toUTCString();
			  document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
		}";


	// echo "setCookie('isTeach','true',30);";	

	echo "</script>";		

}

function clarendon_gapi_add_to_classroom()
{
	global $wpdb;
	// ini_set('display_errors','On');
	// ini_set('error_reporting', E_ALL );	
	// ini_set('display_errors','Off');
	// ini_set('error_reporting', E_ALL );
	try{



	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	
	require CLARENDON_CC_DIR . 'gapi/vendor/autoload.php';

    $client = new Google_Client();
    $client->setApplicationName('Chalkboard Publishing');
	
    $client->setScopes(array(Google_Service_Classroom::CLASSROOM_COURSES,Google_Service_Classroom::CLASSROOM_ROSTERS,Google_Service_Classroom::CLASSROOM_TOPICS,Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS,Google_Service_Classroom::CLASSROOM_COURSEWORK_ME));
    $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/clarendon-gapi/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
	$currentToken = get_user_meta(get_current_user_id(), "classroom_token", true);	



	if (isset($currentToken) && $currentToken!= "") {
		$client->setAccessToken($currentToken);
		if ($client->isAccessTokenExpired()) {
			if ($client->getRefreshToken()) {
					$currentToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
					update_user_meta(get_current_user_id(), "classroom_token", $currentToken);
				}
		}
	}

	
	$resource = get_user_meta(get_current_user_id(), "classroom_resource", true);

	$currentRedirect = get_user_meta(get_current_user_id(), "classroom_redirect", true);	

	$forceAuth = get_user_meta(get_current_user_id(), "force_auth", true);	


	// if(get_current_user_id() == 829)
	// {
	// 	// echo "in here";

	// 	$error = 'Always throw this error PERMISSION_DENIED';
 //    	throw new Exception($error);

	// 	// $currentRedirect = get_user_meta(0, "classroom_redirect", true);	


	// 	// die();

	// }





	// if((isset($_GET['topicType']) && !empty($_GET['topicType'])) || isset($_GET['code']))
	// {


		if (isset($_GET['resource'])) {
			if (!isset($resource) || empty($resource))
			{
				add_user_meta(get_current_user_id(), "classroom_resource", $_GET['resource']);
			}
			else {
				update_user_meta(get_current_user_id(), "classroom_resource", $_GET['resource']);
			}
			$resource = $_GET['resource'];
		}
		if (isset($_GET['clear'])) {
			delete_user_meta(get_current_user_id(), "classroom_token");
			delete_user_meta(get_current_user_id(), "classroom_resource");		

		}
		else if (isset($currentToken) && $currentToken != "" && $forceAuth != "true"){
			if (isset($resource) && $resource != 0)
			{


				if($resource == '-1'){
					echo "<script type=\"text/javascript\">";
					echo "window.location='/index.php/my-account/?finished=true';";
					echo "</script>";
					die();
				}



				// check if type has been set
				if((isset($_GET['topicType']) && !empty($_GET['topicType'])))
				{

					$accessToken = $currentToken;
					$client->setAccessToken($accessToken);
					$service = new Google_Service_Classroom($client);	
					$optParams = array(
					  'pageSize' => 10
					);
					$results = $service->courses->listCourses($optParams);

					if (count($results->getCourses()) == 0) {
					  print "No courses found.\n";
					} else {
					  // print "Courses:\n";
					  foreach ($results->getCourses() as $course) {


				  		if((isset($_GET['classRoomID']) && !empty($_GET['classRoomID']))){

				  			if($course->getId() != $_GET['classRoomID']){
				  				continue;
				  			}

				  		}

					  
						//if (strpos( $course->getName(), "Kindergarten") === false) continue;

						$userProfiles = $service->userProfiles->get("me");
						$teachers = $service->courses_teachers->listCoursesTeachers($course->getId());
					    $isTeacher = false;
						foreach ($teachers->getTeachers() as $teacher) {	
							if ($teacher->getUserId() == $userProfiles->getId()) {
								$isTeacher = true;
							}
						}			
						if (!$isTeacher) {
							continue;
						}


						$permalink = get_permalink($resource);

						$currentRedirect = get_user_meta(get_current_user_id(), "classroom_redirect", true);

						if (!isset($currentRedirect) || empty($currentRedirect))
						{
							add_user_meta(get_current_user_id(), "classroom_redirect", $permalink);
						}
						else {
							update_user_meta(get_current_user_id(), "classroom_redirect", $permalink);
						}




						$currentRedirect = get_user_meta(get_current_user_id(), "classroom_redirect", true);


						// echo "<a href=\"".$currentRedirect."\" class=\"nectar-button large\">" . $course->getName() . " &check;</a> ";


						$postTitle = get_the_title($resource);


						echo "<p><strong>\"". $postTitle ."\"</strong> has been shared with your <strong>". $course->getName() . "</strong> class on Google Classroom.</p>";



						echo "<a href=\"".$currentRedirect."\" class=\"nectar-button large\"> Return to Activity </a> ";


						$topicId = "";
						
						$topicName = $_GET['topicType'];

						foreach ($service->courses_topics->listCoursesTopics($course->getId()) as $top) {
							if ($top->name == $topicName) {
								$topicId = $top->topicId;
							}
						}
						if ($topicId == "")
						{


							$topic = new Google_Service_Classroom_Topic();
							$topic->courseId = $course->getId();
							$topic->name = $topicName;
							$topic = $service->courses_topics->create($course->getId(), $topic);
							$topicId = $topic->topicId;
						}
						else {

						}
						
						$currentCourseWork = $service->courses_courseWork->listCoursesCourseWork($course->getId());
						$permalink = get_permalink($resource) . "?source=classroom";
						$found = false;
						if (count($currentCourseWork) > 0) {
							foreach ($currentCourseWork as $curCourse) {
								foreach ($curCourse->materials as $material) {										
									if($material->getLink() != null) //check if link is null
									{
										if ($material->getLink()->getUrl() == $permalink) {
											$found = true;
										}										
									}
								}
							}
						}
						if (!$found)
						{
							
							$excerpt = get_the_excerpt($resource);

							$type = get_post_type($resource);


							// echo $type;


							if($type == 'sfwd-courses')
							{

								$resource_title = esc_html(get_the_title($resource));


								$args = array(
								    'post_type' => 'sfwd-quiz',
								    'posts_per_page' => -1,
								    'meta_key' => 'course_id', 
								    'meta_value' => $resource
								);

								$quizs = get_posts($args);	


							}
							else if($type == 'sfwd-quiz')
							{

								$courseID = get_post_meta( $resource, 'course_id', true);

								// echo $courseID . "<br>";


								$resource_title = esc_html(get_the_title($courseID));


								// echo $courseID . "<br>";
								

								// $args = array(
								//     'post_type' => 'sfwd-quiz',
								//     'posts_per_page' => -1,
								// );

								$quizs = get_post($resource);								

								// $quizs = json_decode(json_encode($quizObj), true);

							}							
							else{


								$courseID = get_post_meta( $resource, 'course_id', true);

								// echo "Course ID " . $courseID . "<br>";

								$resource_title = esc_html(get_the_title($courseID));

								$args = array(
								    'post_type' => 'sfwd-quiz',
								    'posts_per_page' => -1,
								    'meta_key' => 'lesson_id', 
								    'meta_value' => $resource
								);	

								$quizs = get_posts($args);	


							}

							// echo $resource;


							// $img_src = get_the_post_thumbnail_url();

							// $url_parse = wp_parse_url($img_src);


							// $img_src = "//".$_SERVER['HTTP_HOST'] . $url_parse['path'];	

							/* Query args. */


							/* Get Reviews */

							// print_r($quizs);


							// echo "Quiz Count " . count($quizs);	


							if(!is_object($quizs) && count($quizs) > 0)
							{

								foreach ($quizs as $quiz) {


									if($type == "sfwd-lessons"){

										$permalink = get_post_permalink($quiz->ID) . "?source=classroom";

									}
									

									$questions = get_post_meta( $quiz->ID, 'ld_quiz_questions', false);

									$pointTotal = 0;

									for ($i=0; $i < count($questions) ; $i++) { 
										# code...
										$key = array_keys($questions[$i]);

										foreach ($key as $questionID) {

											$points = get_post_meta( $questionID, 'question_points', false);

											foreach ($points as $point) {

												$pointTotal += $point;

											}


										}

									}


									// print_r($quiz);


									$title = $resource_title . '-' . $quiz->post_title;

									$title = str_replace('&#8211;', '-', $title);

									// echo "<br>" . $title . "<br>";

									// echo $pointTotal;

									$courseWork = new Google_Service_Classroom_CourseWork();
									$courseWork->courseId = $course->getId();
									$courseWork->title = $title;
									$courseWork->workType = 'ASSIGNMENT';
									$courseWork->state = 'PUBLISHED';
									$courseWork->description = $excerpt;
									$courseWork->maxPoints = $pointTotal;				
									$material = new Google_Service_Classroom_Material();
									$link = new Google_Service_Classroom_Link();
									$link->setTitle($title);
									$link->setUrl($permalink);
									$material->setLink($link);
									$courseWork->materials = array($material);
									$courseWork->topicId = $topicId;
									$courseWork = $service->courses_courseWork->create($course->getId(), $courseWork);


								}									

							}	
							else if(is_object($quizs)){


								$questions = get_post_meta( $quizs->ID, 'ld_quiz_questions', false);

								$pointTotal = 0;

								for ($i=0; $i < count($questions) ; $i++) { 
									# code...
									$key = array_keys($questions[$i]);

									foreach ($key as $questionID) {

										$points = get_post_meta( $questionID, 'question_points', false);

										foreach ($points as $point) {

											$pointTotal += $point;

										}


									}

								}


								// $title = $resource_title;

								$title = $resource_title . '-' . $quizs->post_title;

								$title = str_replace('&#8211;', '-', $title);

								// echo "<br>" . $title . "<br>";

								// echo $pointTotal;

								$courseWork = new Google_Service_Classroom_CourseWork();
								$courseWork->courseId = $course->getId();
								$courseWork->title = $title;
								$courseWork->workType = 'ASSIGNMENT';
								$courseWork->state = 'PUBLISHED';
								$courseWork->description = $excerpt;
								$courseWork->maxPoints = $pointTotal;				
								$material = new Google_Service_Classroom_Material();
								$link = new Google_Service_Classroom_Link();
								$link->setTitle($title);
								$link->setUrl($permalink);
								$material->setLink($link);
								$courseWork->materials = array($material);
								$courseWork->topicId = $topicId;
								$courseWork = $service->courses_courseWork->create($course->getId(), $courseWork);								


								// print_r($quizs);


								// echo $quizs->ID;
							}
							else{
								// echo "title " . esc_html(get_the_title($resource));

									// echo "in here";

									$title = str_replace('&#8211;', '-', get_the_title($resource));

									$courseWork = new Google_Service_Classroom_CourseWork();
									$courseWork->courseId = $course->getId();
									$courseWork->title = $title;
									$courseWork->workType = 'ASSIGNMENT';
									$courseWork->state = 'PUBLISHED';
									$courseWork->description = $excerpt;
									$courseWork->maxPoints = 10;				
									$material = new Google_Service_Classroom_Material();
									$link = new Google_Service_Classroom_Link();
									$link->setTitle(esc_html(get_the_title($resource)));
									$link->setUrl($permalink);
									$material->setLink($link);
									$courseWork->materials = array($material);
									$courseWork->topicId = $topicId;
									$courseWork = $service->courses_courseWork->create($course->getId(), $courseWork);						

							}


							
							
							add_user_meta(get_current_user_id(), "course_ID", $course->getId());
							

							$classroomID = get_user_meta(get_current_user_id(), "classroom_ID");
							

							add_user_meta(get_current_user_id(), "classroom_ID", $userProfiles->getId());




						}
			            else {

						}	
					  }
					}	
				}
				else{

						// if no topic is set show topic textbox

						$accessToken = $currentToken;
						$client->setAccessToken($accessToken);
						$service = new Google_Service_Classroom($client);	
						$optParams = array(
						  'pageSize' => 10
						);
						$results = $service->courses->listCourses($optParams);

						if (count($results->getCourses()) == 0) {
						  	print "No courses found.\n";

						} else {
							//print "Courses:\n";
							foreach ($results->getCourses() as $course) {

							}						

						}


						echo "<div class='topicCon'>";

						echo "<p>Please enter the topic and classroom for this resource.</p>";



						if (count($results->getCourses()) == 0) {
						  	print "No courses found.\n";
						  	
						} else {
							// print "Courses:\n";

							echo "<select class='ClassroomSelect'>";


							echo "<option value=''>Select Classroom</option>";

							$selected = "";

							foreach ($results->getCourses() as $course) {

								 // $CID = $course->getId();

								 // $Name = $course->getName();


								if(count($results->getCourses()) == 1){
									$selected = "selected";
								}


								echo "<option $selected value='". $course->getId() ."'>". $course->getName() . "</option>";

							}			

							echo "</select>";			

						}						

						echo "<input type='text' class='topicSelect' placeholder='Work Topic'>";



						echo "<a onclick='topicSelect();'>Select</a>";

						echo "</div>";


						echo "\n<script>


							function topicSelect(){

								var topic = jQuery('.topicSelect').val();

								var ClassroomSelect = jQuery('.ClassroomSelect').val();


								var url = window.location.href;

								if(topic != '' && ClassroomSelect != '')
								{
									console.log(url + '&topicType=' + topic );

									window.location.replace(url + '&topicType=' + topic + '&classRoomID=' + ClassroomSelect);

								}else{

									jQuery('.topicCon').append('<p style=\"padding-top: 10px; color: red;\" class=\"errorMessage\">Please enter all fields.</p>');

								}




							}


						</script>

						";					
				}

	      }
		}
		else if (isset($_GET['code'])) {
			$authCode=$_GET['code'];
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
		    
			if (!isset($currentToken) || empty($currentToken))
			{
				add_user_meta(get_current_user_id(), "classroom_token", $accessToken);
			}
			else {
				update_user_meta(get_current_user_id(), "classroom_token", $accessToken);
			}
			$client->setAccessToken($accessToken);	

			$service = new Google_Service_Classroom($client);
			if ($currentRedirect != "")
			{
					echo "<script type=\"text/javascript\">";
					echo "window.location='$currentRedirect';";
					echo "</script>";
					die();		
			}
			else
			{
				// Print the first 10 courses the user has access to.
				$optParams = array(
				  'pageSize' => 10
				);
				$results = $service->courses->listCourses($optParams);
				
				if (count($results->getCourses()) == 0) {
				  print "No courses found.\n";
				} else {
				  //print "Courses:\n";
				  foreach ($results->getCourses() as $course) {
								//if (strpos( $course->getName(), "Kindergarten") === false) continue;
					$userProfiles = $service->userProfiles->get("me");
					$teachers = $service->courses_teachers->listCoursesTeachers($course->getId());
					$isTeacher = false;
					foreach ($teachers->getTeachers() as $teacher) {	
						if ($teacher->getUserId() == $userProfiles->getId()) {
							$isTeacher = true;
						}
					}			
					if (!$isTeacher) {
						continue;
					}else{

						if(isset($resource))
						{
							//after connecting to classroom and is teacher redirect back to added lesson 

							$redirect = "/authorize/?resource=" . $resource;

							echo "<script type=\"text/javascript\">";
							echo "window.location='$redirect';";
							echo "</script>";
							die();													
						}

					}							
					 echo "<a href=\"/authorize/?add=true\" class=\"nectar-button large\">" . $course->getName() . "</a>";
				  }
				}	
			}		
		}
		else
		{
	    // Load previously authorized token from a file, if it exists.
	    // The file token.json stores the user's access and refresh tokens, and is
	    // created automatically when the authorization flow completes for the first
	    // time.


			if($forceAuth == "true"){
				delete_user_meta(get_current_user_id(), "classroom_token");
			}


			update_user_meta(get_current_user_id(), "force_auth", 'false');

			delete_user_meta(get_current_user_id(), "classroom_redirect");

			// If there is no previous token or it's expired.
			if ($client->isAccessTokenExpired()) {
				// Refresh the token if possible, else fetch a new one.
				if ($client->getRefreshToken()) {
					$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				} else {
					// Request authorization from the user.
					$authUrl = $client->createAuthUrl();
					echo "<script type=\"text/javascript\">";
					echo "window.location='$authUrl';";
					echo "</script>";
					die();

				}

			}
		}	


		}
		catch(Exception $e){

			echo "<p><strong>Sorry, we were unable to process your request. Have you tried:</strong><p>
			<ul>
				<li>Making sure you are logged into your email address associated with your Google Classroom?</li>
				<li>Closing down excess tabs in your Web Browser?</li>
			</ul>";

			echo $e;


			if (strpos($e, 'PERMISSION_DENIED') !== false) {
			    // echo 'true';

			    delete_user_meta(get_current_user_id(), "classroom_token");
			}			

			$to = 'jake@clarendontech.com';
			$subject = 'Chalkboard Auth Error';
			$body = "Users ID: " . get_current_user_id() . ": " . $e;
			$headers = array('Content-Type: text/html; charset=UTF-8');
			 
			wp_mail( $to, $subject, $body, $headers );			

			// echo $e;
		}


	// }
	// else{
	            	
	// 				echo "<div class='topicCon'>";

	// 				echo "<p>Please enter what the topic is for this Course.</p>";

	// 				echo "<input type='text' class='topicSelect' placeholder='Work Topic'>";



	// 				echo "<a onclick='topicSelect();'>Select</a>";

	// 				echo "</div>";


	// 				echo "\n<script>


	// 					function topicSelect(){

	// 						var topic = jQuery('.topicSelect').val();

	// 						var url = window.location.href;

	// 						if(topic != '')
	// 						{
	// 							console.log(url + '&topicType=' + topic );

	// 							window.location.replace(url + '&topicType=' + topic);

	// 						}




	// 					}


	// 				</script>

	// 				";
	// }
	

	
}


add_shortcode('AddToGoogleClassroom', 'clarendon_gapi_add_to_classroom');








?>