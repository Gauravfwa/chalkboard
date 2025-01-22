<?php

/*

Plugin Name: Clarendon Microsoft Teams API Integration

Version: 1.0

Description: Microsoft Teams API Integration Developed by Clarendon Technologies Inc.

Author: David MacNeill

License: GPL

*/

?>
<?php
define("CLARENDON_MSAPI_DIR", $_SERVER['DOCUMENT_ROOT']. '/wp-content/plugins/clarendon-msapi/' );

//Admin Menu
function msapi_menu() {

	//$page = add_menu_page( "Conditions", "Conditions", 'manage_options', "condition_control", 'clarendon_condition_control_admin_view' );
	//add_action('admin_print_styles-' . $page, 'clarendon_condition_control_admin_style');
}

function clarendon_condition_msapi_admin_style() {

	//$src = CLARENDON_CC_DIR . 'clarendon_cc_admin.css';

	//wp_register_style('clarendon_cc-admin-style',$src); 

	//wp_enqueue_style('clarendon_cc-admin-style');

}

function clarendon_msapi_admin_view()
{
	global $wpdb;
        
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */


function clarendon_msapi_authenticate()
{
	global $wpdb;

	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	


	//Set up the Scopes.

	$currentToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);	

	$forceAuth = get_user_meta(get_current_user_id(), "ms_force_auth", true);	


	if (isset($_GET['clear'])) {
		delete_user_meta(get_current_user_id(), "ms_teams_token");
		delete_user_meta(get_current_user_id(), "ms_teams_token_expiry");
		delete_user_meta(get_current_user_id(), "ms_teams_refresh_token");
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
			add_user_meta(get_current_user_id(), "ms_teams_token", $accessToken);
		}
		else {
			update_user_meta(get_current_user_id(), "ms_teams_token", $accessToken);
		}
		
		//set the access token.
		
		$client->setAccessToken($accessToken);	

		//get the courses




		die();


		echo "<script type=\"text/javascript\">";
		echo "window.location='/index.php/ms-teams-auth/';";
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

			update_user_meta(get_current_user_id(), "ms_force_auth", 'false');


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

?>
<?php

add_action('admin_menu','msgapi_menu');

add_shortcode('MSTeamsAPI', 'clarendon_msapi_add_to_teams');


add_shortcode('Add_To_MS_Teams', 'addToTeamsButton');

add_shortcode('CheckMicrosoftAccess', 'checkMicrosoftAccess');

function curl($url, $cookie = false, $post = false, $header = false, $follow_location = false, $referer=false,$proxy=false)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);
    if ($cookie) {
        curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
    }
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $response = curl_exec ($ch);
	if(curl_errno($ch))
		$response = 'Curl error: '.curl_error($ch);	
    curl_close($ch);
    return $response;
}

