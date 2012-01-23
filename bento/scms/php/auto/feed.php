<?php
// Make sure the file exists
if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/feed.php" ) ){ 
	require_once $_SERVER['DOCUMENT_ROOT'] . "/feed.php";
// if
}

// This is for some core functionality
class scms_feed_core {
	
	// Declate it
	public $private;

	/*
	@method: feed( $response, $xhr=true )
	@description: This handles the polling of the server
	@params:
	@shortcode:  
	@return:
	*/
	public function __construct(){
	
		$this->private = (object)array();	
		
	// method
	}
	
	/*
	@method: feed( $response, $xhr=true )
	@description: This handles the polling of the server
	@params:
	@shortcode:  
	@return:
	*/
	public function feed( $response, $xhr=true, $variables=array() ){
		
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}				
				
		// Get some default values
		$db->select("setup");
				
		// Set up the method based on page
		if( !isset($scms->method) ){
			
			$method = $form->post("scms_feed_slug");
			$scms->method = $method;

		// if			
		} else {
		
			// Set this again in ca
			$method = $scms->method;
		
		// if
		}
				
		// Get the feed variables
		if( $xhr ){
		
			$feed_variables = (object)json_decode(html_entity_decode(stripslashes($form->post("scms_feed_variables"))));
			$feed_variables->inline = false;
			
			// Get the support class
			$support_class = $form->post("scms_feed_plugin") . "_feed";			
			
			// Set the plugin
			$scms->is_plugin( $form->post("scms_feed_slug") );

		} else {
			
			// Check if it was set as feed variables, or passed as parater
			if( count($variables) == 0 ){
		
				// Now do it
				$feed_variables = (object)$scms->private->feed_variables;
			
			// Otherwise, use what was passwd	
			} else {
				
				$feed_variables = (object)$variables;
				
			// if
			}
			
			$feed_variables->inline = true;

			// Get the support class
			$support_class = $scms->is_plugin() . "_feed";
		
		// if
		}
		
		// Tell the program we haven't yet handled the feed
		$handled = false;
				
		// Set if it's a new search
		$scms->private->feed_new = (isset($_SESSION["bento"]["scms"]["feeds"]["search"][ $method ]) && isset($feed_variables) && $_SESSION["bento"]["scms"]["feeds"]["search"][ $method ] != json_encode($feed_variables));

		// Set what we searched for
		$_SESSION["bento"]["scms"]["feeds"]["search"][ $method ] = json_encode($feed_variables);
		
		// Here it is 
		$feeds = array();
		$html = array();
		$combined = array();
		
		// Check if this is xhr
		if( $xhr ){
	
			// Check if there are new permissions
			$scms->update_permissions();
			
			// Check if they should be 
			if( $scms->check_permissions() ){ 
			
				// Logged out
				$forward = false;
				
			// Check this out
			} else {

				// Get the forward
				$forward = $scms->is_forward();			
				
			// if
			}

			// This will return if we're logged in ot not
			$notify = array( 
							"logged_in"	=>	array(
												"scms"	=>	$scms->logged_in(),
												"facebook"	=>	$scms->logged_in("facebook"),
												"twitter"	=>	$scms->logged_in("twitter")
												),
							"forward"	=>	$forward,
							"notifications"	=>	(int)$db->select_count("notification.account_id=" . $scms->account_id() . " and notification.state=1")
							);
		
			// Check if we're logged in
			$feeds = $this->notifications( $feed_variables );
			
		// if
		}
		
		// Look for a custom method		
		if( method_exists( $this, $method ) ){
		
			// Load the method
			$feeds = array_merge($feeds,$this->$method( $feed_variables ));
			$handled = true;
			
		// If it doesn't exists, just call hommie
		} 
		
		// Look for a load
		if( method_exists( $support_class, "__load" ) && $xhr ){
	
			// Load the method
			$feeds = array_merge($feeds,$GLOBALS[ $support_class ]->__load( $feed_variables ));
			$handled = true;
			
		// if
		}

		// Look for a custom method		
		if( method_exists( $support_class, $method ) ){
		
			// Load the method
			$feeds = array_merge($feeds,$GLOBALS[ $support_class ]->$method( $feed_variables ));
			$handled = true;
			
		// If it doesn't exists, just call hommie
		} 
		
		// Close it all
		if( method_exists( $support_class, "__unload" ) && $xhr ){
	
			// Load the method
			$feeds = array_merge($feeds,$GLOBALS[ $support_class ]->__unload( $feed_variables ));
			$handled = true;
			
		// if
		}
		
