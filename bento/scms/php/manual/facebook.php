<?php
/*
@class: facebook
@description: deals connecting to facebook
@params:
*/
class facebookConnect {

	// Load the variables 
	public $public;
	public $private;

	/*
	@method: __construct()
	@description: Loads 3rd party classes
	@params:
	@shortcode:  
	@return:
	*/
	public function __construct(){
	
		global $scms,$db;
	
		// Include the facebook library
		require_once "facebook/facebookExt.php";
		
		// Setup the facebook variables
		$scms->facebook = new Facebook(array(
		  'appId'  => $scms->private->facebook->app_id,
		  'secret' => $scms->private->facebook->secret,
		  'cookie' => true, // enable optional cookie support
		));
	
		// Make sure we're not logged in
		if( !$scms->logged_in() || !$scms->logged_in('facebook') ){ 

			try {
			  $me = $scms->facebook->api('/me');
			} catch (FacebookApiException $e) { $me = NULL; }
						
			// Check if we're add to our records
			if( isset($_GET["scms_facebook"]) /*&& !is_null($me)*/ ){ 
			
				$db->output();
			
				// Get the dbay
				$bday = explode("/",$me["birthday"]);
				
				// Check tis out
				if( strtolower($me["gender"]) == "male" ){
				
					$gender = 1;
					
				} else if( strtolower($me["gender"]) == "female" ){
				
					$gender = 2;
					
				} else {
				
					$gender = 0;
				
				}
				
				// Check this out
				if( isset($bday[0]) && isset($bday[1]) && isset($bday[2]) ){
					$bday = mktime(0,0,0,$bday[0],$bday[1],$bday[2]);
				} else { 
				
					$bday = 0;
				}
				
				$image = 'https://graph.facebook.com/' . $me["id"] . '/picture';
						
				// Get the session
				$session = json_decode(str_replace("\\","",$_GET["session"]));
			
				// check if the email exists
				if( $scms->logged_in() && $db->select("account.id=" . $scms->account_id() ) ){
							
					// Clear the account up
					$db->clear("account");
				
					// Let's update an existing account
					$db->update(
								array(
									"table"	=>	"account",
									"values"	=>	array(
														"facebook_id"	=>	$me["id"],
														"facebook_token"	=>	$session->access_token,
														"facebook_secret"	=>	$session->secret,
														"name_first"	=>	$me["first_name"],
														"name_last"	=>	$me["last_name"],
														"gender"	=>	$gender,
														"extension"	=>	"jpg",
														"date_edited"	=>	time(),
													),
									"criteria"	=>	"id=" . $scms->account_id()
									)	
								);
									
					$db->select("account.id=" .  $scms->account_id() );
				
				// check if the email exists
				} else if( !$scms->logged_in() && $db->select("account.email=" . $me["email"] ) ){
							
					// Clear the account up
					$db->clear("account");
				
					// Let's update an existing account
					$db->update(
								array(
									"table"	=>	"account",
									"values"	=> array(
														"facebook_id"	=>	$me["id"],
														"facebook_token"	=>	$session->access_token,
														"facebook_secret"	=>	$session->secret,
														"name_first"	=>	$me["first_name"],
														"name_last"	=>	$me["last_name"],
														"gender"	=>	$me["gender"],
														"extension"	=>	"jpg",
														"date_edit"	=>	time(),
													),
									"criteria"	=>	"email='" . $me["email"] . "'"
									)
								);
									
					$db->select("account.email=" .  $me["email"] );
										
				// Let's update the information from facebook
				} else {
					
					// Let's create a new account
					$response = $db->insert(
										array(
											"table"	=>	"account",
											"values"	=>	array(
																"facebook_id"	=>	$me["id"],
																"facebook_token"	=>	$session->access_token,
																"facebook_secret"	=>	$session->secret,
																"name_first"	=>	$me["first_name"],
																"name_last"	=>	$me["last_name"],
																"email"	=>	$me["email"],
																"gender"	=>	$me["gender"],
																"verify"	=>	$me["verified"],
																"password"	=>	"",
																"token"	=>	"",
																"birthday"	=>	$bday,
																"date_created"	=>	time(),
																"extension"	=>	"jpg"
															)
											)
									);
									
					$db->select("account.id=" . $response );
														
				// if
				}

				// Copy the picture from facebook
				$tmp = $_SERVER['DOCUMENT_ROOT'] . "/images/profile/" . $me["id"] . ".jpg";
				
				// Copy the image to our server
				copy($image,$tmp );
			
				require_once $_SERVER['DOCUMENT_ROOT'] . '/bento/form/libraries/SimpleImage.php';
				
				$image = new SimpleImage();
				$image->load( $tmp );
				$image->resizeToWidth(50);
				$image->save( $_SERVER['DOCUMENT_ROOT'] . "/images/profile/" . $db->record("account.id") . ".jpg" );
				unlink( $tmp );	
				
				// Log us into the system
				$scms->login(false);	
				
				// Clear up teh junk
				$db->clear("account");
	
				// Redirect to where we were
				$scms->redirect(301,$scms->remembered());

			// if
			}
		
		// if
		}
		
		// Check if we're add to our records
		if( isset($_GET["scms_facebook"]) ){ 
		
			// Redirect to where we were
			$scms->redirect(301,$scms->remembered());
			
		// if
		}
		
	// method
	}