function addToTeamsButton() {

	//initiate the API and set the scopes.
	$currentToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);	


	if(!is_user_logged_in() && isset($_GET['source']) && $_GET['source'] == "teams"){

		setcookie("wordpress_isTeams", 'true', time() + (86400 * 30), '/');  /* expire in 1 day */
		// unset($_COOKIE['wordpress_appView']);

		setcookie ("wordpress_appView", "", time()-100 , '/' ); // past time

		// setcookie("wordpress_appView", "", time()-3600);

	}


	if(!is_user_logged_in() && isset($_GET['appView']) && $_GET['appView'] == "true"){

		setcookie("wordpress_appView", 'true', time() + (86400 * 30), '/');  /* expire in 1 day */
		// unset($_COOKIE['wordpress_isTeams']);
		// setcookie("wordpress_isTeams", "", time()-3600);

		setcookie ("wordpress_isTeams", "", time()-100 , '/' ); // past time


	}		

	
	if (isset($currentToken) && $currentToken != ""){
		
	//check for token expiry.
	
				require_once CLARENDON_MSAPI_DIR . 'oauth2-client/load.php';	
					
				$tokenExpired = true;
				$expiry = get_user_meta(get_current_user_id(), "ms_teams_token_expiry", true);
				if (isset($expiry)) {
					if ($expiry > time()) {
						$tokenExpired = false;
					}
				}
						
						
					
								
				if ($tokenExpired) {
							// Refresh the token if possible, else fetch a new one.
					$refreshToken = get_user_meta(get_current_user_id(), "ms_teams_refresh_token", true);

					if (isset($refreshToken) && strlen($refreshToken) > 0) {
								//we have a refresh token.
						
						$client = new \GuzzleHttp\Client();
						
						$form_params = array();
						$form_params["client_id"] = "b984d8f3-78d7-4640-94ec-74cad74527b0";
								
						$form_params["scope"] = "openid profile offline_access user.read EduAssignments.ReadWrite EduRoster.ReadWrite";
						$form_params["refresh_token"] = $refreshToken;
						$form_params["redirect_uri"] = $redirectUri;
								
						$form_params["grant_type"] = "refresh_token";
						$form_params["client_secret"] = "JBw8Q~ZB4_MC0SbT.64pNptjLLNlvgeiXTDVhbc-";

						$url = "https://login.microsoftonline.com/common/oauth2/v2.0/token";
						
						try
						{
							$outcomeResponse = $client->request('POST',
								$url,
								array( 'form_params' => $form_params ),															
							);					
							$outcomeResult = json_decode( $outcomeResponse->getBody() );
							
							update_user_meta(get_current_user_id(), "ms_teams_token", $outcomeResult->access_token);
							
							$currentToken = $outcomeResult->access_token;
							
							update_user_meta(get_current_user_id(), "ms_teams_refresh_token", $outcomeResult->refresh_token);
							
							update_user_meta(get_current_user_id(), "ms_teams_token_expiry", time() + $outcomeResult->expires_in);
						}
						catch(Exception $e){
							print_r($e);
						}
					}
				}
			
		
		
		//set the access token.
		//get the courses

		$isTeacher = false;

		$hasCourse = false;


		//for each course, get the teachers to see if the current user is a teacher.
		/*
		echo "1";
		require CLARENDON_MSAPI_DIR . 'src/Graph.php';	
		echo "2";
		$graph = new Graph();
		echo "3";
		$graph->setAccessToken($currentToken);
		echo "4";
		*/
	/*
	$headers = [
		'Authorization' => 'Bearer ' . $currentToken,
	];
	$server_output = curl("https://graph.microsoft.com/v1.0/me", false, false, $headers);
	echo "OUTPUT";
	print_r($server_output);
	*/
	/*
	$header = [
		'Authorization' => 'Bearer ' . $currentToken,
	];	
	$url = "https://graph.microsoft.com/v1.0/me";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, $header);

    $response = curl_exec ($ch);
	if(curl_errno($ch))
		$response = 'Curl error: '.curl_error($ch);	
    curl_close($ch);	
	echo $response;
	*/
	$headers = [
		'Authorization' => 'Bearer ' . $currentToken,
	];	


	$client = new \GuzzleHttp\Client();
	$myId = "";
	$meResponse = $client->request(
		'GET',
		'https://graph.microsoft.com/v1.0/me',
		array( 'headers' => $headers )
	);	
	
	$meResult = json_decode( $meResponse->getBody() );	


	if (is_array($meResult))
	{
		foreach ($meResult as $me){
			$myId = $me[0]->id;
		}
	}
	else {
		$myId = $meResult->id;
	}

	$response = $client->request(
		'GET',
		'https://graph.microsoft.com/v1.0/education/classes',
		array( 'headers' => $headers )
	);	
	

	
	$result = json_decode( $response->getBody() );
	if (!is_array($result)) $result = $result->value;
	$isTeacher = false;
	$teamId = "";
	foreach ($result as $team) {

		

			if (is_array($team)) {
				$teamId = $team[0]->id;
			}
			else $teamId = $team->id;
			
			//is the user a teacher of this class?

				if (!empty($teamId))
				{
					$teacherResponse = $client->request(
					'GET',
					'https://graph.microsoft.com/v1.0/education/classes/' . $teamId . '/teachers',
						array( 'headers' => $headers )
					);
			
					$teacherResult = json_decode( $teacherResponse->getBody() );
					$hasCourse = true;
					
					
					foreach ($teacherResult as $teacher) {
						foreach ($teacher as $teach)
						{
							if ($teach->id == $myId) {
								$isTeacher = true;
							}
						}
					}
				
				}

	}
	

		if(is_user_logged_in() && $isTeacher && $hasCourse)  {

			//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";
			//submitQuizToMSTeamsX();
		}

		if(is_user_logged_in() && !$isTeacher && $hasCourse){

			//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Student</strong></p>";
			//submitQuizToMSTeamsX();
		}
		$buttonAdded = false;

		if(is_user_logged_in() && $hasCourse){

			if((isset($_GET['appView']) && $_GET['appView'] == "true") ||(isset($_SESSION["isTeach"]) && $_SESSION["isTeach"]) || (isset($_COOKIE["isTeach"]))){

				$_SESSION["isTeach"] = true;

				$resource= get_the_ID();


				//echo "<p style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";



				
				// echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";


				if(!isset($_COOKIE["isTrial"]) && empty($_COOKIE["isTrial"]) && !isset($_GET['trialView']) && empty($_GET["trialView"]))
				{
				}		

					// echo "BUTTON 1";

					echo "<a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Microsoft Teams\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/04/teams_logo.png\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to Microsoft Teams</a>";	
					$buttonAdded = true;

					//echo "<a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Microsoft Teams\" src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMTAyNCAxMDI0Ij4KICAgICAgPGRlZnM+CiAgICAgICAgPGxpbmVhckdyYWRpZW50IGlkPSJwbGF0ZS1maWxsIiB4MT0iLS4yIiB5MT0iLS4yIiB4Mj0iLjgiIHkyPSIuOCI+CiAgICAgICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM1YTYyYzQiPjwvc3RvcD4KICAgICAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzM5NDBhYiI+PC9zdG9wPgogICAgICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICAgICAgPHN0eWxlPgogICAgICAgICAgLmNscy0xe2ZpbGw6IzUwNTljOX0uY2xzLTJ7ZmlsbDojN2I4M2VifQogICAgICAgIDwvc3R5bGU+CiAgICAgICAgPGZpbHRlciBpZD0icGVyc29uLXNoYWRvdyIgeD0iLTUwJSIgeT0iLTUwJSIgd2lkdGg9IjMwMCUiIGhlaWdodD0iMzAwJSI+CiAgICAgICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI1Ij48L2ZlR2F1c3NpYW5CbHVyPgogICAgICAgICAgPGZlT2Zmc2V0IGR5PSIyNSI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgICA8ZmVGdW5jQSB0eXBlPSJsaW5lYXIiIHNsb3BlPSIuMjUiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKCiAgICAgICAgPGZpbHRlciBpZD0iYmFjay1wbGF0ZS1zaGFkb3ciIHg9Ii01MCUiIHk9Ii01MCUiIHdpZHRoPSIzMDAlIiBoZWlnaHQ9IjMwMCUiPgogICAgICAgICAgCgk8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI0Ij48L2ZlR2F1c3NpYW5CbHVyPgoJICA8ZmVPZmZzZXQgZHg9IjIiIGR5PSIyNCI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjYiPjwvZmVGdW5jQT4KCiAgICAgICAgICA8L2ZlQ29tcG9uZW50VHJhbnNmZXI+CiAgICAgICAgICA8ZmVNZXJnZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlPjwvZmVNZXJnZU5vZGU+CiAgICAgICAgICAgIDxmZU1lcmdlTm9kZSBpbj0iU291cmNlR3JhcGhpYyI+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgIDwvZmVNZXJnZT4KICAgICAgICA8L2ZpbHRlcj4KICAgICAgICA8ZmlsdGVyIGlkPSJ0ZWUtc2hhZG93IiB4PSItNTAlIiB5PSItNTAlIiB3aWR0aD0iMjUwJSIgaGVpZ2h0PSIyNTAlIj4KICAgICAgICAgIDxmZUdhdXNzaWFuQmx1ciBpbj0iU291cmNlQWxwaGEiIHN0ZERldmlhdGlvbj0iMTIiPjwvZmVHYXVzc2lhbkJsdXI+CiAgICAgICAgICA8ZmVPZmZzZXQgZHg9IjEwIiBkeT0iMjAiPjwvZmVPZmZzZXQ+CiAgICAgICAgICA8ZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjIiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKICAgICAgIAoKICAgICAgICA8Y2xpcFBhdGggaWQ9ImJhY2stcGxhdGUtY2xpcCI+CiAgICAgICAgICA8cGF0aCBkPSJNNjg0IDQzMkg1MTJ2LTQ5LjE0M0ExMTIgMTEyIDAgMSAwIDQxNiAyNzJhMTExLjU1NiAxMTEuNTU2IDAgMCAwIDEwLjc4NSA0OEgxNjBhMzIuMDk0IDMyLjA5NCAwIDAgMC0zMiAzMnYzMjBhMzIuMDk0IDMyLjA5NCAwIDAgMCAzMiAzMmgxNzguNjdjMTUuMjM2IDkwLjggOTQuMiAxNjAgMTg5LjMzIDE2MCAxMDYuMDM5IDAgMTkyLTg1Ljk2MSAxOTItMTkyVjQ2OGEzNiAzNiAwIDAgMC0zNi0zNnoiIGZpbGw9IiNmZmYiPjwvcGF0aD4KICAgICAgICA8L2NsaXBQYXRoPgogICAgICA8L2RlZnM+CiAgICAgIDxnIGlkPSJzbWFsbF9wZXJzb24iIGZpbHRlcj0idXJsKCNwZXJzb24tc2hhZG93KSI+CiAgICAgICAgPHBhdGggaWQ9IkJvZHkiIGNsYXNzPSJjbHMtMSIgZD0iTTY5MiA0MzJoMTY4YTM2IDM2IDAgMCAxIDM2IDM2djE2NGExMjAgMTIwIDAgMCAxLTEyMCAxMjAgMTIwIDEyMCAwIDAgMS0xMjAtMTIwVjQ2OGEzNiAzNiAwIDAgMSAzNi0zNnoiPjwvcGF0aD4KICAgICAgICA8Y2lyY2xlIGlkPSJIZWFkIiBjbGFzcz0iY2xzLTEiIGN4PSI3NzYiIGN5PSIzMDQiIHI9IjgwIj48L2NpcmNsZT4KICAgICAgPC9nPgogICAgICA8ZyBpZD0iTGFyZ2VfUGVyc29uIiBmaWx0ZXI9InVybCgjcGVyc29uLXNoYWRvdykiPgogICAgICAgIDxwYXRoIGlkPSJCb2R5LTIiIGRhdGEtbmFtZT0iQm9keSIgY2xhc3M9ImNscy0yIiBkPSJNMzcyIDQzMmgzMTJhMzYgMzYgMCAwIDEgMzYgMzZ2MjA0YTE5MiAxOTIgMCAwIDEtMTkyIDE5MiAxOTIgMTkyIDAgMCAxLTE5Mi0xOTJWNDY4YTM2IDM2IDAgMCAxIDM2LTM2eiI+PC9wYXRoPgogICAgICAgIDxjaXJjbGUgaWQ9IkhlYWQtMiIgZGF0YS1uYW1lPSJIZWFkIiBjbGFzcz0iY2xzLTIiIGN4PSI1MjgiIGN5PSIyNzIiIHI9IjExMiI+PC9jaXJjbGU+CiAgICAgIDwvZz4KICAgICAgPHJlY3QgaWQ9IkJhY2tfUGxhdGUiIHg9IjEyOCIgeT0iMzIwIiB3aWR0aD0iMzg0IiBoZWlnaHQ9IjM4NCIgcng9IjMyIiByeT0iMzIiIGZpbHRlcj0idXJsKCNiYWNrLXBsYXRlLXNoYWRvdykiIGNsaXAtcGF0aD0idXJsKCNiYWNrLXBsYXRlLWNsaXApIiBmaWxsPSJ1cmwoI3BsYXRlLWZpbGwpIj48L3JlY3Q+CiAgICAgIDxwYXRoIGlkPSJMZXR0ZXJfVCIgZD0iTTM5OS4zNjUgNDQ1Ljg1NWgtNjAuMjkzdjE2NC4yaC0zOC40MTh2LTE2NC4yaC02MC4wMlY0MTRoMTU4LjczeiIgZmlsdGVyPSJ1cmwoI3RlZS1zaGFkb3cpIiBmaWxsPSIjZmZmIj48L3BhdGg+CiAgICA8L3N2Zz4=\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to Microsoft Teams</a>";					

				// if(!isset($_GET['trialView']) && $_GET['trialView'] == "" && !isset($_COOKIE["trialView"]) && $_COOKIE['trialView'] == "")
				// {
				// 	echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";
				// }


			}
		}



		if(is_user_logged_in() && ($isTeacher || isset($_COOKIE["wordpress_appView"]))){
			$resource= get_the_ID();




				// echo "<a style='display: none;' href=\"/authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\"><span class='fas fa-users'></span> Add to Google Classroom</a>";

				if(!isset($_COOKIE["isTrial"]) && empty($_COOKIE["isTrial"]) && !isset($_GET['trialView']) && empty($_GET["trialView"]))
				{
				}	

				// echo "BUTTON 2";

				if (!$buttonAdded) echo "<a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Microsoft Teams\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/04/teams_logo.png\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to Microsoft Teams</a>";					


				//echo "<a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Microsoft Teams\" src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMTAyNCAxMDI0Ij4KICAgICAgPGRlZnM+CiAgICAgICAgPGxpbmVhckdyYWRpZW50IGlkPSJwbGF0ZS1maWxsIiB4MT0iLS4yIiB5MT0iLS4yIiB4Mj0iLjgiIHkyPSIuOCI+CiAgICAgICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM1YTYyYzQiPjwvc3RvcD4KICAgICAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzM5NDBhYiI+PC9zdG9wPgogICAgICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICAgICAgPHN0eWxlPgogICAgICAgICAgLmNscy0xe2ZpbGw6IzUwNTljOX0uY2xzLTJ7ZmlsbDojN2I4M2VifQogICAgICAgIDwvc3R5bGU+CiAgICAgICAgPGZpbHRlciBpZD0icGVyc29uLXNoYWRvdyIgeD0iLTUwJSIgeT0iLTUwJSIgd2lkdGg9IjMwMCUiIGhlaWdodD0iMzAwJSI+CiAgICAgICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI1Ij48L2ZlR2F1c3NpYW5CbHVyPgogICAgICAgICAgPGZlT2Zmc2V0IGR5PSIyNSI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgICA8ZmVGdW5jQSB0eXBlPSJsaW5lYXIiIHNsb3BlPSIuMjUiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKCiAgICAgICAgPGZpbHRlciBpZD0iYmFjay1wbGF0ZS1zaGFkb3ciIHg9Ii01MCUiIHk9Ii01MCUiIHdpZHRoPSIzMDAlIiBoZWlnaHQ9IjMwMCUiPgogICAgICAgICAgCgk8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI0Ij48L2ZlR2F1c3NpYW5CbHVyPgoJICA8ZmVPZmZzZXQgZHg9IjIiIGR5PSIyNCI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjYiPjwvZmVGdW5jQT4KCiAgICAgICAgICA8L2ZlQ29tcG9uZW50VHJhbnNmZXI+CiAgICAgICAgICA8ZmVNZXJnZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlPjwvZmVNZXJnZU5vZGU+CiAgICAgICAgICAgIDxmZU1lcmdlTm9kZSBpbj0iU291cmNlR3JhcGhpYyI+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgIDwvZmVNZXJnZT4KICAgICAgICA8L2ZpbHRlcj4KICAgICAgICA8ZmlsdGVyIGlkPSJ0ZWUtc2hhZG93IiB4PSItNTAlIiB5PSItNTAlIiB3aWR0aD0iMjUwJSIgaGVpZ2h0PSIyNTAlIj4KICAgICAgICAgIDxmZUdhdXNzaWFuQmx1ciBpbj0iU291cmNlQWxwaGEiIHN0ZERldmlhdGlvbj0iMTIiPjwvZmVHYXVzc2lhbkJsdXI+CiAgICAgICAgICA8ZmVPZmZzZXQgZHg9IjEwIiBkeT0iMjAiPjwvZmVPZmZzZXQ+CiAgICAgICAgICA8ZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjIiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKICAgICAgIAoKICAgICAgICA8Y2xpcFBhdGggaWQ9ImJhY2stcGxhdGUtY2xpcCI+CiAgICAgICAgICA8cGF0aCBkPSJNNjg0IDQzMkg1MTJ2LTQ5LjE0M0ExMTIgMTEyIDAgMSAwIDQxNiAyNzJhMTExLjU1NiAxMTEuNTU2IDAgMCAwIDEwLjc4NSA0OEgxNjBhMzIuMDk0IDMyLjA5NCAwIDAgMC0zMiAzMnYzMjBhMzIuMDk0IDMyLjA5NCAwIDAgMCAzMiAzMmgxNzguNjdjMTUuMjM2IDkwLjggOTQuMiAxNjAgMTg5LjMzIDE2MCAxMDYuMDM5IDAgMTkyLTg1Ljk2MSAxOTItMTkyVjQ2OGEzNiAzNiAwIDAgMC0zNi0zNnoiIGZpbGw9IiNmZmYiPjwvcGF0aD4KICAgICAgICA8L2NsaXBQYXRoPgogICAgICA8L2RlZnM+CiAgICAgIDxnIGlkPSJzbWFsbF9wZXJzb24iIGZpbHRlcj0idXJsKCNwZXJzb24tc2hhZG93KSI+CiAgICAgICAgPHBhdGggaWQ9IkJvZHkiIGNsYXNzPSJjbHMtMSIgZD0iTTY5MiA0MzJoMTY4YTM2IDM2IDAgMCAxIDM2IDM2djE2NGExMjAgMTIwIDAgMCAxLTEyMCAxMjAgMTIwIDEyMCAwIDAgMS0xMjAtMTIwVjQ2OGEzNiAzNiAwIDAgMSAzNi0zNnoiPjwvcGF0aD4KICAgICAgICA8Y2lyY2xlIGlkPSJIZWFkIiBjbGFzcz0iY2xzLTEiIGN4PSI3NzYiIGN5PSIzMDQiIHI9IjgwIj48L2NpcmNsZT4KICAgICAgPC9nPgogICAgICA8ZyBpZD0iTGFyZ2VfUGVyc29uIiBmaWx0ZXI9InVybCgjcGVyc29uLXNoYWRvdykiPgogICAgICAgIDxwYXRoIGlkPSJCb2R5LTIiIGRhdGEtbmFtZT0iQm9keSIgY2xhc3M9ImNscy0yIiBkPSJNMzcyIDQzMmgzMTJhMzYgMzYgMCAwIDEgMzYgMzZ2MjA0YTE5MiAxOTIgMCAwIDEtMTkyIDE5MiAxOTIgMTkyIDAgMCAxLTE5Mi0xOTJWNDY4YTM2IDM2IDAgMCAxIDM2LTM2eiI+PC9wYXRoPgogICAgICAgIDxjaXJjbGUgaWQ9IkhlYWQtMiIgZGF0YS1uYW1lPSJIZWFkIiBjbGFzcz0iY2xzLTIiIGN4PSI1MjgiIGN5PSIyNzIiIHI9IjExMiI+PC9jaXJjbGU+CiAgICAgIDwvZz4KICAgICAgPHJlY3QgaWQ9IkJhY2tfUGxhdGUiIHg9IjEyOCIgeT0iMzIwIiB3aWR0aD0iMzg0IiBoZWlnaHQ9IjM4NCIgcng9IjMyIiByeT0iMzIiIGZpbHRlcj0idXJsKCNiYWNrLXBsYXRlLXNoYWRvdykiIGNsaXAtcGF0aD0idXJsKCNiYWNrLXBsYXRlLWNsaXApIiBmaWxsPSJ1cmwoI3BsYXRlLWZpbGwpIj48L3JlY3Q+CiAgICAgIDxwYXRoIGlkPSJMZXR0ZXJfVCIgZD0iTTM5OS4zNjUgNDQ1Ljg1NWgtNjAuMjkzdjE2NC4yaC0zOC40MTh2LTE2NC4yaC02MC4wMlY0MTRoMTU4LjczeiIgZmlsdGVyPSJ1cmwoI3RlZS1zaGFkb3cpIiBmaWxsPSIjZmZmIj48L3BhdGg+CiAgICA8L3N2Zz4=\" width=\"24\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to Microsoft Teams</a>";					

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

			if(is_user_logged_in() && isset($_COOKIE["isTeach"]))
			{
				$resource= get_the_ID();

				// echo "BUTTON 3";


				echo "<p  style='text-align: center; background-color: #EFEFEF; padding: 5px; margin-bottom: 8px;'>You are currently signed in as a <strong>Teacher</strong></p>";

				echo "<a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Microsoft Teams\" src=\"https://chalkboardpublishing.com/wp-content/uploads/2022/04/teams_logo.png\" width=\"36\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to Microsoft Teams</a>";	
								// echo "<a href=\"/ms-teams-authorize/?resource=" . $resource . "\" class=\"addToClassroomButton\" style=\"line-height: 24px; margin-right: 10px;\"><img alt=\"Share to Microsoft Teams\" src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMTAyNCAxMDI0Ij4KICAgICAgPGRlZnM+CiAgICAgICAgPGxpbmVhckdyYWRpZW50IGlkPSJwbGF0ZS1maWxsIiB4MT0iLS4yIiB5MT0iLS4yIiB4Mj0iLjgiIHkyPSIuOCI+CiAgICAgICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM1YTYyYzQiPjwvc3RvcD4KICAgICAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzM5NDBhYiI+PC9zdG9wPgogICAgICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICAgICAgPHN0eWxlPgogICAgICAgICAgLmNscy0xe2ZpbGw6IzUwNTljOX0uY2xzLTJ7ZmlsbDojN2I4M2VifQogICAgICAgIDwvc3R5bGU+CiAgICAgICAgPGZpbHRlciBpZD0icGVyc29uLXNoYWRvdyIgeD0iLTUwJSIgeT0iLTUwJSIgd2lkdGg9IjMwMCUiIGhlaWdodD0iMzAwJSI+CiAgICAgICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI1Ij48L2ZlR2F1c3NpYW5CbHVyPgogICAgICAgICAgPGZlT2Zmc2V0IGR5PSIyNSI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgICA8ZmVGdW5jQSB0eXBlPSJsaW5lYXIiIHNsb3BlPSIuMjUiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKCiAgICAgICAgPGZpbHRlciBpZD0iYmFjay1wbGF0ZS1zaGFkb3ciIHg9Ii01MCUiIHk9Ii01MCUiIHdpZHRoPSIzMDAlIiBoZWlnaHQ9IjMwMCUiPgogICAgICAgICAgCgk8ZmVHYXVzc2lhbkJsdXIgaW49IlNvdXJjZUFscGhhIiBzdGREZXZpYXRpb249IjI0Ij48L2ZlR2F1c3NpYW5CbHVyPgoJICA8ZmVPZmZzZXQgZHg9IjIiIGR5PSIyNCI+PC9mZU9mZnNldD4KICAgICAgICAgIDxmZUNvbXBvbmVudFRyYW5zZmVyPgogICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjYiPjwvZmVGdW5jQT4KCiAgICAgICAgICA8L2ZlQ29tcG9uZW50VHJhbnNmZXI+CiAgICAgICAgICA8ZmVNZXJnZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlPjwvZmVNZXJnZU5vZGU+CiAgICAgICAgICAgIDxmZU1lcmdlTm9kZSBpbj0iU291cmNlR3JhcGhpYyI+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgIDwvZmVNZXJnZT4KICAgICAgICA8L2ZpbHRlcj4KICAgICAgICA8ZmlsdGVyIGlkPSJ0ZWUtc2hhZG93IiB4PSItNTAlIiB5PSItNTAlIiB3aWR0aD0iMjUwJSIgaGVpZ2h0PSIyNTAlIj4KICAgICAgICAgIDxmZUdhdXNzaWFuQmx1ciBpbj0iU291cmNlQWxwaGEiIHN0ZERldmlhdGlvbj0iMTIiPjwvZmVHYXVzc2lhbkJsdXI+CiAgICAgICAgICA8ZmVPZmZzZXQgZHg9IjEwIiBkeT0iMjAiPjwvZmVPZmZzZXQ+CiAgICAgICAgICA8ZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgICAgPGZlRnVuY0EgdHlwZT0ibGluZWFyIiBzbG9wZT0iLjIiPjwvZmVGdW5jQT4KICAgICAgICAgIDwvZmVDb21wb25lbnRUcmFuc2Zlcj4KICAgICAgICAgIDxmZU1lcmdlPgogICAgICAgICAgICA8ZmVNZXJnZU5vZGU+PC9mZU1lcmdlTm9kZT4KICAgICAgICAgICAgPGZlTWVyZ2VOb2RlIGluPSJTb3VyY2VHcmFwaGljIj48L2ZlTWVyZ2VOb2RlPgogICAgICAgICAgPC9mZU1lcmdlPgogICAgICAgIDwvZmlsdGVyPgoKICAgICAgIAoKICAgICAgICA8Y2xpcFBhdGggaWQ9ImJhY2stcGxhdGUtY2xpcCI+CiAgICAgICAgICA8cGF0aCBkPSJNNjg0IDQzMkg1MTJ2LTQ5LjE0M0ExMTIgMTEyIDAgMSAwIDQxNiAyNzJhMTExLjU1NiAxMTEuNTU2IDAgMCAwIDEwLjc4NSA0OEgxNjBhMzIuMDk0IDMyLjA5NCAwIDAgMC0zMiAzMnYzMjBhMzIuMDk0IDMyLjA5NCAwIDAgMCAzMiAzMmgxNzguNjdjMTUuMjM2IDkwLjggOTQuMiAxNjAgMTg5LjMzIDE2MCAxMDYuMDM5IDAgMTkyLTg1Ljk2MSAxOTItMTkyVjQ2OGEzNiAzNiAwIDAgMC0zNi0zNnoiIGZpbGw9IiNmZmYiPjwvcGF0aD4KICAgICAgICA8L2NsaXBQYXRoPgogICAgICA8L2RlZnM+CiAgICAgIDxnIGlkPSJzbWFsbF9wZXJzb24iIGZpbHRlcj0idXJsKCNwZXJzb24tc2hhZG93KSI+CiAgICAgICAgPHBhdGggaWQ9IkJvZHkiIGNsYXNzPSJjbHMtMSIgZD0iTTY5MiA0MzJoMTY4YTM2IDM2IDAgMCAxIDM2IDM2djE2NGExMjAgMTIwIDAgMCAxLTEyMCAxMjAgMTIwIDEyMCAwIDAgMS0xMjAtMTIwVjQ2OGEzNiAzNiAwIDAgMSAzNi0zNnoiPjwvcGF0aD4KICAgICAgICA8Y2lyY2xlIGlkPSJIZWFkIiBjbGFzcz0iY2xzLTEiIGN4PSI3NzYiIGN5PSIzMDQiIHI9IjgwIj48L2NpcmNsZT4KICAgICAgPC9nPgogICAgICA8ZyBpZD0iTGFyZ2VfUGVyc29uIiBmaWx0ZXI9InVybCgjcGVyc29uLXNoYWRvdykiPgogICAgICAgIDxwYXRoIGlkPSJCb2R5LTIiIGRhdGEtbmFtZT0iQm9keSIgY2xhc3M9ImNscy0yIiBkPSJNMzcyIDQzMmgzMTJhMzYgMzYgMCAwIDEgMzYgMzZ2MjA0YTE5MiAxOTIgMCAwIDEtMTkyIDE5MiAxOTIgMTkyIDAgMCAxLTE5Mi0xOTJWNDY4YTM2IDM2IDAgMCAxIDM2LTM2eiI+PC9wYXRoPgogICAgICAgIDxjaXJjbGUgaWQ9IkhlYWQtMiIgZGF0YS1uYW1lPSJIZWFkIiBjbGFzcz0iY2xzLTIiIGN4PSI1MjgiIGN5PSIyNzIiIHI9IjExMiI+PC9jaXJjbGU+CiAgICAgIDwvZz4KICAgICAgPHJlY3QgaWQ9IkJhY2tfUGxhdGUiIHg9IjEyOCIgeT0iMzIwIiB3aWR0aD0iMzg0IiBoZWlnaHQ9IjM4NCIgcng9IjMyIiByeT0iMzIiIGZpbHRlcj0idXJsKCNiYWNrLXBsYXRlLXNoYWRvdykiIGNsaXAtcGF0aD0idXJsKCNiYWNrLXBsYXRlLWNsaXApIiBmaWxsPSJ1cmwoI3BsYXRlLWZpbGwpIj48L3JlY3Q+CiAgICAgIDxwYXRoIGlkPSJMZXR0ZXJfVCIgZD0iTTM5OS4zNjUgNDQ1Ljg1NWgtNjAuMjkzdjE2NC4yaC0zOC40MTh2LTE2NC4yaC02MC4wMlY0MTRoMTU4LjczeiIgZmlsdGVyPSJ1cmwoI3RlZS1zaGFkb3cpIiBmaWxsPSIjZmZmIj48L3BhdGg+CiAgICA8L3N2Zz4=\" width=\"24\" style=\"width: 28px; float: left; margin-bottom: 0; margin-right: 10px;\"> Add to Microsoft Teams</a>";	


			}


	}


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

function checkMicrosoftAccess() {
	//echo "CHECKING ACCESS";
	if(is_user_logged_in() && (isset($_GET['source']) && $_GET['source'] == "teams"))
	{

		//initialize the API and set the scopes.
	
		require_once CLARENDON_MSAPI_DIR . 'oauth2-client/load.php';		


		//initialize the api and set the scopes.
		
		$redirectUri = "http://localhost:8076/index.php/ms-teams-authorize/";
		$redirectUri = "https://chalkboardstg.wpengine.com/ms-teams-authorize/";	
		$redirectUri = "https://chalkboardpublishing.com/ms-teams-authorize/";	
		
		$oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
		  'clientId'                => "b984d8f3-78d7-4640-94ec-74cad74527b0",
		  'clientSecret'            => "JBw8Q~ZB4_MC0SbT.64pNptjLLNlvgeiXTDVhbc-",
		  'redirectUri'             => $redirectUri,
		  'urlAuthorize'            => "https://login.microsoftonline.com/common/oauth2/v2.0/authorize",
		  'urlAccessToken'          => "https://login.microsoftonline.com/common/oauth2/v2.0/token",
		  'urlResourceOwnerDetails' => '',
		  'scopes'                  => "openid profile offline_access user.read EduAssignments.ReadWrite EduRoster.ReadWrite"
		]);

		$currentToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);
		$currentRedirect = get_user_meta(get_current_user_id(), "ms_teams_redirect", true);	
		$resource = get_the_ID();
		$permalink = get_permalink($resource);	
		if (!isset($currentRedirect) || empty($currentRedirect))
		{
			add_user_meta(get_current_user_id(), "ms_teams_redirect", $permalink);
		}
		else {
			update_user_meta(get_current_user_id(), "ms_teams_redirect", $permalink);
		}		
		if (isset($currentToken) && $currentToken != "") {
			
		}	
		else if (isset($_GET['code'])) {	
			$authCode=$_GET['code'];
			//get the access token from the auth code.
			
			$accessToken = "";
		    
			if (!isset($currentToken) || empty($currentToken))
			{
				add_user_meta(get_current_user_id(), "ms_teams_token", $accessToken);
			}
			else {
				update_user_meta(get_current_user_id(), "ms_teams_token", $accessToken);
			}	
		}
		else {

			if (isset($currentToken) && $currentToken != "") 
			{
				$tokenExpired = true;
				$expiry = get_user_meta(get_current_user_id(), "ms_teams_token_expiry", true);
				if (isset($expiry)) {
					if ($expiry > time()) {
						$tokenExpired = false;
					}
				}
						
						
						
				if ($tokenExpired) {
							// Refresh the token if possible, else fetch a new one.
					$refreshToken = get_user_meta(get_current_user_id(), "ms_teams_refresh_token", true);

					if (isset($refreshToken) && strlen($refreshToken) > 0) {
								//we have a refresh token.
								
						$client = new \GuzzleHttp\Client();
						$form_params = array();
						$form_params["client_id"] = "b984d8f3-78d7-4640-94ec-74cad74527b0";
								
						$form_params["scope"] = "openid profile offline_access user.read EduAssignments.ReadWrite EduRoster.ReadWrite";
						$form_params["refresh_token"] = $refreshToken;
						$form_params["redirect_uri"] = $redirectUri;
								
						$form_params["grant_type"] = "refresh_token";
						$form_params["client_secret"] = "JBw8Q~ZB4_MC0SbT.64pNptjLLNlvgeiXTDVhbc-";

						$url = "https://login.microsoftonline.com/common/oauth2/v2.0/token";

						$outcomeResponse = $client->request('POST',
							$url,
							array( 'form_params' => $form_params ),															
						);					
						$outcomeResult = json_decode( $outcomeResponse->getBody() );
						
						update_user_meta(get_current_user_id(), "ms_teams_token", $outcomeResult->access_token);
						
						$currentToken = $outcomeResult->access_token;
						
						update_user_meta(get_current_user_id(), "ms_teams_refresh_token", $outcomeResult->refresh_token);
						
						update_user_meta(get_current_user_id(), "ms_teams_token_expiry", time() + $outcomeResult->expires_in);
					}
				}
			}
			else {
				$authUrl = $oauthClient->getAuthorizationUrl();
				echo "<script type=\"text/javascript\">";
				echo "window.location='$authUrl';";
				echo "</script>";
				die();				
			}
			
		}

	}
	else if(!is_user_logged_in()){

		$current_url = "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];


		if(isset($_GET["source"]) && $_GET["source"] == "teams")
		{

			// echo "<div style='text-align: center; margin-top: 40px; margin-bottom: 40px;'><h2>To view this assigned work, click \"Continue with Google\" below</h2>";
			/*
			echo "<div style='margin-top: 40px; margin-bottom: 40px; background-color: #EFEFEF; padding: 10px;'>";
			echo "<p style='color: red;'><strong>Your teacher has assigned this work for you to complete.</strong></p>
				<p><strong>Step 1:</strong> Click “Continue with Microsoft” below.</p>
				<p><strong>Step 2:</strong> Sign into the same Microsoft account that you use in your Microsoft Teams</p>
				<p><strong>Step 3:</strong> Complete the assignment and click “Finish Questions”. Your work will be automatically submitted to your teacher</p>

			";
			*/

		}else{
			/*
			echo "<div style='margin-top: 40px; margin-bottom: 40px; background-color: #EFEFEF; padding: 10px;'>";
			echo "<p style='color: red;'><strong>How to share this Lesson/Activity with your Microsoft Teams:</strong></p>
			<ol style='padding-bottom: 20px; line-height: 1.5;'>
				<li>To share this lesson/activity with Microsoft Teams, click \"Continue with Microsoft\" to get started.</li>
				<li>After logging in, click \"Add to Microsoft Teams\" to assign this lesson/activity to your students.</li>
			</ol>";
			*/
		}



	//	echo do_shortcode( '[nextend_social_login trackerdata="source" redirect="'.$current_url.'" align="center"]' );

		// echo do_shortcode( '[TheChamp-Login]' );



		echo "</div>";
		// die();
	}
}