		// We didn't have anything to do
		if( !$handled ){
		
			// Check it returning
			if( $xhr ){ 
			
				// Respond with the error
				$form->response(
								array(
									"response"	=>	true,
									"message"	=>	"Nothing to do."
									)
								);
				
				// Kill it
				die();
				
			// This is a feed
			} else {
			
				// Return nothing
				return false;
			
			// if
			}
		
		// Otherwise
		} else {
			
			// Translate things
			if( count($feeds) && !isset($feeds[0]) ){
			
				// Just do it
				$feeds = array($feeds);
			
			// if
			}
	
			// Loop through the feeds
			foreach( $feeds as $z => $f ){
			
				// Check if we have a set (a set is a category of of feeds)
				if( !isset($feeds[ $z ]["plugin"]) ){
				
					$feeds[ $z ]["plugin"] = $scms->is_plugin();
					
				// if
				}

				// Check if we have a set (a set is a category of of feeds)
				if( !isset($feeds[ $z ]["set"]) ){
				
					$feeds[ $z ]["set"] = $scms->is_slug();
					
				// if
				}

				// Check if we have a set (a set is a category of of feeds)
				if( !isset($feeds[ $z ]["id"]) ){
				
					$feeds[ $z ]["id"] = str_replace(".","",microtime(true));
					
				// if
				}

				// A randomized number in the event we need it (to help with logging)
				if( !isset($feeds[ $z ]["random"]) ){
				
					$feeds[ $z ]["random"] = str_replace(".","",microtime(true));
					
				// if
				}
	
				// Clear up stuff
				if( isset($feeds[ $z ]["unset"]) ){ 
				
					// Remove it from the session
					unset( $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $feeds[ $z ]["plugin"] ][ $feeds[ $z ]["unset"] ] );
		
					// Unset it so it doesn't make it's way to the response payload
					unset( $feeds[ $z ]["unset"] );
					
				// if
				}
				
				// Clear up stuff
				if( isset($feeds[ $z ]["clear"]) ){ 
				
					// Check if we're clearing something ups
					unset( $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $feeds[ $z ]["plugin"] ][ $feeds[ $z ]["set"] ][ $feeds[ $z ]["clear"] ] );
		
					// Unset it so it doesn't make it's way to the response payload
					unset( $feeds[ $z ]["clear"] );
					
				// if
				}
					