	/*
	@method: request()
	@description: sends a request to facebook
	@params:
	@shortcode:  
	@return:
	*/
	public function request( $url, $params, $post=false ){
	
			// Do it up!!
			$ch = curl_init();
			
			// Here are the options
			$options = array(
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_VERBOSE => true
						);
						
			// Check if we're posting or not
			if( $post ){
			
				$options[ CURLOPT_URL ] = $url;
				$options[ CURLOPT_POSTFIELDS ] = http_build_query($params);
				
			// if
			} else {
			
				$options[ CURLOPT_URL ] = $url . "?" . http_build_query($params);
	
			// if
			}
			
			// Set the options
			curl_setopt_array($ch, $options);
		
			// Get the result
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			// Fantastic!!!
			curl_close($ch);
			
			// return it
			return $response;
			
	// method
	}

	/*
	@method: url()
	@description: get's the facebook login url
	@params:
	@shortcode:  
	@return:
	*/
	public function url(){

		global $scms;
	
		$tmp = $scms->facebook->getLoginUrl(array(
												"req_perms" => $scms->private->facebook->perms,
												"next"	=>	"http://" . $_SERVER['HTTP_HOST'] . "?scms_facebook"
												)
											);
		
		// If we're in a modal window fix it
		if( $scms->is_modal() ){ 
		
			$tmp = "javascript:window.parent.document.location.href = '" . $tmp . "';";
			
		// if
		}

		return $tmp;

	 // method
	}

	/*
	@method: post()
	@description: posts something to facebook for the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function post( $variables ){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
		
			// First let's NOT double post anything
			$last_post = $this->feed();
			$last_post = $last_post->data;
			
			// Cancel if we've posted this already
			if( (isset($last_post[0]->message) && $last_post[0]->message == $variables["message"]) || ( isset($last_post[0]->description) && $last_post[0]->description == $variables["description"] ) ){
			
				// Tell the world we're good
				return $last_post[0];
			
			// if
			}
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/me/feed';
			
			// Setup the variables
			foreach( array('name','link','caption','message','actions','picture') as $tmp ){
		
				if( isset( $variables[ $tmp ] ) ){ $params[ $tmp ] = $variables[ $tmp ];}
			
			// foreach
			}
			
			// Add a uid so it doesn't cache
			if( isset($params["link"]) ){
				if( stristr($params["link"],"?") ){
					$params["link"] .= "&" . time();
				} else {
					$params["link"] .= "?" . time();
				// if
				}
			// if	
			}
			
			// Send it over
			$response = $this->request( $url, $params, true );
			
			// Return the response
			return $response;
	
		// if
		} 
		
		return false;
	
	// method
	}

	/*
	@method: comments()
	@description: sends a comment to the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function comment( $variables ){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/' . $variables["id"] . "/comments/";
			
			// Setup the variables
			foreach( array('message') as $tmp ){
		
				if( isset( $variables[ $tmp ] ) ){ $params[ $tmp ] = $variables[ $tmp ];}
			
			// foreach
			}
			
			// Send it over
			$response = $this->request( $url, $params, true );;
			
			// Return the response
			return $response;
	
		// if
		}
	
	// method
	}


	/*
	@method: __construct()
	@description: gets a list of friends for the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function friends(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/me/friendlists';
			
			// Echo it out
			$response = $this->request( $url, $params );
			
			// Return the response
			return $response;
	
		// if
		}
	
	// method
	}

	/*
	@method: __construct()
	@description: gets the feed for the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function feed(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/me/feed';
			
			// Echo it out
			$response = $this->request( $url, $params );
			
			// Return the response
			return $response;
	
		// if
		}
	
	// method
	}

	/*
	@method: news()
	@description: gets news for the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function news(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/me/home';
			
			// Echo it out
			$response = $this->request( $url, $params );
			
			// Return the response
			return $response;
	
		// if
		}
	
	// method
	}

	/*
	@method: posts()
	@description: reads the posts from the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function posts(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/me/posts';
			
			// Echo it out
			$response = $this->request( $url, $params );
			
			// Return the response
			return $response;
	
		// if
		}
	
	// method
	}

	/*
	@method: photos()
	@description: gets posted photos of the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function photos(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("facebook") ){
	
			// Check what we're posting here
			$params["access_token"] = $_SESSION["bento"]["scms"]["account"]["facebook"]["token"];
			$url = 'https://graph.facebook.com/me/photos';
			
			// Echo it out
			$response = $this->request( $url, $params );
			
			// Return the response
			return $response;
	
		// if
		}
	
	// method
	}

	/*
	@method: response()
	@description: gets the response of posts and request to facebook for the logged in user
	@params:
	@shortcode:  
	@return:
	*/
	public function response( $message ){
		
		if( isset($message) && isset($message->id) ){
			return true;
		} else {
			return false;
		}
	
	// method
	}

// class
} ?>