function clarendon_msapi_add_to_teams()
{
	//echo "ADDING to TEAMS";
	global $wpdb;

	session_start();

    //ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	


	try{

	require CLARENDON_MSAPI_DIR . 'oauth2-client/load.php';		


	//initialize the api and set the scopes.
	
	$redirectUri = "http://localhost:8076/index.php/ms-teams-authorize/";
	$redirectUri = "https://chalkboardstg.wpengine.com/ms-teams-authorize/";	
	$redirectUri = "https://chalkboardpublishing.com/ms-teams-authorize/";	
	
	$oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
      'clientId'                => "b984d8f3-78d7-4640-94ec-74cad74527b0",
      'clientSecret'            => "JBw8Q~ZB4_MC0SbT.64pNptjLLNlvgeiXTDVhbc-",
      'redirectUri'             => $redirectUri,
      'urlAuthorize'            => "https://login.microsoftonline.com/common/oauth2/v2.0/authorize",
      'urlAccessToken'          => "https://login.microsoftonline.com/common/oauth2/v2.0/token",
      'urlResourceOwnerDetails' => '',
      'scopes'                  => "openid profile offline_access user.read EduAssignments.ReadWrite EduRoster.ReadWrite"
    ]);
	
	$currentToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);
	if (isset($currentToken) && $currentToken != "") 
	{
		$tokenExpired = true;
		$expiry = get_user_meta(get_current_user_id(), "ms_teams_token_expiry", true);
		if (isset($expiry)) {
			if ($expiry > time()) {
				$tokenExpired = false;
			}
		}
				
				
				
		if ($tokenExpired) {
					// Refresh the token if possible, else fetch a new one.
			$refreshToken = get_user_meta(get_current_user_id(), "ms_teams_refresh_token", true);

			if (isset($refreshToken) && strlen($refreshToken) > 0) {
						//we have a refresh token.
						
				$client = new \GuzzleHttp\Client();
				$form_params = array();
				$form_params["client_id"] = "b984d8f3-78d7-4640-94ec-74cad74527b0";
						
				$form_params["scope"] = "openid profile offline_access user.read EduAssignments.ReadWrite EduRoster.ReadWrite";
				$form_params["refresh_token"] = $refreshToken;
				$form_params["redirect_uri"] = $redirectUri;
						
				$form_params["grant_type"] = "refresh_token";
				$form_params["client_secret"] = "JBw8Q~ZB4_MC0SbT.64pNptjLLNlvgeiXTDVhbc-";

				$url = "https://login.microsoftonline.com/common/oauth2/v2.0/token";

				$outcomeResponse = $client->request('POST',
					$url,
					array( 'form_params' => $form_params ),															
				);					
				$outcomeResult = json_decode( $outcomeResponse->getBody() );
				
				update_user_meta(get_current_user_id(), "ms_teams_token", $outcomeResult->access_token);
				
				$currentToken = $outcomeResult->access_token;
				
				update_user_meta(get_current_user_id(), "ms_teams_refresh_token", $outcomeResult->refresh_token);
				
				update_user_meta(get_current_user_id(), "ms_teams_token_expiry", time() + $outcomeResult->expires_in);
			}
		}
	}

	
	
	$resource = get_user_meta(get_current_user_id(), "ms_teams_resource", true);

	$currentRedirect = get_user_meta(get_current_user_id(), "ms_teams_redirect", true);	

	$forceAuth = get_user_meta(get_current_user_id(), "ms_force_auth", true);	



		if (isset($_GET['resource'])) {



			echo "<script>";


			echo "function setCookie(cname, cvalue, exdays) {
					  var d = new Date();
					  d.setTime(d.getTime() + (exdays*24*60*60*1000));
					  var expires = \"expires=\"+ d.toUTCString();
					  document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
				}";


			echo "setCookie('wordpress_ms_resource_id','".$_GET['resource']."',30);";	

		
			echo "</script>";



			if (!isset($resource) || empty($resource))
			{
				add_user_meta(get_current_user_id(), "ms_teams_resource", $_GET['resource']);

				$_SESSION["CUR_RESOURCE"] = $_GET['resource'];

			}
			else {
				update_user_meta(get_current_user_id(), "ms_teams_resource", $_GET['resource']);
			}
			$resource = $_GET['resource'];
		}

		if (isset($_GET['clear'])) {
			delete_user_meta(get_current_user_id(), "ms_teams_token");
			delete_user_meta(get_current_user_id(), "ms_teams_resource");		

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

					//set the access token.
					
					//initialize the API
					$headers = [
						'Authorization' => 'Bearer ' . $currentToken,
					];	
					$client = new \GuzzleHttp\Client();
					$myId = "";
					$meResponse = $client->request(
						'GET',
						'https://graph.microsoft.com/v1.0/me',
						array( 'headers' => $headers )
					);	
					$meResult = json_decode( $meResponse->getBody() );	
					if (is_array($meResult)) {					
						foreach ($meResult as $me){
							$myId = $me->id;
						}
					}
					else
					{
						$myId = $meResult->id;
					}
					
					$response = $client->request(
						'GET',
						'https://graph.microsoft.com/v1.0/education/classes',
						array( 'headers' => $headers )
					);	
					
					$result = json_decode( $response->getBody() );
					
					if (!is_array($result)) {
						
						$result = $result->value;
						
					}
					$teamId = "";
		
					foreach ($result as $team) {

						

							if (is_array($team)) {
								$teamId = $team[0]->id;
							}
							else $teamId = $team->id;
							
							//is the user a teacher of this class?
								if (!empty($teamId))
								{
									if ($teamId != $_GET['teamID']) continue;
									
									$teacherResponse = $client->request(
									'GET',
									'https://graph.microsoft.com/v1.0/education/classes/' . $teamId . '/teachers',
										array( 'headers' => $headers )
									);
									
									
									
									$teacherResult = json_decode( $teacherResponse->getBody() );
									$hasCourse = true;
									foreach ($teacherResult as $teacher) {
										foreach ($teacher as $teach)
										{
											if ($teach->id == $myId) {
												$isTeacher = true;
											}
										}
									}
									
									if ($isTeacher) {
										//
										
										$permalink = get_permalink($resource);

										$currentRedirect = get_user_meta(get_current_user_id(), "ms_teams_redirect", true);

										if (!isset($currentRedirect) || empty($currentRedirect))
										{
											add_user_meta(get_current_user_id(), "ms_teams_redirect", $permalink);
										}
										else {
											update_user_meta(get_current_user_id(), "ms_teams_redirect", $permalink);
										}




										$currentRedirect = get_user_meta(get_current_user_id(), "ms_teams_redirect", true);

										$postTitle = get_the_title($resource);
										$dispName = "";
										if (is_array($team)) {
											$dispName = $team[0]->displayName;
										}
										else $dispName = $team->displayName;

										echo "<p><strong>\"". $postTitle ."\"</strong> has been shared with your <strong>". $dispName . "</strong> class on Microsoft Teams.</p>";



										echo "<a href=\"".$currentRedirect."\" class=\"nectar-button large\"> Return to Activity </a> ";
										$topicId = "";
										
										$topicName = $_GET['topicType'];
										
										$assignmentResponse = $client->request(
										'GET',
										'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments',
											array( 'headers' => $headers )
										);
										$assignmentResult = json_decode( $assignmentResponse->getBody() );
										//print_r($assignmentResult);
										$alreadyAssigned = false;
										$permalink = get_permalink($resource) . "?source=teams";
										
										foreach ($assignmentResult as $assignment) {
											if ($assignment[0]->resources[0]->link == $permalink) {
												$alreadyAssigned = true;
											}
											
										}
										if (!$alreadyAssigned) {

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


										}
										$pointTotal = 10;
										
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
					
										}
										else{

			
												$title = str_replace('&#8211;', '-', get_the_title($resource));
		
												

										}
										

										
												$cur_date = time();
												$cur_date = mktime(17, 0, 0, date("m", $cur_date), date("d", $cur_date), date("Y", $cur_date));	
												$form_params = array();
												$form_params["dueDateTime"] = date('Y-m-d', strtotime('+7 day', $cur_date)) . "T" . date('H:i:s', strtotime('+7 day', $cur_date)) . "Z";
												$form_params["displayName"] =  $title;
												//stop_date = date('Y-m-d H:i:s', strtotime('+1 day', $stop_date));
												
	
												
												
												$grading = array();
												$grading["@odata.type"] = "#microsoft.education.assignments.api.educationAssignmentPointsGradeType";
												$grading["maxPoints"] = $pointTotal;
												$form_params["grading"] = $grading;
												$instructions = array();
												$instructions["contentType"] = "text";
												$instructions["content"] = str_replace('"', '', $excerpt);
												$form_params["instructions"] = $instructions;
												
												
												
	
												
												$form_params["status"] = "draft";
												$form_params["allowStudentsToAddResourcesToSubmission"] = true;
												
												$form_params = json_encode($form_params);
	
												$postheaders = [
													'Authorization' => 'Bearer ' . $currentToken,
													'Content-type' => 'application/json',
													'Content-length' => strlen($form_params)
												];	

												$assignmentResponse = $client->request('POST',
												'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments',
													array( 'headers' => $postheaders, 'body' => $form_params ),
													
												);					
												
												$assignment = json_decode( $assignmentResponse->getBody() );												
												

													
													$assignmentId = $assignment->id;
													if (!empty($assignmentId))
													{	
													
														$form_params = array();
														$form_params["distributeForStudentWork"] = "false";
														$resource = array();
														$resource["displayName"] = $title;
														$resource["link"] = $permalink;
														
														
														
														$resource["@odata.type"] = "#microsoft.education.assignments.api.educationLinkResource";
														
														$form_params["resource"] = $resource;
														$form_params = json_encode($form_params);

														$postheaders = [
															'Authorization' => 'Bearer ' . $currentToken,
															'Content-type' => 'application/json',
															'Content-length' => strlen($form_params)
														];	
														
														
														//attach the resource.
														$assignmentResponse = $client->request('POST',
														'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/resources',
															array( 'headers' => $postheaders, 'body' => $form_params ),
															
														);	

														//publish the assignment
														$assignmentResponse = $client->request('POST',
														'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/publish',
															array( 'headers' => $headers ),
															
														);

														$categoryResponse = $client->request(
														'GET',
														'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignmentCategories/',
															array( 'headers' => $headers )
														);
														
														$categoryBody = json_decode( $categoryResponse->getBody() );
														
														
														$categoryExists = false;
														$categoryId = "";
														
														foreach ($categoryBody->value as $category) {
	
															
															if ($category->displayName == $topicName) {
																$categoryExists = true;
																$categoryId = $category->id;
															}
														}
														
														if (!$categoryExists) {
															//create the category

															$form_params = array();
															$form_params['displayName'] = $topicName;
															$form_params = json_encode($form_params);
															
															
															$postheaders = [
																'Authorization' => 'Bearer ' . $currentToken,
																'Content-type' => 'application/json',
																'Content-length' => strlen($form_params)
															];	
															$categoryResponse = $client->request('POST',
															'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignmentCategories',
																array( 'headers' => $postheaders, 'body' => $form_params ),
																
															);
															$category = json_decode( $categoryResponse->getBody() );

															$categoryId = $category->id;
														}
				
														
														$form_params = array();
														$form_params['@odata.id'] = 'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignmentCategories/' . $categoryId;
														$form_params = json_encode($form_params);
														
														
														$postheaders = [
																'Authorization' => 'Bearer ' . $currentToken,
																'Content-type' => 'application/json',
																'Content-length' => strlen($form_params)
															];
														$category = $client->request('POST',
															'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/categories/$ref',
																array( 'headers' => $postheaders, 'body' => $form_params ),
																
															);	
													}
												

										
										
										add_user_meta(get_current_user_id(), "team_ID", $teamId);
										

										$microsoftID = get_user_meta(get_current_user_id(), "microsoft_ID");
										

										add_user_meta(get_current_user_id(), "microsoft_ID", $myId);										
										
	
									}
								
								}

					}
					
					
					//get the courses
					
					//if the course ID is not equal to the $_GET['classRoomID'] continue
					
					//check to see if the user is the teacher.
					
					//get the permalink to the resource
					
					//create a new assignment in Microsoft Teams
					
					
		
				}
				else{

						// if no topic is set show topic textbox

						echo "<div class='topicCon'>";


					$accessToken = $currentToken;
					
					//set the access token.
					
					//initialize the API
					$headers = [
						'Authorization' => 'Bearer ' . $currentToken,
					];	
					$client = new \GuzzleHttp\Client();
					$myId = "";
					$meResponse = $client->request(
						'GET',
						'https://graph.microsoft.com/v1.0/me',
						array( 'headers' => $headers )
					);	
					$meResult = json_decode( $meResponse->getBody() );	

					if (is_array($meResult))
					{
						foreach ($meResult as $me){
							$myId = $me->id;
						}
					}
					else {
						$myId = $meResult->id;
					}
					
					$response = $client->request(
						'GET',
						'https://graph.microsoft.com/v1.0/education/classes',
						array( 'headers' => $headers )
					);	
					$result = json_decode( $response->getBody() );
					if (!is_array($result)) $result = $result->value;
					//print_r($resultX);
					$teamId = "";
					echo "<p style='width: 100%;'>Please enter the topic and team for this resource.</p>";

					echo "<select class='ClassroomSelect'>";
					echo "<option value=''>Select Team</option>";	
					$dbg = "";
					foreach ($result as $team) {

						
							if (is_array($team)) {
								$teamId = $team[0]->id;
							}
							else $teamId = $team->id;
							//is the user a teacher of this class?
								if (!empty($teamId))
								{
									$teacherResponse = $client->request(
									'GET',
									'https://graph.microsoft.com/v1.0/education/classes/' . $teamId . '/teachers',
										array( 'headers' => $headers )
									);
									
									$teacherResult = json_decode( $teacherResponse->getBody() );
									
									$hasCourse = true;
									foreach ($teacherResult as $teacher) {
										foreach ($teacher as $teach)
										{
											if ($teach->id == $myId) {
												$isTeacher = true;
												$selected = "";

												if (count($result) <= 2) $selected = " selected";
												if (is_array($team)) {
													$teamName = $team[0]->displayName;
												}
												else $teamName = $team->displayName;
												echo "<option value=\"" . $teamId . "\" $selected>" . $teamName . "</option>";
											
											}
										}
									}
								
								}

					}
					
					


					

						echo "</select>";

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
				$meResponse = $client->request(
					'GET',
					'https://graph.microsoft.com/v1.0/me',
					array( 'headers' => $headers )
				);
				$meResult = json_decode( $meResponse->getBody());	


				$mail = "";
				$userName = "";
				$fName = "";
				$lName = "";
				// print_r($meResult);

				if (is_array($meResult))
				{
					foreach ($meResult as $me){
						$mail = $me[0]->mail;
						$userName = $me[0]->displayName;
						$fName = $me[0]->givenName;
						$lName = $me[0]->surname;

					}
				}
				else {
					$mail = $meResult->mail;
					$userName = $meResult->displayName;
					$fName = $meResult->givenName;
					$lName = $meResult->surname;


				}

				if($mail != ""){

					$exists = email_exists($mail);

				}

				if($exists){
					// sign in user

					// echo "user exists";

					$user = get_user_by( 'email', $mail);

				    wp_set_auth_cookie($user->ID, false, is_ssl());



				}else{

					// echo "new user";

				    $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

				    $user_id = wp_create_user($userName, $random_password, $mail);	

				    $user_data = wp_update_user( array( 'ID' => $user_id, 'user_email' => $mail, 'first_name' => $fName, 'last_name' => $lName ) );				

				    wp_set_auth_cookie($user_id, false, is_ssl());

					//create user and sign in

				}


				// echo "CUR RESOURCE " . $_SESSION["resourceID"];
				// session_start();
				$permalink = "";

				if(isset($_COOKIE["wordpress_ms_resource_id"])){

					// $currentRedirect =

					$permalink = get_permalink($_COOKIE['wordpress_ms_resource_id']);

				}

				if(isset($_COOKIE["wordpress_quizID"]) && isset($_COOKIE["wordpress_userID"])){


					$permalink = "/assignment-answers/?uid=".$_COOKIE["wordpress_userID"]."&quid=" . $_COOKIE["wordpress_quizID"];

					setcookie ("wordpress_quizID", "", time()-100 , '/' ); // past time	

					setcookie ("wordpress_userID", "", time()-100 , '/' ); // past time						

				}				

				// print_r($_COOKIE);

				// echo "PERMALINK " . $permalink;

				// echo "COOKIE " . $_COOKIE["wordpress_isTeams"];

				// die();

				if(isset($permalink) && isset($_COOKIE["wordpress_isTeams"])){

					$currentRedirect = $permalink;

				}
				else if(isset($permalink) && isset($_COOKIE["wordpress_appView"])){

					$currentRedirect = $permalink;

				}
				else{

					// $currentRedirect = "/ms-teams-authorize/?resource=" . $_COOKIE['wordpress_ms_resource_id'];

					$currentRedirect = $permalink;


				}

				// unset($_COOKIE["ms_resource_id"]);

				// echo "COOKIE " . $_COOKIE['ms_resource_id'];





				
				// die();

				
				//get user by email
				//if exists wp_set_auth_cookie
				//else wp_create_user and set wp_set_auth_cookie
			}


			



			if (!isset($currentToken) || empty($currentToken))
			{
				add_user_meta(get_current_user_id(), "ms_teams_token", strval($accessToken->getToken()));
				add_user_meta(get_current_user_id(), "ms_teams_refresh_token", $accessToken->getRefreshToken());
				add_user_meta(get_current_user_id(), "ms_teams_token_expiry", $accessToken->getExpires());
			}
			else {
				update_user_meta(get_current_user_id(), "ms_teams_token", strval($accessToken->getToken()));
				update_user_meta(get_current_user_id(), "ms_teams_refresh_token", $accessToken->getRefreshToken());
				update_user_meta(get_current_user_id(), "ms_teams_token_expiry", $accessToken->getExpires());
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
				delete_user_meta(get_current_user_id(), "ms_teams_token");
				
			}


			update_user_meta(get_current_user_id(), "ms_force_auth", 'false');

			delete_user_meta(get_current_user_id(), "ms_teams_redirect");
		

			$authUrl = $oauthClient->getAuthorizationUrl();

			// $authUrl .= "&resourceID=" . $_GET['resource'];
					
			// Request authorization from the user.

			echo "<script type=\"text/javascript\">";
			
			echo "window.location='$authUrl';";
			echo "</script>";
			die();

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

			    delete_user_meta(get_current_user_id(), "ms_teams_token");
			}					
		}



}

