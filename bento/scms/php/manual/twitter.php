<?php
/*
@class: twitterConnect
@description: Loads a 3rdbparty twitter class and uses it to do twittery stuff
@params:
@shortcode:  
@return:
*/
class twitterConnect {

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

		// Let's create the object
		require_once "twitter/EpiCurl.php";
		require_once "twitter/EpiOAuth.php";
		require_once "twitter/EpiTwitter.php";
	
		// Here is the twi	
		$scms->twitter = new EpiTwitter($scms->private->twitter->key, $scms->private->twitter->secret);

		// Make sure we're not logged in
		if( (!$scms->logged_in() || !$scms->logged_in('twitter')) && isset($_GET["scms_twitter"]) ){
			
				// Set the object
				$scms->twitter->setToken($_GET["oauth_token"]);
				$token = $scms->twitter->getAccessToken();

				// Set the tokens to access information				
				$_SESSION["bento"]["scms"]["account"]["twitter"]["token"] = $token->oauth_token;
				$_SESSION["bento"]["scms"]["account"]["twitter"]["secret"] = $token->oauth_token_secret;
				
				$scms->twitter->setToken($token->oauth_token, $token->oauth_token_secret);
				$twitterInfo = $scms->twitter->get_accountVerify_credentials();
				$twitterInfo->response;

				// Check if there was an error or not
				if( isset($twitterInfo->error)){

					// Clear up the goods
					unset($_SESSION["bento"]["scms"]["account"]["twitter"]["token"]);
					unset($_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
					
					// Twitter is being a cunt!
					$scms->redirect(301,$scms->remembered(),array("scms_twitter_error"	=>	$twitterInfo->error));

				// Let's do some registrizing
				} else {

					// Check if there's a problem with twitter
					if( !isset($twitterInfo->id) ){

						// Clear up the junk
						unset($_SESSION["bento"]["scms"]["account"]["twitter"]["token"]);
						unset($_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
						
						// Twitter is being a cunt!
						$scms->redirect(301,$scms->remembered(),array("scms_twitter_error"	=>	"lost id"));
					
					// if
					}
					
					// Get the dbay
					$bday = 0;
					$twitter_id = $twitterInfo->id;
					$name = $twitterInfo->name;
					$image = $twitterInfo->profile_image_url;

					// Get the name
					if( stristr($name," ") ){
					
						$name = explode(" ",$name);
						$name_first = $name[0];
						$name_last = end($name);
					
					// if
					} else {
					
						$name_first = $name;
						$name_last = '';
						
					// if
					}						
					
					if( stristr($image,".") ){
					
						$tmp = explode(".",$image);
						$extension = end($tmp);
					
					}
								
					// Check if a user exists
					if( $scms->logged_in() && $db->select("account.id=" . $scms->account_id() ) ){

						// Let's create a new account
						$db->update(
								array(
									"table"	=>	"account",
									"values"	=>	array(
														"twitter_id"	=>	$twitter_id,
														"twitter_token"	=>	$_SESSION["bento"]["scms"]["account"]["twitter"]["token"],
														"twitter_secret"	=>	$_SESSION["bento"]["scms"]["account"]["twitter"]["secret"],
														"name_first"	=>	$name_first,
														"name_last"	=>	$name_last,
														"verify"	=>	1,
														"gender"	=>	0,
														"password"	=>	$twitter_id,
														"token"	=>	$twitter_id,
														"birthday"	=>	time(),
														"date_edited"	=>	time(),
														"extension"	=>	$extension
													),
									"criteria"	=>	"id=" . $scms->account_id() 
									)
								);	
									
						// get the record	
						$db->select("account.id=" . $scms->account_id()  );	

					// Check if a user exists
					} else if( !$scms->logged_in() && $db->select("account.twitter_id=" . $twitter_id ) ){
					
						$db->clear("account");
					
						// Let's create a new account
						$db->update(
								array(
									"table"	=>	"account",
									"values"	=> array(
														"twitter_id"	=>	$twitter_id,
														"twitter_token"	=>	$_SESSION["bento"]["scms"]["account"]["twitter"]["token"],
														"twitter_secret"	=>	$_SESSION["bento"]["scms"]["account"]["twitter"]["secret"],
														"name_first"	=>	$name_first,
														"name_last"	=>	$name_last,
														"verify"	=>	1,
														"password"	=>	$twitter_id,
														"token"	=>	$twitter_id,
														"birthday"	=>	time(),
														"date_edit"	=>	time(),
														"extension"	=>	$extension
													),
									"criteria"	=>	"twitter_id=" . $twitter_id
									)
								);	
									
						// get the record	
						$db->select("account.twitter_id=" . $twitter_id );				
					
					// if
					} else {
					
						// Let's create a new account
						$response = $db->insert(
											array(
												"table"	=>	"account",
												"values" => array(
																"twitter_id"	=>	$twitter_id,
																"twitter_token"	=>	$_SESSION["bento"]["scms"]["account"]["twitter"]["token"],
																"twitter_secret"	=>	$_SESSION["bento"]["scms"]["account"]["twitter"]["secret"],
																"name_first"	=>	$name_first,
																"name_last"	=>	$name_last,
																/*"email"	=>	$email,*/
																"gender"	=>	"",
																"verify"	=>	1,
																"password"	=>	$twitter_id,
																"token"	=>	$twitter_id,
																"birthday"	=>	time(),
																"date_created"	=>	time(),
																"extension"	=>	$extension
															)
														)
													);
									
						// get the record	
						$db->select("account.id=" . $response );
					
					// if
					}
					
					// Copy the picture from twitter
					$tmp = $_SERVER['DOCUMENT_ROOT'] . "/images/profile/" . $twitter_id . "." . $extension;
					
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
							
					// Redirect to where we were
					$scms->redirect(301,$scms->remembered());
	
			// if
			}
			
		// if
		}
		
	// method
	}

	/*
	@method: url()
	@description: Gets the login url for twitter
	@params:
	@shortcode:  
	@return:
	*/
	public function url(){

		global $scms;
	
		$tmp = $scms->twitter->getAuthorizationUrl();
		
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
	@description: Posts to twitter for the user logged in
	@params:
	@shortcode:  
	@return:
	*/
	public function post( $status ){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
		
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();
			
			$statuses = $scms->twitter->get_statusesUser_timeline();
			$statuses->response[0]->text;
			
			// Check if it already response
			if( isset($statuses->response) && isset($statuses->response[0]) && trim($statuses->response[0]->text) == trim($status) ){
			
				//print_r( $statuses->response[0] );
			
				// Return it so we get a positive message
				return $statuses->response[0];
			
			// if
			}
			
			// This is it here		
			$result = $scms->twitter->post_statusesUpdate(array('status'=>$status));
			
			// Check ift
			return (object)$result->response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: feed()
	@description: get's the logged in users timeline (feed)
	@params:
	@shortcode:  
	@return:
	*/
	public function feed(){ return $this->home_timeline(); }
	public function home_timeline(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_statusesHome_timeline();
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: public_timeline()
	@description: get's the logged in users public timeline (feed)
	@params:
	@shortcode:  
	@return:
	*/
	public function public_timeline(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);

			// This is it here		
			$response = $scms->twitter->get_statusesPublic_timeline();
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: friends_timeline()
	@description: get's the logged in users timeline of a friend
	@params:
	@shortcode:  
	@return:
	*/
	public function friends_timeline(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_statusesFriends_timeline();
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: public_timeline()
	@description: This method is identical to statuses/home_timeline, except that this method will only include retweets if the include_rts parameter is set. The RSS and Atom responses will always include retweets as statuses prefixed with RT.
	@params:
	@shortcode:  
	@return:
	*/
	public function posts(){ return $this->user_timeline(); }
	public function user_timeline(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_statusesUser_timeline();
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: mentions()
	@description: get's the metions of the logged in users.
	@params:
	@shortcode:  
	@return:
	*/
	public function mentions(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_statusesMentions();
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}


	/*
	@method: friends()
	@description: get's the friends of the logged in users.
	@params:
	@shortcode:  
	@return:
	*/
	public function friends(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_statusesFriends();
			
			// Check it
			return $response;
	
		// if
		}
	
		return false;
	
	// method
	}

	/*
	@method: followers()
	@description: get's the friends of the logged in users.
	@params:
	@shortcode:  
	@return:
	*/
	public function followers(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_statusesFollowers();
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: nearby_places()
	@description:  Search for places (cities and neighborhoods) that can be attached to a statuses/update.  Given a latitude and a longitude pair, or an IP address
	@params:
	@shortcode:  
	@return:
	*/
	public function nearby_places(){
		
		global $scms;
		
		// Make sure we're logged in
		if( $scms->logged_in("twitter") ){
	
			// Set the tokens to access information				
			$scms->twitter->setToken($_SESSION["bento"]["scms"]["account"]["twitter"]["token"], $_SESSION["bento"]["scms"]["account"]["twitter"]["secret"]);
			$twitterInfo = $scms->twitter->get_accountVerify_credentials();

			// This is it here		
			$response = $scms->twitter->get_geoNearby_places($_SERVER['REMOTE_ADDR']);
			
			// Check it
			return $response;
	
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: response()
	@description:  returns bool for the response message payload
	@params:
	@shortcode:  
	@return:
	*/
	public function response( $message ){

		// Check it
		if( isset($message) && isset($message->id) ){
			return $message->id;
		} else {
			return false;
		}
	
	// method
	}

// class
} ?>