<?php
/*

Plugin Name: Clarendon D2L API Integration

Version: 1.0

Description: D2L API Integration Developed by Clarendon Technologies Inc.

Author: David MacNeill

License: GPL

*/

?>
<?php
define("CLARENDON_D2LAPI_DIR", $_SERVER['DOCUMENT_ROOT']. '/wp-content/plugins/clarendon-d2lapi/' );

//Admin Menu
function d2lapi_menu() {

	//$page = add_menu_page( "Conditions", "Conditions", 'manage_options', "condition_control", 'clarendon_condition_control_admin_view' );
	//add_action('admin_print_styles-' . $page, 'clarendon_condition_control_admin_style');
}

function clarendon_condition_d2lapi_admin_style() {

	//$src = CLARENDON_CC_DIR . 'clarendon_cc_admin.css';

	//wp_register_style('clarendon_cc-admin-style',$src); 

	//wp_enqueue_style('clarendon_cc-admin-style');

}

function clarendon_d2lapi_admin_view()
{
	global $wpdb;
        
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */

/*
function clarendon_d2lapi_authenticate()
{
	global $wpdb;

	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	


	//Set up the Scopes.

	$currentToken = get_user_meta(get_current_user_id(), "d2l_token", true);	

	$forceAuth = get_user_meta(get_current_user_id(), "d2l_force_auth", true);	


	if (isset($_GET['clear'])) {
		delete_user_meta(get_current_user_id(), "d2l_token");
		delete_user_meta(get_current_user_id(), "d2l_token_expiry");
		delete_user_meta(get_current_user_id(), "d2l_refresh_token");
	}
	else if (isset($currentToken) && $currentToken != ""){
		$accessToken = $currentToken;
		
		//set the access token.
		
		//get the courses
		
		//get the user profile

		//check how many courses - if none found, print no courses found.

		if (false) {
		  print "No courses found.\n";
		} else {
		  
		  
		  //For each course, get the course work.
		  
		}		  
	}
	else if (isset($_GET['code'])) {
		$authCode=$_GET['code'];
		
		//Get the access token from the auth code.
		
		if (!isset($currentToken) || empty($currentToken))
		{
			add_user_meta(get_current_user_id(), "d2l_token", $accessToken);
		}
		else {
			update_user_meta(get_current_user_id(), "d2l_token", $accessToken);
		}
		
		//set the access token.
		
		$client->setAccessToken($accessToken);	

		//get the courses




		die();


		echo "<script type=\"text/javascript\">";
		echo "window.location='/index.php/d2l-authorize/';";
		echo "</script>";


		if (false) {
		  print "No courses found.\n";
		} else {
		  print "Courses:\n";
		  //display the a link to the courses in this format:
		  //echo "<a href=\"/index.php/ms-teams-authorize/?add=true\" class=\"nectar-button large\">" . $course->getName() . "</a>";
		  
		}		
	}
	else
	{
		// If there is no previous token or it's expired.
		$tokenExpired = false;
		
		if ($tokenExpired) {

			update_user_meta(get_current_user_id(), "d2l_force_auth", 'false');


			// Refresh the token if possible, else fetch a new one.
			
			$getRefreshToken = false;
			
			if ($getRefreshToken) {
				//get access token from refresh token.
			} else {
				// Request authorization from the user.
				$authUrl = "";
				
				echo "<script type=\"text/javascript\">";
				echo "window.location='$authUrl';";
				echo "</script>";
				die();

			}
		}
	}	
	
}
*/
?>
<?php

//add_action('admin_menu','d2lapi_menu');

add_shortcode('D2LAPI', 'clarendon_d2lapi_add_to_brightspace');


add_shortcode('Add_To_Brightspace', 'addToBrightspace');

add_shortcode('CheckBrightspaceAccess', 'checkBrightspaceAccess');



function addToBrightspace() {
	
	//initiate the API and set the scopes.
	$currentToken = get_user_meta(get_current_user_id(), "d2l_token", true);	

	if(!is_user_logged_in() && isset($_GET['source']) && $_GET['source'] == "d2l"){

		setcookie("wordpress_isd2l", 'true', time() + (86400 * 30), '/');  

		setcookie ("wordpress_appView", "", time()-100 , '/' ); // past time

	}


	if(!is_user_logged_in() && isset($_GET['appView']) && $_GET['appView'] == "true"){

		setcookie("wordpress_appView", 'true', time() + (86400 * 30), '/');  
		// unset($_COOKIE['wordpress_isTeams']);
		// setcookie("wordpress_isTeams", "", time()-3600);

		setcookie ("wordpress_isd2l", "", time()-100 , '/' ); // past time


	}		
	
	
	if (isset($currentToken) && $currentToken != ""){
		
	//check for token expiry.
			
				//require_once CLARENDON_D2LAPI_DIR . 'oauth2-client/load.php';	
				
				$tokenExpired = true;
				$expiry = get_user_meta(get_current_user_id(), "d2l_token_expiry", true);
				if (isset($expiry)) {
					if ($expiry > time()) {
						$tokenExpired = false;
					}
				}
						
				$clientId = "";
				$clientSecret = "";
				$environmentUrl = "";		
				if (is_user_logged_in())
				{

					$schoolID = get_user_meta(get_current_user_id(), "d2l_school_id");
					if (!empty($schoolID))
					{
						
						$schools = $wpdb->get_results("SELECT * FROM wp_d2l_school where isValid=1 and ID=" . $schoolID . " order by School_Name ");
						foreach ($schools as $schools) {
							$clientId = $schools->ClientID;
							$clientSecret = $schools->ClientSecret;
							$environmentUrl = $schools->EnvironmentUrl;
						}
					}
				}					
			
				if ($tokenExpired && !empty($clientId)) {
							// Refresh the token if possible, else fetch a new one.
					$refreshToken = get_user_meta(get_current_user_id(), "d2l_refresh_token", true);

					if (isset($refreshToken) && strlen($refreshToken) > 0) {
								//we have a refresh token.
								
						$client = new \GuzzleHttp\Client();
						$form_params = array();
						//need to get this from each user.
						$form_params["client_id"] = $clientId;
								
						$form_params["scope"] = "content:file:read enrollment:orgunit:read grades:gradeobjects:write organizations:organization:read quizzing:quizzes:write users:userdata:read users:profile:read users:own_profile:read enrollments:own_enrollment:read content:modules:read dropbox:folders:read,write core:*:*";
						$form_params["refresh_token"] = $refreshToken;
						$form_params["redirect_uri"] = $redirectUri;
								
						$form_params["grant_type"] = "refresh_token";
						$form_params["client_secret"] = $clientSecret;

						$url = "https://auth.brightspace.com/oauth2/auth";

						$outcomeResponse = $client->request('POST',
							$url,
							array( 'form_params' => $form_params ),															
						);					
						$outcomeResult = json_decode( $outcomeResponse->getBody() );
						
						update_user_meta(get_current_user_id(), "d2l_token", $outcomeResult->access_token);
						
						$currentToken = $outcomeResult->access_token;
						
						update_user_meta(get_current_user_id(), "d2l_refresh_token", $outcomeResult->refresh_token);
						
						update_user_meta(get_current_user_id(), "d2l_token_expiry", time() + $outcomeResult->expires_in);
					}
				}

		
		
		//set the access token.
		//get the courses

		$isTeacher = false;

		$hasCourse = false;


	$headers = [
		'Authorization' => 'Bearer ' . $currentToken,
	];	

		
	$client = new \GuzzleHttp\Client();
	//get the user profile ID.
	

	$isTeacher = false;
	$teamId = "";

	//check to see if the user is a teacher.
	

		if(is_user_logged_in() && $isTeacher && $hasCourse)  {

			//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";
			//submitQuizToMSTeamsX();
		}

		if(is_user_logged_in() && !$isTeacher && $hasCourse){

			//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Student</strong></p>";
			//submitQuizToMSTeamsX();
		}


		if(is_user_logged_in() && !$hasCourse){

			if((isset($_GET['appView']) && $_GET['appView'] == "true") ||(isset($_SESSION["isTeach"]) && $_SESSION["isTeach"]) || (isset($_COOKIE["isTeach"]))){

				$_SESSION["isTeach"] = true;

				$resource= get_the_ID();


				//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";



				
				// echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";


				if(!isset($_COOKIE["isTrial"]) && empty($_COOKIE["isTrial"]) && !isset($_GET['trialView']) && empty($_GET["trialView"]))
				{
				}		

					//echo "BUTTON 1";


					echo "<a href=\"/d2l-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton brightspace-btn\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Brightspace\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/06/image.png\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to D2L - Brightspace</a>";					

			}
		}



		if(is_user_logged_in() && ($isTeacher || isset($_COOKIE["wordpress_appView"]))){
			$resource= get_the_ID();




				// echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";

				if(!isset($_COOKIE["isTrial"]) && empty($_COOKIE["isTrial"]) && !isset($_GET['trialView']) && empty($_GET["trialView"]))
				{
				}	

				// echo "BUTTON 2";


				echo "<a href=\"/d2l-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton brightspace-btn\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Brightspace\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/06/image.png\" width=\"24\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to D2L - Brightspace</a>";					

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
	else if($_GET['source'] == "" && !isset($_GET['source'])){

			if(is_user_logged_in())
			{
				$resource= get_the_ID();

				// echo "BUTTON 3";


			//	echo "<p  style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";


								echo "<a href=\"/d2l-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton brightspace-btn\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Brightspace\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/06/image.png\" width=\"24\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to D2L - Brightspace</a>";	


			}

		
	}
	else {
			if((isset($_GET['appView']) && $_GET['appView'] == "true") ||(isset($_SESSION["isTeach"]) && $_SESSION["isTeach"]) || (isset($_COOKIE["isTeach"]))){

				$_SESSION["isTeach"] = true;

				$resource= get_the_ID();


					echo "<a href=\"/d2l-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton brightspace-btn\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Brightspace\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/06/image.png\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to D2L - Brightspace</a>";					

			}		
	}
	//submitQuizToD2LX($quizdata, $user);

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




}

function checkBrightspaceAccess() {
	global $wpdb;
	return;
	if (is_user_logged_in())
	{
		if (!isset($_COOKIE['d2l_school_id'])) {
			
			$school = get_user_meta(get_current_user_id(), "d2l_school_id");
			if (isset($school)) {
				setcookie("d2l_school_id", $school, time() + (86400 * 30), '/');  
			}	
		}		
	}
	$clientId = "";
	$clientSecret = "";
	$environmentUrl = "";
	if(is_user_logged_in() && (isset($_GET['source']) && $_GET['source'] == "d2l"))
	{
		//initialize the API and set the scopes.

		require_once CLARENDON_D2LAPI_DIR . 'oauth2-client/load.php';		
		

		$schoolID = filter_var($_COOKIE["d2l_school_id"], FILTER_SANITIZE_STRING);
		$schools = $wpdb->get_results("SELECT * FROM wp_d2l_school where isValid=1 and ID=" . $schoolID . " order by School_Name ");
		foreach ($schools as $schools) {
			$clientId = $schools->ClientID;
			$clientSecret = $schools->ClientSecret;
			$environmentUrl = $schools->EnvironmentUrl;
		}
	

		//initialize the api and set the scopes.
		
		//$redirectUri = "http://localhost:8076/index.php/ms-teams-authorize/";
		$redirectUri = "https://chalkboardpublishing.com/d2l-authorize/";	
		//$redirectUri = "https://chalkboardpublishing.com/ms-teams-authorize/";	

		$oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
		  'clientId'                => $clientId,
		  'clientSecret'            => $clientSecret,
		  'redirectUri'             => $redirectUri,
		  'urlAuthorize'            => "https://auth.brightspace.com/oauth2/auth",
		  'urlAccessToken'          => "https://auth.brightspace.com/core/connect/token",
		  'urlResourceOwnerDetails' => '',
		  'scopes'                  => "content:file:read enrollment:orgunit:read grades:gradeobjects:write organizations:organization:read quizzing:quizzes:write users:userdata:read users:profile:read users:own_profile:read enrollments:own_enrollment:read content:modules:read dropbox:folders:* core:*:*"
		]);

		$currentToken = get_user_meta(get_current_user_id(), "d2l_token", true);
		$currentRedirect = get_user_meta(get_current_user_id(), "d2l_redirect", true);	
		$resource = get_the_ID();
		$permalink = get_permalink($resource);	
		if (!isset($currentRedirect) || empty($currentRedirect))
		{
			add_user_meta(get_current_user_id(), "d2l_redirect", $permalink);
		}
		else {
			update_user_meta(get_current_user_id(), "d2l_redirect", $permalink);
		}		

		
		
		if (isset($currentToken) && $currentToken != "") {

		}	
		else if (isset($_GET['code'])) {	
		
			$authCode=$_GET['code'];
			//get the access token from the auth code.
			
			$accessToken = "";
		    
			if (!isset($currentToken) || empty($currentToken))
			{
				add_user_meta(get_current_user_id(), "d2l_token", $accessToken);
			}
			else {
				update_user_meta(get_current_user_id(), "d2l_token", $accessToken);
			}	
		}
		else {

			if (isset($currentToken) && $currentToken != "") 
			{
				$tokenExpired = true;
				$expiry = get_user_meta(get_current_user_id(), "d2l_token_expiry", true);
				if (isset($expiry)) {
					if ($expiry > time()) {
						$tokenExpired = false;
					}
				}
						
						
						
				if ($tokenExpired) {
							// Refresh the token if possible, else fetch a new one.
					$refreshToken = get_user_meta(get_current_user_id(), "d2l_refresh_token", true);

					if (isset($refreshToken) && strlen($refreshToken) > 0) {
								//we have a refresh token.
								
						$client = new \GuzzleHttp\Client();
						$form_params = array();
						$form_params["client_id"] = $clientId;
								
						$form_params["scope"] = "content:file:read enrollment:orgunit:read grades:gradeobjects:write organizations:organization:read quizzing:quizzes:write users:userdata:read users:profile:read users:own_profile:read enrollments:own_enrollment:read content:modules:read dropbox:folders:* core:*:*";
						$form_params["refresh_token"] = $refreshToken;
						$form_params["redirect_uri"] = $redirectUri;
								
						$form_params["grant_type"] = "refresh_token";
						$form_params["client_secret"] = $clientSecret;

						$url = "https://auth.brightspace.com/core/connect/token";

						$outcomeResponse = $client->request('POST',
							$url,
							array( 'form_params' => $form_params ),															
						);					
						$outcomeResult = json_decode( $outcomeResponse->getBody() );

						update_user_meta(get_current_user_id(), "d2l_token", $outcomeResult->access_token);
						
						$currentToken = $outcomeResult->access_token;
						
						update_user_meta(get_current_user_id(), "d2l_refresh_token", $outcomeResult->refresh_token);
						
						update_user_meta(get_current_user_id(), "d2l_token_expiry", time() + $outcomeResult->expires_in);
					}
				}
			}
			else {
				$authUrl = $oauthClient->getAuthorizationUrl();
				//echo $authUrl;

				echo "<script type=\"text/javascript\">";
				echo "window.location='$authUrl';";
				echo "</script>";

				die();				
			}
			
		}

	}
	else if(!is_user_logged_in()){

		$current_url = "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];


		if(isset($_GET["source"]) && $_GET["source"] == "d2l")
		{

			 echo "<div style='text-align: center; margin-top: 40px; margin-bottom: 40px;'><h2>To view this assigned work, click \"Continue with Brightspace\" below</h2>";

			 echo "</div>";


		}else{

		}
		$resource = get_the_ID();
	    echo "<a href=\"/d2l-authorize/?resource=" . $resource . "&student=true\" class=\"addToClassroomButton brightspace-btn continue-brightspace\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Brightspace\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/06/image.png\" width=\"36\" style=\"width: 28px!important; float: left; margin-bottom: 0; margin-right: 10px;\"> Continue with Brightspace</a>";					


	//	echo do_shortcode( '[nextend_social_login trackerdata="source" redirect="'.$current_url.'" align="center"]' );

		// echo do_shortcode( '[TheChamp-Login]' );



		// die();
	}


	echo "<script>

		jQuery(document).ready(function(){

			if(jQuery('.continue-brightspace').parent().hasClass('elementor-element')){

				jQuery('.continue-brightspace').attr('style', 'padding: 9px; line-height: 24px; margin-right: 10px; position: absolute; right: 25%; bottom: 48px;');

			}else{

				jQuery('.continue-brightspace').attr('style', 'padding: 9px; line-height: 24px; margin-right: 10px; position: absolute; right: 35%; top: 59px;');

			}

		})

	</script>

	";

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

		.brightspace-btn{
		    background-color: #57a6d5!important;
		    border-radius: 30px!important;
		    color: #FFF!important;
		    font-weight: bold!important;
		    box-shadow: none!important;
		    font-size: 16px!important;			
		}	

		.brightspace-btn img{
			background-color: #FFF;
			border-radius: 50%;
			padding: 5px;
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

}

function clarendon_d2lapi_add_to_brightspace()
{
	global $wpdb;


	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

	try{

	require_once CLARENDON_D2LAPI_DIR . 'oauth2-client/load.php';		

		if (isset($_GET['resource']) && is_user_logged_in()) {



			echo "<script>";


			echo "function setCookie(cname, cvalue, exdays) {
					  var d = new Date();
					  d.setTime(d.getTime() + (exdays*24*60*60*1000));
					  var expires = \"expires=\"+ d.toUTCString();
					  document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
				}";


			echo "setCookie('wordpress_d2l_resource_id','".$_GET['resource']."',30);";	

		
			echo "</script>";


		
			if (!isset($resource) || empty($resource))
			{
				add_user_meta(get_current_user_id(), "d2l_resource", $_GET['resource']);

				$_SESSION["CUR_RESOURCE"] = $_GET['resource'];

			}
			else {
				update_user_meta(get_current_user_id(), "d2l_resource", $_GET['resource']);
			}
			$resource = $_GET['resource'];
		}


	//initialize the api and set the scopes.
	$clientId = ""; //"1991d2f1-9541-492b-a6dc-5e456247b507"
	$clientSecret = ""; //"trhwfvwRdhnPZzBaHAsvhnl47RNI-hYYUgJFKQcoQ4I"
	$baseUrl = ""; //https://chlkbrd.brightspacedemo.com
	//$redirectUri = "http://localhost:8076/index.php/ms-teams-authorize/";
	$redirectUri = "https://chalkboardpublishing.com/d2l-authorize/";	
	//$redirectUri = "https://chalkboardpublishing.com/ms-teams-authorize/";	

	/*
	FIGURE OUT WHAT SCHOOL THEY ARE WITH.  THAT WILL DETERMINE WHAT KEYS TO USE.
	*/

	if (isset($_GET['school'])) {

		setcookie("d2l_school_id", $_GET['school'], time() + (86400 * 30), '/'); 
		
		if (is_user_logged_in()) {
			$school = get_user_meta(get_current_user_id(), "d2l_school_id");
			if (!isset($school)) {
				update_user_meta(get_current_user_id(), "d2l_school_id", $_GET['school']);
			}
		}
	}
	else if (is_user_logged_in()) {
		$school = get_user_meta(get_current_user_id(), "d2l_school_id");
		if (isset($school)) {
			setcookie("d2l_school_id", $school, time() + (86400 * 30), '/');  
		}

	}

	if (isset($_POST['School_Name'])) {
			$schoolName = filter_var($_POST['School_Name'], FILTER_SANITIZE_STRING);
			$city = filter_var($_POST['City'], FILTER_SANITIZE_STRING);
			$province = filter_var($_POST['Province'], FILTER_SANITIZE_STRING);
			$schoolBoard = filter_var($_POST['SchoolBoard'], FILTER_SANITIZE_STRING);
			
			$clientId = filter_var($_POST['Client_ID'], FILTER_SANITIZE_STRING);
			$clientSecret = filter_var($_POST['Client_Secret'], FILTER_SANITIZE_STRING);
			$environmentUrl = filter_var($_POST['Environment_Url'], FILTER_SANITIZE_STRING);
			if (substr($environmentUrl, strlen($environmentUrl) - 1, 1) == "/") {
				$environmentUrl = substr($environmentUrl, 0, strlen($environmentUrl) - 1);
			}
			if (!empty($schoolName) && !empty($city) && !empty($province) && !empty($clientId) && !empty($clientSecret))
			{
				$school = $wpdb->get_results("SELECT ID, School_Name FROM wp_d2l_school where School_Name='" . $schoolName . "' AND City='" . $city . "' order by School_Name ");
				if (count($school) == 0)
				{
					$wpdb->insert( 'wp_d2l_school', array(
						'School_Name' => $schoolName,
						'City' => $city,
						'Province' => $province,
						'ClientID' => $clientId,
						'ClientSecret' => $clientSecret,
						'EnvironmentUrl' => $environmentUrl,
						'SchoolBoard' => $schoolBoard,
						'isValid' => 1
					) );
					$school = $wpdb->get_results("SELECT ID, School_Name FROM wp_d2l_school where School_Name='" . $schoolName . "' AND City='" . $city . "' order by School_Name ");
					foreach ($school as $school) {
						setcookie("d2l_school_id", $school->ID, time() + (86400 * 30), '/');  
					}
				}
				else {
					echo "<P>School already exists.</p>";
					return;
				}
			}
			else {
				echo "<p>Required field missing</p>";
				return;
			}
	}
	if(!isset($_COOKIE["d2l_school_id"])){
	    if (!isset($_GET['add']))
		{
			echo "<p style='width: 100%;'>Please select your school from the list below.</p>";
			foreach ($enrollmentResult->Items as $enrollment){
				if ($enrollment->OrgUnit->Type->Code != 'Course Offering') continue;
				if ($enrollment->Access->IsActive != 1) continue;
				if ($enrollment->Access->ClasslistRoleName != "Instructor") continue;												
			}
			$school = $wpdb->get_results("SELECT ID, School_Name FROM wp_d2l_school where isValid=1 order by School_Name ");

			echo "<div class='topicCon'>";
			echo "<script type=\"text/javascript\">";
			echo "function setCookieX(cname, cvalue, exdays) {
				  var d = new Date();
	
				  d.setTime(d.getTime() + (exdays*24*60*60*1000));
				  var expires = \"expires=\"+ d.toUTCString();
				  document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
			}";
			echo "function getCookieX(cname) {
				  let name = cname + \"=\";
				  let decodedCookie = decodeURIComponent(document.cookie);
				  let ca = decodedCookie.split(';');
				  for(let i = 0; i <ca.length; i++) {
					let c = ca[i];
					while (c.charAt(0) == ' ') {
					  c = c.substring(1);
					}
					if (c.indexOf(name) == 0) {
					  return c.substring(name.length, c.length);
					}
				  }
				  return \"\";
				}
				";
			
			echo "function selectSchool(resource) {
				if (document.getElementById('schoolSelect').selectedIndex > 0) {
					var rand = Math.floor(Math.random() * 1000);
					setCookieX('d2l_school_id', document.getElementById('schoolSelect').value, 30);
					window.location = '/d2l-authorize/?resource=' + resource + '&school=' + document.getElementById('schoolSelect').value + '&v=' + rand;
				}
			}";
			echo "function addNewSchool(resource) {

				window.location = '/d2l-authorize/?resource=' + resource + '&add=true';
			}";			
			if (isset($_GET['resource'])) {
				echo "setCookieX('wordpress_d2l_resource_id', " . $_GET['resource'] . ", 30);";
			}
			echo "</script>";
			echo "<select id='schoolSelect' class='ClassroomSelect'>";		
			echo "<option value='0'>[Select School]</option>";
			foreach ($school as $school) {
				echo "<option value='" . $school->ID . "'>" . $school->School_Name . "</option>";
			}
			
			echo "</select>";
			$resource = filter_var($_GET['resource'], FILTER_SANITIZE_STRING);
			
			echo "<a onclick='javascript:selectSchool($resource);'>Select</a>";
			echo "<p style='margin-top: 40px; width: 100%;'>Can't find your school?  If you are a D2L Adminstrator for your school, register your school below.  Otherwise, contact your school administrator for assistance.</p>";
			echo "<p style='text-align:center; width: 100%;'><a onclick='javascript:addNewSchool($resource);' style='width: auto;'>Add a School</a></p>";
			echo "</div>";
			return;
		}
		else {
			echo "<div class='topicCon'>";
			echo "<H3>Add a School</h3>";
			echo "<form method=\"post\" action=\"/d2l-authorize/?resource=$resource\">";
			echo "<p>You must be an administrator of your school's D2L environment in order to add your school.</p>";
			echo "<input type='text' name='School_Name' placeholder='School Name' ID='School_Name' style='margin-bottom: 15px; width: 100%;'>";
			echo "<input type='text' name='City' placeholder='City' ID='City' style='margin-bottom: 15px; width: 100%;'>";
			echo "<input type='text' name='Province' placeholder='Province' ID='Province' style='margin-bottom: 15px; width: 100%;'>";
			echo "<input type='text' name='SchoolBoard' placeholder='School Board' ID='SchoolBoard' style='margin-bottom: 15px; width: 100%;'>";
			echo "<input type='text' name='Environment_Url' placeholder='Environment Url' ID='Environment_Url' style='margin-bottom: 15px; width: 100%;'>";
			echo "<input type='text' name='Client_ID' placeholder='Client ID' ID='Client_ID' style='margin-bottom: 15px; width: 100%;'>";
			echo "<input type='text' name='Client_Secret' placeholder='Client Secret' ID='Client_Secret' style='margin-bottom: 15px; width: 100%;'>";
			echo "<p style='text-align:center; width: 100%;'><input type='submit' value='Add School'></p>";
			echo "</form>";
			echo "</div>";
			return;
		}
	}
	else {
		$schoolID = filter_var($_COOKIE["d2l_school_id"], FILTER_SANITIZE_STRING);
		
		$school = $wpdb->get_results("SELECT * FROM wp_d2l_school where isValid=1 and ID=" . $schoolID . " order by School_Name ");
		foreach ($school as $school) {
			$clientId = $school->ClientID;
			$clientSecret = $school->ClientSecret;
			$environmentUrl = $school->EnvironmentUrl;
		}

	}
	if (empty($clientId)) {
		echo "<p>School Not Found.</p>";
		return;
	}
	
	$scopes = "content:file:read enrollment:orgunit:read grades:gradeobjects:write organizations:organization:read quizzing:quizzes:write users:userdata:read users:profile:read users:own_profile:read enrollments:own_enrollment:read content:modules:read dropbox:folders:* core:*:*";
	if ($_GET['student'] == 'true')
	{
		$scopes = "users:userdata:read users:profile:read users:own_profile:read enrollment:orgunit:read";
	}
	
	$oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
      'clientId'                => $clientId,
      'clientSecret'            => $clientSecret,
      'redirectUri'             => $redirectUri,
      'urlAuthorize'            => "https://auth.brightspace.com/oauth2/auth",
      'urlAccessToken'          => "https://auth.brightspace.com/core/connect/token",
      'urlResourceOwnerDetails' => '',
      'scopes'                  => $scopes
    ]);
	
	$currentToken = get_user_meta(get_current_user_id(), "d2l_token", true);
	if (isset($currentToken) && $currentToken != "") 
	{
		$tokenExpired = true;
		$expiry = get_user_meta(get_current_user_id(), "d2l_token_expiry", true);
		if (isset($expiry)) {
			if ($expiry > time()) {
				$tokenExpired = false;
			}
		}
				
				
		if ($tokenExpired ) {
					// Refresh the token if possible, else fetch a new one.
			$refreshToken = get_user_meta(get_current_user_id(), "d2l_refresh_token", true);

			if (isset($refreshToken) && strlen($refreshToken) > 0) {
						//we have a refresh token.
				$client = new \GuzzleHttp\Client();
				$form_params = array();
				$form_params["client_id"] = $clientId;
						
				$form_params["scope"] = "content:file:read enrollment:orgunit:read grades:gradeobjects:write organizations:organization:read quizzing:quizzes:write users:userdata:read users:profile:read users:own_profile:read enrollments:own_enrollment:read content:modules:read dropbox:folders:* core:*:*";
				$form_params["refresh_token"] = $refreshToken;
				$form_params["redirect_uri"] = $redirectUri;
						
				$form_params["grant_type"] = "refresh_token";
				$form_params["client_secret"] = $clientSecret;

				$url = "https://auth.brightspace.com/core/connect/token";

				$outcomeResponse = $client->request('POST',
					$url,
					array( 'form_params' => $form_params ),															
				);					
				$outcomeResult = json_decode( $outcomeResponse->getBody() );
				
				update_user_meta(get_current_user_id(), "d2l_token", $outcomeResult->access_token);
				
				$currentToken = $outcomeResult->access_token;
				
				update_user_meta(get_current_user_id(), "d2l_refresh_token", $outcomeResult->refresh_token);
				
				update_user_meta(get_current_user_id(), "d2l_token_expiry", time() + $outcomeResult->expires_in);

			}
		}
	}

	
	
	$resource = get_user_meta(get_current_user_id(), "d2l_resource", true);

	$currentRedirect = get_user_meta(get_current_user_id(), "d2l_redirect", true);	

	$forceAuth = get_user_meta(get_current_user_id(), "d2l_auth", true);	


		if (isset($_GET['clear'])) {
			delete_user_meta(get_current_user_id(), "d2l_token");
			delete_user_meta(get_current_user_id(), "d2l_resource");	
			delete_user_meta(get_current_user_id(), "d2l_token_expiry");		
			delete_user_meta(get_current_user_id(), "d2l_redirect");

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
				if ($_GET['student'] == 'true'){
					$currentRedirect = get_permalink($resource);
					echo "<script type=\"text/javascript\">";
					echo "window.location='$currentRedirect';";
					echo "</script>";	
					die();
				}
				else if((isset($_GET['topicType']) && !empty($_GET['topicType'])))
				{
					$accessToken = $currentToken;
					//get user data
					$headers = [
						'Authorization' => 'Bearer ' . $currentToken,
					];	

					$client = new \GuzzleHttp\Client();
					//set the access token.
					
					//initialize the API
					
					//get the courses
					
					//if the course ID is not equal to the $_GET['classRoomID'] continue
					
					//check to see if the user is the teacher.
					
					//get the permalink to the resource
					$permalink = get_permalink($resource);

					$currentRedirect = get_user_meta(get_current_user_id(), "d2l_redirect", true);

					if (!isset($currentRedirect) || empty($currentRedirect))
					{
						add_user_meta(get_current_user_id(), "d2l_redirect", $permalink);
					}
					else {
						update_user_meta(get_current_user_id(), "d2l_redirect", $permalink);
					}

					$currentRedirect = get_user_meta(get_current_user_id(), "d2l_redirect", true);

					$postTitle = get_the_title($resource);


					
					//create a new assignment in D2L
					$url = $environmentUrl . '/d2l/api/lp/1.26/enrollments/myenrollments/';
					$enrollmentResponse = $client->request(
						'GET',
						$url,
						array( 'headers' => $headers )
					);	
					$enrollmentResult = json_decode( $enrollmentResponse->getBody() );	
					$found = false;				
					$enrollmentId = 0;
					foreach ($enrollmentResult->Items as $enrollment){
						if ($enrollment->OrgUnit->Type->Code != 'Course Offering') continue;
						if ($enrollment->Access->IsActive != 1) continue;
						if ($enrollment->Access->ClasslistRoleName != "Instructor") continue;	
						if ($enrollment->OrgUnit->Code != $_GET['teamID']){
							continue;
						}
						
						add_user_meta(get_current_user_id(), "d2l_ID", $enrollment->OrgUnit->Id);
						$enrollmentId = $enrollment->OrgUnit->Id;
						echo "<p><strong>\"". $postTitle ."\"</strong> has been shared with your <strong>". $enrollment->OrgUnit->Name . "</strong> course on Brightspace.</p>";
						echo "<a href=\"".$currentRedirect."\" class=\"nectar-button large\"> Return to Activity </a> ";

						$url = $environmentUrl . '/d2l/api/le/1.51/' . $enrollment->OrgUnit->Id . '/dropbox/folders/';
						delete_user_meta(get_current_user_id(), "d2l_resource");
						$dropboxResponse = $client->request(
							'GET',
							$url,
							array( 'headers' => $headers )
						);
						$dropboxResult = json_decode( $dropboxResponse->getBody() );
						
						foreach ($dropboxResult as $dropbox) {
						
							foreach ($dropbox->LinkAttachments as $link)
							{
								if ($link->Href == $permalink) {
									$found = true;
								}

							}
						}
						
					}
		

					if (!$found){
						//assignment doesn't already exist.  Create it.
						$url = $environmentUrl . '/d2l/api/le/1.51/' . $enrollmentId . '/dropbox/folders/';
						
						$form_params = array();
						//need to get this from each user.



						$excerpt = get_the_excerpt($resource);

						$type = get_post_type($resource);

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

							$resource_title = esc_html(get_the_title($courseID));

							$quizs = get_post($resource);								

						}							
						else{

							$courseID = get_post_meta( $resource, 'course_id', true);

							$resource_title = esc_html(get_the_title($courseID));

							$args = array(
								'post_type' => 'sfwd-quiz',
								'posts_per_page' => -1,
								'meta_key' => 'lesson_id', 
								'meta_value' => $resource
							);	

							$quizs = get_posts($args);	
						}

						$pointTotal = 10;
										
						if(!is_object($quizs) && count($quizs) > 0)
						{
							foreach ($quizs as $quiz) {

								if($type == "sfwd-lessons"){

									$permalink = get_post_permalink($quiz->ID) . "?source=d2l";

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

								$title = $resource_title . '-' . $quiz->post_title;

								$title = str_replace('&#8211;', '-', $title);

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


							$title = $resource_title . '-' . $quizs->post_title;

							$title = str_replace('&#8211;', '-', $title);
					
						}
						else{

							$title = str_replace('&#8211;', '-', get_the_title($resource));
		
						}
						
						$form_params["Name"] = $title;
						$instructions = array();
						//$instructions["Text"] = $excerpt . '\n\nClick Here to complete the activity: ' . $permalink . "&source=d2l";
						$instructions["Content"] = $excerpt . '<br/><br/>Click Here to complete the activity:<br/><a href=' . $permalink . '?source=d2l' . ' target=_blank>' . $title . '</a>';
						$instructions["Type"] = 'Html';

						$form_params["CustomInstructions"] = $instructions;
						$availability = array();
						$availability["StartDate"] = str_replace("+00:00", ".000Z", date(DATE_ATOM, mktime(0, 0, 0, date("m"), date("d"), date("Y"))));
						$availability["EndDate"] = str_replace("+00:00", ".000Z", date(DATE_ATOM, mktime(0, 0, 0, date("m"), date("d")+7, date("Y"))));
						//$form_params["Availability"] = $availability;
						$form_params["DueDate"] = str_replace("+00:00", ".000Z", date(DATE_ATOM, mktime(0, 0, 0, date("m"), date("d")+7, date("Y"))));

						$form_params["IsHidden"] = false;
						$form_params["IsAnonymous"] = false;
						
						$form_params["AllowOnlyUsersWithSpecialAccess"] = false;
						$form_params["DisplayOnCalendar"] = true;
						
						$form_params["SubmissionType"] = 0;
						
						$form_params["CompletionType"] = 0;
						
						$assessment = array();
						if (empty($pointTotal)) $pointTotal = 20;
						$assessment["ScoreDenominator"] = $pointTotal;
						//$form_params["NotificationEmail"] = "david@clarendontech.com"; //Teacher's Email
						$form_params["Assessment"] = $assessment;
						$linkAttachments = array(); 
						$link = array();
					
						$link["Href"] = $permalink . "&source=d2l";
						$link["LinkName"] = $title;
						$linkAttachments[0] = $link;
						$form_params["LinkAttachments"] = $linkAttachments;

						$form_params = json_encode($form_params);
						$form_params = str_replace("\\/", "/", $form_params);
						
						$dropboxResponse = $client->request('POST',
							$url,
							array( 'body' => $form_params, 'headers' => $headers ),															
						);		
						$dropboxResult = json_decode( $dropboxResponse->getBody() );						
						
					}						
		
				}
				else{
						// if no topic is set show topic textbox
						echo "<div class='topicCon'>";


					$accessToken = $currentToken;
					//get user data
					$headers = [
						'Authorization' => 'Bearer ' . $currentToken,
					];	

					$client = new \GuzzleHttp\Client();
					$myId = "";
					$meResponse = $client->request(
						'GET',
						$environmentUrl . '/d2l/api/lp/1.10/users/whoami',
						array( 'headers' => $headers )
					);	
					$meResult = json_decode( $meResponse->getBody() );	

					$myId = $meResult->ProfileIdentifier;
					$url = $environmentUrl . '/d2l/api/lp/1.10/profile/' . $myId;
					$profileResponse = $client->request(
						'GET',
						$url,
						array( 'headers' => $headers )
					);	
					$profileResult = json_decode( $profileResponse->getBody() );	
					
					
					$url = $environmentUrl . '/d2l/api/lp/1.26/enrollments/myenrollments/';
					$enrollmentResponse = $client->request(
						'GET',
						$url,
						array( 'headers' => $headers )
					);	
					$enrollmentResult = json_decode( $enrollmentResponse->getBody() );
					echo "<p style='width: 100%;'>Please enter the topic and course for this resource.</p>";
					foreach ($enrollmentResult->Items as $enrollment){
						if ($enrollment->OrgUnit->Type->Code != 'Course Offering') continue;
						if ($enrollment->Access->IsActive != 1) continue;
						if ($enrollment->Access->ClasslistRoleName != "Instructor") continue;												
					}
					echo "<select class='ClassroomSelect'>";
					echo "<option value=''>Select Course</option>";						
					foreach ($enrollmentResult->Items as $enrollment){
						if ($enrollment->OrgUnit->Type->Code != 'Course Offering') continue;
						if ($enrollment->Access->IsActive != 1) continue;
						if ($enrollment->Access->ClasslistRoleName != "Instructor") continue;
						
						echo "<option value=\"" .  $enrollment->OrgUnit->Code . "\">" . $enrollment->OrgUnit->Name . "</option>";
												
					}
					echo "</select>";
					
					//get classes
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
									if (url.indexOf('?') > 0) {
										window.location.replace(url + '&topicType=' + topic + '&teamID=' + ClassroomSelect);
									}
									else window.location.replace(url + '?topicType=' + topic + '&teamID=' + ClassroomSelect);

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
			
			//get the access token from the auth code.
			$accessToken = $oauthClient->getAccessToken('authorization_code', [
				'code' => $authCode
			]);

			$headers = [
					'Authorization' => 'Bearer ' . $accessToken,
			];
		

			$client = new \GuzzleHttp\Client();

			if (!is_user_logged_in()) {

				//get the logged in user.

				$mail = "";
				$userName = "";
				$fName = "";
				$lName = "";
				
					$myId = "";
					$meResponse = $client->request(
						'GET',
						$environmentUrl . '/d2l/api/lp/1.10/users/whoami',
						array( 'headers' => $headers )
					);	
					$meResult = json_decode( $meResponse->getBody() );	



				if (is_array($meResult))
				{
					foreach ($meResult as $me){
						$mail = $me[0]->Email;
						$userName = $meResult->ProfileIdentifier;
						$fName = $me[0]->FirstName;
						$lName = $me[0]->LastName;

					}
				}
				else {
					$mail = $meResult->Email;
					$userName = $meResult->ProfileIdentifier;
					$fName = $meResult->FirstName;
					$lName = $meResult->LastName;


				}
				if (empty($mail)) {
					$myId = $meResult->ProfileIdentifier;
					$url = $environmentUrl . '/d2l/api/lp/1.10/profile/' . $myId;
					$profileResponse = $client->request(
						'GET',
						$url,
						array( 'headers' => $headers )
					);	
					$meResult = json_decode( $profileResponse->getBody() );
					$mail = $meResult->Email;
					if (empty($mail)) {
						$mail = $userName . '@chalkboardpublishing.com';
					}
				}

				if($mail != ""){

					$exists = email_exists($mail);

				}

				if($exists){
					// sign in user

					$user = get_user_by( 'email', $mail);
					
				    wp_set_auth_cookie($user->ID, false, is_ssl());



				}else{

					//echo $userName . " " . $mail . " " . $fName . " " . $lName;
				    $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
	
				    $user_id = wp_create_user($userName, $random_password, $mail);	
					
				    $user_data = wp_update_user( array( 'ID' => $user_id, 'user_email' => $mail, 'first_name' => $fName, 'last_name' => $lName ) );				

				    wp_set_auth_cookie($user_id, false, is_ssl());

					//create user and sign in

				}


				// echo "CUR RESOURCE " . $_SESSION["resourceID"];
				// session_start();
				$permalink = "";
				
				if(isset($_COOKIE["wordpress_d2l_resource_id"])){

					// $currentRedirect =

					$permalink = get_permalink($_COOKIE['wordpress_d2l_resource_id']);

				}

				if(isset($_COOKIE["wordpress_quizID"]) && isset($_COOKIE["wordpress_userID"])){


					$permalink = "/assignment-answers/?uid=".$_COOKIE["wordpress_userID"]."&quid=" . $_COOKIE["wordpress_quizID"];

					setcookie ("wordpress_quizID", "", time()-100 , '/' ); // past time	

					setcookie ("wordpress_userID", "", time()-100 , '/' ); // past time						

				}				


				if(isset($permalink) && isset($_COOKIE["wordpress_isd2l"])){

					$currentRedirect = $permalink;

				}
				else if(isset($permalink) && isset($_COOKIE["wordpress_appView"])){

					$currentRedirect = $permalink;

				}
				if (isset($_COOKIE['isTeach'])) {

					 $currentRedirect = "/d2l-authorize/?resource=" . $_COOKIE['wordpress_d2l_resource_id'];

					//$currentRedirect = $permalink;


				}
				
		


				//get user by email
				//if exists wp_set_auth_cookie
				//else wp_create_user and set wp_set_auth_cookie
			}


			



			if (!isset($currentToken) || empty($currentToken))
			{
				add_user_meta(get_current_user_id(), "d2l_token", strval($accessToken->getToken()));
				add_user_meta(get_current_user_id(), "d2l_refresh_token", $accessToken->getRefreshToken());
				add_user_meta(get_current_user_id(), "d2l_token_expiry", $accessToken->getExpires());
			}
			else {
				update_user_meta(get_current_user_id(), "d2l_token", strval($accessToken->getToken()));
				update_user_meta(get_current_user_id(), "d2l_refresh_token", $accessToken->getRefreshToken());
				update_user_meta(get_current_user_id(), "d2l_token_expiry", $accessToken->getExpires());
			}
			if ($currentRedirect != "")
			{
					echo "<script type=\"text/javascript\">";
					echo "window.location='$currentRedirect';";
					echo "</script>";
					die();		
			}
			else
			{
				//display a list of courses for the user to pick to authorize.

					echo "<script type=\"text/javascript\">";
					echo "window.location='$redirectUri';";
					echo "</script>";
					die();
				
			}		
		}
		else
		{
	    // Load previously authorized token from a file, if it exists.
	    // The file token.json stores the user's access and refresh tokens, and is
	    // created automatically when the authorization flow completes for the first
	    // time.
			if($forceAuth == "true"){
				delete_user_meta(get_current_user_id(), "d2l_token");
				
			}


			update_user_meta(get_current_user_id(), "d2l_force_auth", 'false');

			delete_user_meta(get_current_user_id(), "d2l_teams_redirect");
		

			$authUrl = $oauthClient->getAuthorizationUrl();

			// $authUrl .= "&resourceID=" . $_GET['resource'];
					
			// Request authorization from the user.

			echo "<script type=\"text/javascript\">";
			
			echo "window.location='$authUrl';";
			echo "</script>";
			die();

			//echo $authUrl . "";
		}	
		

		}
		catch(Exception $e){
			print_r($e);
			echo "<p><strong>Sorry, we were unable to process your request. Have you tried:</strong><p>
			<ul>
				<li>Making sure you are logged into your email address associated with your Microsoft Teams?</li>
				<li>Closing down excess tabs in your Web Browser?</li>
			</ul>";


			if (strpos($e, 'PERMISSION_DENIED') !== false) {
			    // echo 'true';

			    delete_user_meta(get_current_user_id(), "d2l_token");
			}					
		}



}



//add_shortcode('AddToBrightspace', 'clarendon_d2lapi_add_to_brightspace');







?>