				// Clear previously load
				if( isset($_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $feeds[ $z ]["plugin"] ][ $feeds[ $z ]["set"] ][ $feeds[ $z ]["id"] ]) ){
				
					unset( $feeds[ $z ] );
					
					continue;
				
				// if
				}
				
				// Check if this is xhr or not
				if( $xhr ){
	
					// Update the variabled
					foreach( array("container","place","load") as $variable ){
					
						// Make sure we didn't set it in the area
						if( !isset($feeds[ $z ][ $variable ])){
					
							$feeds[ $z ][ $variable ] = $db->record("setup.feed_" . $variable );
							
						// if
						}
					
					// if
					}
					
					// Add a max length so we can remove junk
					if( !isset($feeds[ $z ]["max"]) ){
						
						$feeds[ $z ]["max"] = 0;
						
					// if
					}
		
				// Otherwise, it's inline so we format it now
				}
										
				// Make sure we're good
				if( isset($feeds[ $z ]) && !isset($_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $feeds[ $z ]["plugin"] ][ $feeds[ $z ]["set"] ][ $feeds[ $z ]["id"] ]) ){
			
					// Log the feed
					$_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $feeds[ $z ]["plugin"] ][ $feeds[ $z ]["set"] ][ $feeds[ $z ]["id"] ] = $feeds[ $z ]["id"];
			
					// Clear up additional
					unset( $feeds[ $z ]["plugin"] );
					
				// if
				}
				
				// Make sure we're good
				if( isset($feeds[ $z ]) && isset($feeds[ $z ]["combined"]) ){
				
					// This is what we're working with
					$tmp = array();
				
					// Update the variabled
					foreach( array("id","container","place","load","combined","max","action") as $variable ){
					
						// Make sure we didn't set it in the area
						if( isset($feeds[ $z ][ $variable ])){
					
							$tmp[ $variable ] = $feeds[ $z ][ $variable ];
							
						// if
						}
					
					// if
					}	
					
					// Change combined to the requisite html
					$tmp["html"] = $tmp["combined"]; unset($tmp["combined"]);
					
					// Add tis
					$combined[] = $tmp;

					// Clear up additional
					unset( $feeds[ $z ] ); unset($tmp);
					
				// if
				}
				
				// Make sure we're good
				if( isset($feeds[ $z ]["feed"]) && !isset($feeds[ $z ]["combined"]) && !isset($_SESSION["bento"]["scms"]["feeds"]["logs"]["html"][ $feeds[ $z ]["feed"] ] ) ){
	
					// Log the feed
					$_SESSION["bento"]["scms"]["feeds"]["logs"]["html"][ $feeds[ $z ]["feed"] ] = true;
	
					// Format the feed
					$tmp = $scms->feed_html( $feeds[ $z ] );
					
					// Clear this up
					if( $tmp ){
					
						$html[] = $tmp;
						
					// if
					}				
					
				// if
				}
			
			// foreach
			}
						
			// Check if we're reloading content
			/*
			if( $form->post("bento_scms_reload") && !isset($_SESSION["bento"]["scms"]["feeds"]["logs"][ "reload-" . date("Gi") ]) ){
			
				// Set it
				$_SESSION["bento"]["scms"]["feeds"]["logs"][ $feeds[ $z ]["plugin"] ][ "reload-" . date("Gi") ] = true;
			
				// Tell the program which page we're on
				$scms->private->on_page = $method;
			
				// Get a new version of the page
				$variables["feeds"][] = array(
											"id"	=>	"reload-" . date("Gi"),
											"html"	=>	$scms->page(true),
											"load"	=>	true,
											"container"	=> "page",
											"place"	=>	"overwrite"
											);
			
			// if
			}*/
			
			// Clear the setup variables
			$db->clear("setup");
	
			// Check it returning
			if( $xhr ){ 
			
				// Return the methods 
				$form->response(
								array(
									"response"	=>	true,
									"message"	=>	"Feed loaded from " . $method . ".",
									"variables"	=>	array(
														"html"	=>	$html,
														"data"	=>	array_values($feeds),
														"combined"	=>	$combined,
														"time" =>	(int)$db->record("setup.feed_time")*1000
														),
									"actions"	=>	"bento.scms.feed.notify(" . json_encode($notify) . ");"
									)
								);
			
			// This is inline	
			} else {
				
				// Get the html
				$scms->public->feed->html = array_merge($scms->public->feed->html,$html);
				$scms->public->feed->data = array();
				$scms->public->feed->combined = array();
			
				// Write a container if there's only one feed
				if( $feed_variables->wrap ){
			
					// Output the container
					echo "<" . $feed_variables->wrapper . " id=\"" . $method . "\">\r\n";
			
				// if
				}
				
				// Loop through the feeds for html
				foreach( $feeds as $feed ){
					
					// If there's an action add it to the queue
					if( isset($feed["action"]) ){
					
						$bento->add_action( $feed["action"] ); 
					
					// if		
					}
					
					// Chek if it's already been combined
					if( !isset($feed["html"]) ){
				
						// Output the html
						echo $scms->feed_combine(
												array(
													"feed"	=>	$feed["feed"], 
													"feeds"	=>	$feeds,
													"html"	=>	$scms->public->feed->html 
													),
												true
												);
					
					// It's been combined already	
					} else {
						
						echo $feed["html"];
						
					// if
					}
							
				// foreach
				}
				
				// Loop through the feeds for html
				foreach( $combined as $feed ){
					
					// If there's an action add it to the queue
					if( isset($feed["action"]) ){
					
						$bento->add_action( $feed["action"] ); 
					
					// if		
					}
					
					// Output it
					echo $feed["html"];
							
				// foreach
				}
								
				// Close the container if there's only one feed
				if( $feed_variables->wrap ){
	
					// Close the container
					echo "\r\n</" . $feed_variables->wrapper . ">";
	
				// if
				}
	
			// if
			}
			
		// if
		}
		
		// Clear this up
		$db->clear("setup");
		
	// method
	}	
	
	/*
	@method: feed( $response, $xhr=true )
	@description: This handles the polling of the server
	@params:
	@shortcode:  
	@return:
	*/
	public function notifications( $variables ){
	
		global $db,$scms,$form;
		
		// Get the array
		$feeds = array();
		$notes = array();
		
		// Check if we have any notifications
		if( $scms->logged_in() ){
		
			// Check it out
			$db->select("notification.account_id=" . $scms->account_id() . " limit 0,3 order by notification.time desc");
		
		// if
		}	
		
		// This is where we'll store how many unique notifcations there are
		$tmp = array();
		$check = array();
		
		// Loop through the notifications
		foreach( $db->recordset("notification") as $notification ){
			
				// Check it out
				if( !isset($tmp[ $notification["id"] ]) ){
			
					$tmp[ $notification["id"] ] = 1;
			
				// Add it up
				} else {
				
					$tmp[ $notification["id"] ]++;
		
				// if
				}
		
		// foreach
		}
		
		// Loop through the notifications
		foreach( $db->recordset("notification") as $notification ){
			
			// Let's check it all out
			if( !in_array($notification["id"],$check) ){
				
				// We only need to show it once
				$check[] = $notification["id"];
		
				// Get the feeds
				$feeds[] = array(
							"plugin"	=>	"scms",
							"id"	=>	"notification-" . $notification["id"],
							"feed"	=>	"notifications",
							"container"	=>	"notifications",
							"message"	=> $notification["message"],
							"url"	=> $notification["url"],
							"place"	=>	"top",
							"load"	=>	true,
							"time"	=>	$scms->timestamp($notification["time"],false),
							"max"	=>	3
							);
							
			// if
			}
						
		// foreach
		}
		
		// Clear up notifications
		$db->clear("notification");
					
		// return it
		return $feeds;
		
	// method
	}
	
// class
}

// Here are some core handling files
$GLOBALS["scms_feed_core"] = new scms_feed_core();
?>