function submitQuizToMSTeamsX($quizdata, $user) {
	global $wpdb, $wp;

	$currentToken = get_user_meta(get_current_user_id(), "ms_teams_token", true);	
	$UID = get_current_user_id();
	if (isset($currentToken) && $currentToken != ""){	
		$isTeacher = false;
	
		$hasCourse = false;

		$headers = [
			'Authorization' => 'Bearer ' . $currentToken,
		];	

		require_once CLARENDON_MSAPI_DIR . 'oauth2-client/load.php';	
		$client = new \GuzzleHttp\Client();
		$myId = "";

		$meResponse = $client->request(
			'GET',
			'https://graph.microsoft.com/v1.0/me',
			array( 'headers' => $headers )
		);	
	

		$meResult = json_decode( $meResponse->getBody() );	

		if (is_array($meResult))
		{
			foreach ($meResult as $me){
				$myId = $me[0]->id;
			}
		}
		else {
			$myId = $meResult->id;
		}
	
		$teamId = "";
		$teachId = 0;
		$response = $client->request(
			'GET',
			'https://graph.microsoft.com/v1.0/education/classes',
			array( 'headers' => $headers )
		);	
		$result = json_decode( $response->getBody() );	
		if (!is_array($result)) $result = $result->value;
		$score = $quizdata['points'];
	
		foreach ($result as $team) {

			
				
				if (is_array($team)) {
					$teamId = $team[0]->id;
				}
				else $teamId = $team->id;

				//is the user a teacher of this class?

					if (!empty($teamId))
					{
						$teacherResponse = $client->request(
						'GET',
						'https://graph.microsoft.com/v1.0/education/classes/' . $teamId . '/teachers',
							array( 'headers' => $headers )
						);
				
						$teacherResult = json_decode( $teacherResponse->getBody() );
						$hasCourse = true;
						foreach ($teacherResult as $teacher) {
							foreach ($teacher as $teach)
							{
								if ($teach->id == $myId) {
									$isTeacher = true;
								}
							}
							$teacherUser = get_users(array('meta_key' => 'team_ID', 'meta_value' => $teamId));
							foreach ($teacherUser as $teach) {

								$teachID = $teach->ID;

							}
						}
					
					}

		}

			$longAnswer = "";

			$hasEssay = false;

			foreach ($quizdata['questions'] as $question) {

				$result = $wpdb->get_results("SELECT * FROM wp_posts INNER JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.id WHERE wp_postmeta.meta_key = 'question_id' AND wp_postmeta.meta_value = ".$question->getId()." AND post_author = " . $UID);


				if(count($result) > 0)
				{
					$hasEssay = true; 

					$error .= 'has Essay';

				}



				// foreach($result as $row) {

				// 	$longAnswer .= "Question: " . $row->post_title . "\n";

				// 	$longAnswer .= "Answer: " . $row->post_content . "\n";

				// }
			
			}



			// $error .= $longAnswer;


			if($hasEssay) $essay = "Y";
			else $essay = "N";

			$wpdb->insert( 'wp_grades', array(
			    'User_ID' => $UID,
			    'Teacher_ID' => $teachID,
			    'Grade' => $quizdata['points'],
			    'HasEssay' => $essay,
			    'ResourceID' => $quizdata['quiz']->ID,
			) );


			$lastid = $wpdb->insert_id;

		$resourceID = get_the_ID();

		$permalink = get_permalink();

		$assignmentResponse = $client->request(
			'GET',
			'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments',
			array( 'headers' => $headers )
		);
		$assignmentResult = json_decode( $assignmentResponse->getBody() );
		
		$permalink = get_permalink($resource) . "?source=teams";
		$assignmentId = "";		
		$submissionId = "";
		foreach ($assignmentResult as $assignment) {

			if ($assignment[0]->resources[0]->resource->link == $permalink) {
				$alreadyAssigned = true;
				$assignmentId = $assignment[0]->id;
				$submissionId = $assignment[0]->submissions[0]->id;
				
			}
												
		}	
		if (!empty($submissionId)) {

			if ($hasEssay)
			{
				$form_params = array();

				$submissionURL = 'https://' . $_SERVER['HTTP_HOST'] . "/assignment-answers/?uid=". get_current_user_id() . '&quid=' . $quizID ."&tid=" . $teachID;
				$resource = array();
				$form_params["assignmentResourceUrl"] = $permalink;
				$title = "Submitted Work";
				$resource["displayName"] = $title;
				$resource["link"] = $submissionURL;
				$cur_date = time();
																	
				$resource["@odata.type"] = "#microsoft.education.assignments.api.educationLinkResource";
														
				$form_params["resource"] = $resource;		
				$form_params = json_encode($form_params);
				$form_params = str_replace("\\/", "/", $form_params);
						
				$postheaders = [
					'Authorization' => 'Bearer ' . $currentToken,
					'Content-type' => 'application/json',
					'Content-length' => strlen($form_params)
				];
				//attach the resource.

				$submissionResponse = $client->request('POST',
					'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/submissions/' . $submissionId . '/resources/',
					array( 'headers' => $postheaders, 'body' => $form_params ),															
				);	

				$outcomeResponse = $client->request(
					'GET',
					'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/submissions/' . $submissionId . '/outcomes/',
					array( 'headers' => $headers )
				);

				$outcomeResult = json_decode( $outcomeResponse->getBody() );

			
				foreach ($outcomeResult->value as $outcome) {
					
					$outcomeArr = (array)$outcome;

					$isPoints = false;
					foreach ($outcomeArr as $key=>$val) {
						if ($val == "#microsoft.graph.educationPointsOutcome"){
							$isPoints = true;
						}
					}
					
					if ($isPoints) {
						$form_params = array();
						$form_params["@odata.type"] = "#microsoft.graph.educationPointsOutcome";
						$points = array();
						$points["@odata.type"] = "#microsoft.graph.educationAssignmentPointsGrade";
						$points["points"] = $score;
						$form_params["points"] = $points;
						 
				
						$teacherToken = get_user_meta($teachID, "ms_teams_token", true);
						$form_params = json_encode($form_params);
						$postheaders = [
							'Authorization' => 'Bearer ' . $teacherToken,
							'Content-type' => 'application/json',
							'Content-length' => strlen($form_params)
						];						
						$outcomeResponse = $client->request('PATCH',
							'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/submissions/' . $submissionId . '/outcomes/' . $outcome->id,
							array( 'headers' => $postheaders, 'body' => $form_params ),															
						);					
					}
					

				}

				
			}

			$submissionResponse = $client->request('POST',
				'https://graph.microsoft.com/beta/education/classes/' . $teamId . '/assignments/' . $assignmentId . '/submissions/' . $submissionId . '/submit/',
				array( 'headers' => $headers, 'body' => $form_params ),															
			);	

		}
			
	}

}



add_shortcode('AddToMicrosoftTeams', 'clarendon_msapi_add_to_teams');








?>