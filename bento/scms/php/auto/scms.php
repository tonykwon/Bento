<?php
/*
@class: scms
@description: Creates the scms class
@params:
*/
class scms {

	// Load the variables 
	public $public;
	public $private;
	public $html;

	/*
	@method: __construct()
	@description: Assign class variables from the database
	@params:
	@shortcode:  
	@return:
	*/
	public function __construct(){
	
		global $bento;

		// Load this when
		$bento->add_event('all','loaded','remember');
		$bento->add_event('all','loaded','output');

		// This will add the map form at the end
		$bento->add_event('bento','loaded','feed_form');
		
		// Add the shortcodes
		$bento->add_shortcode("<!--scms:include:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:feed:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:page-->");
		$bento->add_shortcode("<!--scms:link:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:button:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:url:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:account:name-->");
		$bento->add_shortcode("<!--scms:profile_image-->");
		$bento->add_shortcode("<!--scms:querystring:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:q:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:language-->");
		$bento->add_shortcode("<!--scms:admin:([^>]+|)-->");
		$bento->add_shortcode("<!--scms:breadcrumb-->");
		$bento->add_shortcode("<!--scms:sitemap-->");
		$bento->add_shortcode("<!--scms:meta:title-->");
		$bento->add_shortcode("<!--scms:meta:description-->");
		$bento->add_shortcode("<!--scms:switch:([^>]+|)-->");
		
	// method
	}

	/*
	@method: __configure()
	@description: Set some more variables
	@params:
	@shortcode:  
	@return:
	*/
	public function __configure(){
	
		global $db,$bento;
		
		// Check if this is setup yet
		if( !$db->table_exists("setup") || !$db->table_exists("plugins") || $this->state != "installed" ){
		
			return false;
		
		// if
		}
		
		// Load up the setup variables
		$db->select("setup");
		
		// This is for the setup
		$tmp = $db->recordset("setup");

		// Loop through each
		foreach( $tmp[0] as $variable => $value ){
		
			// We don't need the id
			if( $variable != "id" ){
		
				$this->public->{ $variable } = $value;
				
			// if
			}
		
		// foreach
		}

		// Clear up the records
		$db->clear("setup");
	
		// Check it out
		$this->public->feed_time = $this->public->feed_time*1000;
		
		// Change the plugin directory
		$this->private->directory->plugins = $_SERVER['DOCUMENT_ROOT'] . "/". $this->private->directory->plugins; 
		
		// Load up the setup variables
		$db->select("plugins");

		// Loop through each
		foreach( $db->recordset("plugins") as $plugin ){
		
			// Get the plugin
			$this->private->plugins[] = $plugin["name"];
		
		// foreach
		}

		// Clear up the records
		$db->clear("plugins");
	
		// These are the default option
		$this->private->page = array();
		$this->private->setup = array("home"	=>	false, "error"	=>	false,	"admin"	=>	false,	"help"	=>	false );

		// make sure the page file exists
		$bento->add_php(
						array(
							"plugin"	=>	"scms",
							"name"	=> "page"
						)
					);
						
		// Add this into the fray
		$GLOBALS["scms_page"] = new scms_page();
			
		// make sure the url construction file exists
		$bento->add_php(
						array(
							"plugin"	=>	"scms",
							"name"	=> "handler"
							)
						);					
		
		// Add this into the fray
		$GLOBALS["scms_handler"] = new scms_handler();	

		// make sure the url construction file exists
		$bento->add_php(
						array(
							"plugin"	=>	"scms",
							"name"	=> "url"
						)
					);
					

		// Add this into the fray
		$GLOBALS["scms_url"] = new scms_url();	
			
		// make sure the url construction file exists
		$bento->add_php(
						array(
							"plugin"	=>	"scms",
							"name"	=> "feed"
						)
					);
		
		// Add this into the fray
		$GLOBALS["scms_feed"] = new scms_feed();

		// Make sure we want to connect to facebook
		$bento->add_php(
						array(
							"plugin"	=>	"scms",
							"name"	=> "facebook"
						)
					);
		
		// Add this into the fray
		$GLOBALS["facebook"] = new facebookConnect();

		// Make sure we want to connect to facebook
		$bento->add_php(
						array(
							"plugin"	=>	"scms",
							"name"	=> "twitter"
						)
					);
		
		// Add this into the fray
		$GLOBALS["twitter"] = new twitterConnect();
	
		// This is up
		$this->private->feed_variables = array("reload"	=>	$this->public->feed_reload );
		
		// Now let's load the plugins
		$this->plugins();
		
		return true;
		
	// method
	}

	/*
	@method: __load()
	@description: Starts it all up
	@params:
	@shortcode:  
	@return:
	*/
	public function __load(){
	
		global $db,$bento,$form,$scms_page,$scms_page_core,$language;

		// Check if we're secure or not
		( $_SERVER["SERVER_PORT"] != 443 ) ? $this->private->http = "http://" : $this->private->http = "https://";
	
		// Before we get to the niceties, this might be an app that requires the assets to start
		if( isset($_GET[ $this->public->app->querystring ]) ){
		
			// We're going to response with assets
			if( $_GET[ $this->public->app->querystring ] != "data" ){
			
				$this->app( $_GET[ $this->public->app->querystring ] );
			
			// if
			}
			
		// if
		} else {
			
			// Fist check fo user agents to skip stuff
			if( !in_array($_SERVER['HTTP_USER_AGENT'],$this->private->agents) && !isset($_GET["scms_facebook"]) && !isset($_GET["scms_twitter"]) ){
		
				// First let's check if this if mobile
				if( !isset($_SESSION["bento"]["scms"]["mode"]) && !$this->q("scms_mode") && !isset($_GET[$form->public->querystring]) ){
			
					// Include the library
					$bento->add_php(
									array(
										"plugin"	=>	"scms",
										"name"	=> "mobile"
									)
								);
					
					// Get the page
					$request = parse_url($_SERVER['REQUEST_URI']);
					
					// Make it easy
					if( !isset($request["query"]) ){ $request["query"] = ""; }
					
					// Check for a json qs
					$json_qs = json_decode((string)htmlspecialchars_decode(urldecode($request["query"])));
					
					if( $json_qs ){
						
						$qs = (array)$json_qs;
						
					} else {
					
						// Set the querystring
						parse_str($request["query"],$qs);
						
					// if
					}
	
					// Get the page
					$page = $request["path"] . "?" . htmlentities(urlencode(json_encode(array_merge( array("scms_mode"	=>	true), $qs))));	
				
					// Check what we we forward to
					if( $_SERVER["HTTP_HOST"] == $this->public->domain_facebook ){
				
						$web = $this->public->domain_facebook;
	
					// If it's facebook
					} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_app ){
			
						$web = $this->public->domain_app;		
			
					// If it's facebook
					} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_narrowcast ){
			
						$web = $this->public->domain_narrowcast;	
	
					// if
					} else {
					
						$web = $this->public->domain_web;	
					
					// if
					}
				
					// Check if it's mobile
					mobile_device_detect(true,true,true,true,true,true,true,$this->private->http . $this->public->domain_mobile . $page,$this->private->http . $web . $page );
					
				// if
				}
		
			// if
			}
	
		// if
		}
	
		// Set the mode
		if( $_SERVER["HTTP_HOST"] == $this->public->domain_web ){
		
			$_SESSION["bento"]["scms"]["mode"] = "web";
		
		// If it's mobile
		} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_mobile ){
		
			$_SESSION["bento"]["scms"]["mode"] = "mobile";
			
		// If it's facebook
		} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_facebook ){

			$_SESSION["bento"]["scms"]["mode"] = "facebook";		

		// If it's an app
		} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_app ){

			$_SESSION["bento"]["scms"]["mode"] = "app";		
			
			// Check what type of output we're looking for
			if( $this->public->app->mode == "data" ){
			
				// Set that the data
				$bento->private->output = "data";
			
			// if
			}

		// If it's a kiosk
		} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_kiosk ){

			$_SESSION["bento"]["scms"]["mode"] = "kiosk";		

		// If it's narrowcast
		} else if( $_SERVER["HTTP_HOST"] == $this->public->domain_narrowcast ){

			$_SESSION["bento"]["scms"]["mode"] = "narrowcast";		

		// We need to redirect it to the proper web domain to avoid duplicate content
		} else {
		
			// Redirect
			$this->redirect(301, $this->http() . $this->public->domain_web . $_SERVER['REQUEST_URI']);
		
		// if
		}
		
		// Set the right mode
		$this->public->mode = $_SESSION["bento"]["scms"]["mode"];
		
		// Set if we're logged in
		$this->public->logged_in = array(
										"scms"	=>	(bool)$this->logged_in(),
										"facebook"	=>	(bool)$this->logged_in("facebook"),
										"twitter"	=>	(bool)$this->logged_in("twitter")
										);
										
		// Check if we have any notifications
		if( $this->logged_in() ){
		
			// Check it out
			$db->select("notification.account_id=" . $this->account_id() . " and notification.state=1");
		
		// if
		}
		
		// Set this up
		$this->public->notification = $db->recordset("notification");
		
		// Clear up notifications
		$db->clear("notification");
		
		// This is for javascript specific
		$this->public->template = array();
		$this->public->page = array();
		
		// Set up the feeds
		$this->public->feed = (object)array();
		$this->public->feed->html = array();
		$this->public->feed->data = array();
		$this->public->feed->combined = array();

		// Get the content types
		$db->select("content.id>0 order by content.name asc");
		
		// Loop through the content types for searches
		foreach( $db->recordset("content") as $content ){
		
			// Make sure we have someting to search
			if( $db->table_exists( $content["table"] ) ){
			
				// Add the content to the fields
				$this->private->content[ $content["table"] ] = explode(",",$content["fields"]);
			
			// if
			}
		
		// foreach
		}
		
		// CLear it up
		$db->clear("content");

		// Get the pages, and set
		$db->select(
					array(
						"table"	=>	"page.id>0 order by page.home_" . $_SESSION["bento"]["scms"]["mode"] . " desc, page.priority asc, page.name asc",
						"join"	=>	"page_permission_x,permission"
						)
					);
			
		// Set it up
		foreach( $db->recordset("page") as $page ){
		
			// filter it in
			$db->filter("page.id=" . $page["id"]);
		
			// Set up the page
			$this->private->page[ $page["slug"] ]["id"] = $page["id"];
			$this->private->page[ $page["slug"] ]["parent_id"] = $page["parent_id"];
			$this->private->page[ $page["slug"] ]["priority"] = $page["priority"];
			$this->private->page[ $page["slug"] ]["plugin"] = $page["plugin"];
			$this->private->page[ $page["slug"] ]["theme"] = $page["theme"];
			$this->private->page[ $page["slug"] ]["name"] = $page["name"];
			$this->private->page[ $page["slug"] ]["anchor"] = $page["anchor"];
			$this->private->page[ $page["slug"] ]["title"] = $page["title"];
			$this->private->page[ $page["slug"] ]["description"] =  $page["description"];
			$this->private->page[ $page["slug"] ]["template"] =  $page["template_" . $_SESSION["bento"]["scms"]["mode"] ];
			$this->private->page[ $page["slug"] ]["home"] = (bool)$page["home_" . $_SESSION["bento"]["scms"]["mode"]];
			$this->private->page[ $page["slug"] ]["error"] = (bool)$page["error"];
			$this->private->page[ $page["slug"] ]["feed"] = (bool)$page["feed"];
			$this->private->page[ $page["slug"] ]["secure"] = (bool)$page["secure"];
			$this->private->page[ $page["slug"] ]["forward"] = $page["forward"];
			$this->private->page[ $page["slug"] ]["hidden"] = (bool)$page["hidden"];
			$this->private->page[ $page["slug"] ]["check"] = "/" . $page["url"] . "/";
						
			// Make sure things are setup
			if( !(bool)$this->private->setup["home"] ){ if( (bool)$page["home_" . $_SESSION["bento"]["scms"]["mode"] ] ){ $this->private->setup["home"] = "/"; } }
			if( !(bool)$this->private->setup["error"] ){ if( (bool)$page["error"] ){ $this->private->setup["error"] = (bool)$page["error"]; } }			
			if( !(bool)$this->private->setup["admin"] ){ if( (bool)$page["admin"] ){ $this->private->setup["admin"] = (bool)$page["admin"]; } }		
			if( !(bool)$this->private->setup["help"] ){ if( (bool)$page["help"] ){ $this->private->setup["help"] = $page["url"]; } }	
		
			// Let's get the permissions
			$this->private->page[ $page["slug"] ]["permission"] = $db->recordset("permission.id");
			if( !is_array($this->private->page[ $page["slug"] ]["permission"]) ){ $this->private->page[ $page["slug"] ]["permission"] = array(); }
		
			// Check what kind of page it it
			if( $page["modal"] && ($this->public->mode != "mobile" && $this->public->mode != "app" && $this->public->mode != "kiosk") ){
				
				// We're going to create and object
				$open_vars = array(
								"url"	=>	strtolower(str_replace(" ","-",$page["url"])),
								"title"	=>	"!--scms:modal:title--",
								"variables"	=>	"!--scms:link:vars--",
								"width"	=>	(int)$page["modal_width"],
								"height"	=>	 (int)$page["modal_height"]
								);
			
				$this->private->page[ $page["slug"] ]["modal"] = true;
				$this->private->page[ $page["slug"] ]["url"] = "javascript:bento.scms.modal.open('" . json_encode( $open_vars ) . "');";
							
			// Else 
			} else if( $this->is_mode("app") ){
			
				$this->private->page[ $page["slug"] ]["modal"] = false;
				$this->private->page[ $page["slug"] ]["url"] = "javascript:bento.scms.app.open('index.html?url=/" . $page["url"] . "/&!--scms:link:vars--" . "')";
				
			// if
			} else {
			
				$this->private->page[ $page["slug"] ]["modal"] = false;
				$this->private->page[ $page["slug"] ]["url"] = "/" . $page["url"] . "/?!--scms:link:vars--";
			
			// if
			}
			
			// Construt the link and the button
			$this->private->page[ $page["slug"] ]["link"] =  '<a href="<!--scms:link:url-->" <!--scms:link:id--> class="<!--scms:link:class-->"><!--scms:link:anchor--></a>';
			$this->private->page[ $page["slug"] ]["button"] =  '<input href="<!--scms:link:url-->" type="<!--scms:link:type-->" class="<!--scms:link:class-->" value="<!--scms:link:anchor-->">';
		
		// foreach
		}
				
		// Clear out the filter
		$db->unfilter();

		// Clear things up 		
		$db->clear("page,page_permission_x,permission");
		
		// Let's get the mail
		$db->select("mail");
			
		// Set it up
		foreach( $db->recordset("mail") as $mail ){ 
		
			// filter it in
			$db->filter("mail.id=" . $mail["id"]);
		
			// Set up the page
			$this->private->mail[ $mail["slug"] ]["id"] = $mail["id"];
			$this->private->mail[ $mail["slug"] ]["template"] = $mail["template"];
			$this->private->mail[ $mail["slug"] ]["subject"] = $mail["subject"];
			$this->private->mail[ $mail["slug"] ]["from_name"] = $mail["from_name"];
			$this->private->mail[ $mail["slug"] ]["from_email"] = $mail["from_email"];
	
		// foreach
		}
		
		// Clear things up 		
		$db->clear("mail");
		
		// If it's a feed, we have all the information we need
		if( $form->submitting() ){ 
		
			// Set the slugs
			$this->private->on_slug = $form->post("scms_feed_slug");
			
			// Set the theme
			$this->private->theme = $form->post("scms_feed_theme");
			
			// Get outta here before it get's serious
			return;
			
		// if
		}

		// Don't
		$secure = false;
	
		// Loop through the URLS
		foreach( $this->private->page as $name => $var ){
		
			// Skip the home base
			if( strlen(str_replace("?" . $_SERVER['QUERY_STRING'],"",$_SERVER['REQUEST_URI'])) == 1 && $var["home"] ){
			
				$this->private->on_slug = $name;
				$this->private->on_page = $var["name"];
				$this->private->theme = $var["theme"];
							
			} else {
					
				// This will set what page we're on to load all the functions we need to load before the template and page
				if( stristr($_SERVER['REQUEST_URI'], trim($var["check"]) ) ){ 
					
					$this->private->on_slug = $name;
					$this->private->on_page = $var["name"];
					$this->private->theme = $var["theme"];
					$secure = $var["secure"];
					
				// if
				}

			// if
			}
			
		// foreach
		}	
		
		// Let's make sure we're setup correctly
		if( !(bool)$this->private->setup["home"] || !(bool)$this->private->setup["error"] || !(bool)$this->private->setup["admin"] ){
		
			// Tell the world there's a problem with the setup
			$this->error("setup");
		
		// if
		}
				
		// Check if secure (ssl) or not
		if( $secure && $this->http("http") ){
		
			$this->redirect(301,"https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
		
		// if
		} else if( !$secure && $this->http("https") ){
		
			$this->redirect(301,"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);

		// if
		}
		
		// Make sure we're  on the right page
		if( !isset($this->private->on_slug) ){
			
			// Redirect to the error page
			$this->error(404);
			
		// if
		}
		
		// Check to make sure we have ample permissions to be here
		if( isset($this->private->page[ $this->on_slug() ]) && is_array($this->private->page[ $this->on_slug() ]["permission"]) && count($this->private->page[ $this->on_slug() ]["permission"])  ){
			
			// Update the permissions for this account if they're logged in
			$this->update_permissions();
			
			// Check the permissions on the slug
			if( !$this->check_permissions() ){
			
				// Not authorized error
				$this->error(403);	
		
			// if
			}
		
		// if
		}
		
		// Clear up old feeds for this page
		$this->feed_unset( $this->is_plugin(), $this->is_slug() );

		// See some new variables
		$this->public->modal = array("page" => array());

		// Check if we're on the modal
		if( !$this->is_modal() && !$this->is_mode("mobile") && !$this->is_mode("app") && !$this->is_mode("kiosk") ){

			// Tell the program we'll need an additional js file
			$bento->add_js(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"modal_closed"
							)
						);		

			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"web"
							)
						);
						
			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"modal_closed"
							)
						);
		
		} else if( $this->is_modal() ){
			
			// Change where the loading image is
			$form->public->load_image = "white";
			
			// Tell the program we'll need an additional js file
			$bento->add_js(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"modal_opened"
							)
						);
						
			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"modal_opened"
							)
						);

			// More vars
			$this->public->modal["page"] = $this->is_page();
		
		// if
		} else if( $this->is_mode("mobile") ){
		
			// Tell the program we'll need an additional js file
			$bento->add_js(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"mobile"
							)
						);
						
			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"place"	=>	1,
								"plugin"	=>	"scms",
								"name"	=>	"mobile"
							)
						);
		
		// if
		} else if( $this->is_mode("app") ){

			// Change where the loading image is
			if( $form->public->load_image ){
				
				$form->public->load_image = "white";
				
			// if
			}
	
			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"app"
							)
						);	
		
			// Tell the program we'll need an additional js file
			$bento->add_js(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"app"

							)
						);				
		
		// if
		} else if( $this->is_mode("narrowcast") ){
		
			// Tell the program we'll need an additional js file
			$bento->add_js(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"narrowcast"
							)
						);		
						
			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"narrowcast"
							)
						);

		// Facebook css
		} else if( $this->is_mode("facebook") ){
		
			// Tell the program we'll need an additional js file
			$bento->add_css(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"facebook"
							)
						);
		
		// Check it
		} else {
		
			$this->public->page = $this->is_page();

		// if		
		}
		
		// Check if we're logged in, show notifications
		if( $this->logged_in() ){

			// Add some stylings
			$bento->add_style(
							".scms",
							array(
								"display"	=>	"block",
								"visibility"	=>	"visible",
								"opacity"	=>	1
							)
						);
			
			// Add some stylings
			$bento->add_style(
							"#scms_notification",
							array(
								"display"	=>	"block",
								"visibility"	=>	"visible",
								"opacity"	=>	1
							)
						);
			
		// if
		}
		
		// Check if we're logged in, show facebook
		if( $this->logged_in("facebook") ){
			
			// Add some stylings
			$bento->add_style(
							".facebook",
							array(
								"display"	=>	"block",
								"visibility"	=>	"visible",
								"opacity"	=>	1
							)
						);
			
		// if
		}
		
		// Check if we're logged in, show facebook
		if( $this->logged_in("twitter") ){
			
			// Add some stylings
			$bento->add_style(
							".twitter",
							array(
								"display"	=>	"block",
								"visibility"	=>	"visible",
								"opacity"	=>	1
							)
						);
			
		// if
		}
		
		// Check if we have a feed
		if( $this->has_feed() ){
			
			// Add the feed variable so we don't get any errors
			$this->public->feed->data = array();
			$this->public->feed->combined = array();

			// Tell the program we'll need an additional js file
			$bento->add_js(
							array(
								"plugin"	=>	"scms",
								"name"	=>	"feed"
							)
						);
		
		// if
		}

		// Load the javascript and css for the page and template if they exist
		foreach( array($bento->public->assets->js,$bento->public->assets->css ) as $library ){
 				
			// Now, let's check for js/css files related to the templates
			if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->private->theme . "/" . $library . "/" . $this->is_mode() . "/" . $bento->private->auto . "/" . $this->is_template() . "." . $library ) ){
			
				// Assign the js to the manifest
				$bento->add(
							array(
								"type"	=>	$library,
							 	"file"	=>	$_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->private->theme . "/" . $library . "/" . $this->is_mode() . "/" . $bento->private->auto . "/" . $this->is_template() . "." . $library
								)
							);

			// if
			}
					
			// Now, let's check for js/css files related to the pages
			if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->private->theme . "/" . $library . "/" . $this->is_mode() . "/" . $bento->private->auto . "/" . $this->is_slug() . "." . $library ) ){
			
				// Assign the js to the manifest
				$bento->add(
							array(
								"type"	=>	$library,
							 	"file"	=>	$_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->private->theme . "/" . $library . "/" . $this->is_mode() . "/" . $bento->private->auto . "/" . $this->is_slug() . "." . $library
								)
							);			
			// if
			}
			
		// foreach
		}
		
		// Tell the world we are in modal or not
		$this->public->is_modal = $this->is_modal();
		$this->public->is_mode = $this->is_mode();
		
		// Clear up out filters
		$db->unfilter();
		
		// Set the timezone
		$this->timezone(true);
		
		// This is the url, querystring
		if( !stristr($_SERVER['REQUEST_URI'],"?") ){
			$this->private->parts = explode("/",$_SERVER['REQUEST_URI']);
		// Remove the querystring
		} else {
			$tmp = explode("?",$_SERVER['REQUEST_URI']);
			$this->private->parts = explode("/",$tmp[0]);
		// if
		}
		
		// Check if a new language is set
		if( $this->querystring("language") ){
		
			// Set the language
			$language->set( $this->querystring("language") );
			
		// if
		}
		
		// Looking for a theme control file
		$theme = NULL;
				
		// Check what theme we're on
		if( file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->is_theme() ) ){
		
			// This is the theme file
			$theme = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->private->theme . "/" . $bento->public->assets->php . "/theme.php";
			
			// This is the theme directory
			$this->private->directory->theme = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->is_theme();

		// Otherweise
		} else {

			// This is the asset file
			$theme = $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->themes . "/" . $this->is_plugin() . "/" . $bento->public->assets->php . "/theme.php";
			
			// This is the theme directory
			$this->private->directory->theme = $this->private->directory->plugins. "/" . $this->is_plugin() . "/" . $this->private->directory->themes . "/" . $this->is_theme();
		
		// if
		}
			
		// We have a theme issue
		if( !$this->private->directory->theme ){
			
			$bento->error("The theme selected doesn't exist.");
			
		// if
		}
		
		// Check if there's a custom file for this theme
		if( file_exists( $theme ) ){
		
			// Add the custom class
			$bento->add(
						array(
							"type"	=>	"php",
							"file"	=> $theme
							)
						);
		
			// Add it
			$GLOBALS["scms_theme"] = new scms_theme();
			global $scms_theme;
		
		// if
		}
		
		// Check what page directory we're on
		if( $this->is_admin() ){
			
			$this->private->directory->page = $_SERVER['DOCUMENT_ROOT'] . "/" . $bento->private->directory . "/scms/" . $bento->public->assets->php . "/" . $this->private->directory->admin . "/" . $this->private->directory->pages;

		// Check what page directory we're on
		} else if( $this->is_help() ){
			
			// Check if we're using base pages	
			if( $this->q("plugin") == "scms" ){
	
				// This is the theme directory
				$this->private->directory->page = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->pages;
	
			// Otherweise
			} else {
	
				// This is the theme directory
				$this->private->directory->page = $this->private->directory->plugins . "/" . $this->q("plugin") . "/" . $this->private->directory->pages;
			
			// if
			}
			
		} else {
		
			// Check if we're using base pages	
			if( $this->is_plugin("scms") ){
	
				// This is the theme directory
				$this->private->directory->page = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->pages;
	
			// Otherweise
			} else {
	
				// This is the theme directory
				$this->private->directory->page = $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->pages;
			
			// if
			}
			
		// if
		}
		
		// We have a theme issue
		if( !$this->private->directory->page ){
			
			$bento->error("The page directory selected does not exist.");
			
		// if
		}
		
		// We'll remove any events we may have logged for us
		$this->delete_event();
		
		// Now we'll load the plugins
		$this->plugins("load");
		
		// Load the controller depending on what we're doing
		if( $this->is_slug("admin") ){
		
			// There's the method we want to call
			$this->method = $this->q("page");
					
		// if
		} else {
		
			// This is the tmp
			$this->method = $this->private->on_slug;
		
		}
				
		// Get the method for the page
		$method = $this->method;

		// Return data for the app
		$t = array();
		$p = array();

		// If it doesn't exists, just call the template
		if( class_exists("scms_theme") && method_exists( $scms_theme, $this->method ) ){
			
			// Execute it
			$scms_theme->$method();
		
		// if
		}

		// If it doesn't exists, just call the template
		if( class_exists("scms_page_core") && method_exists( $scms_page_core, $this->method ) ){
			
			// Execute it
			$scms_page_core->$method();
		
		// if
		}
		
		// Check the plugin for the modal
		$support_class = $this->is_plugin() . "_page";
	
		// If it doesn't exists, just call the template
		if( class_exists( $support_class ) && method_exists( $support_class, "__load" ) ){
			
			// Execute it
			$GLOBALS[ $support_class ]->__load();
		
		// if
		}
		
		// If it doesn't exists, just call the template
		if( class_exists( $support_class ) && method_exists( $support_class, $this->method ) ){
			
			// Execute it
			$GLOBALS[ $support_class ]->$method();
		
		// if
		}

		// If it doesn't exists, just call the template
		if( class_exists( $support_class ) && method_exists( $support_class, "__unload" ) ){
			
			// Execute it
			$GLOBALS[ $support_class ]->__unload();
		
		// if
		}
		
		// Load the controller depending on what we're doing
		if( $this->is_slug("admin") ){
		
			// Generate some admin stuff
			$this->admin();
			
		// if
		}
		
		// Output the template
		$this->template();
		
		// Return this up
		return true;
	
	// method
	}

	/*
	@method: __shortcode($shortcode,$html)
	@description: Handles any shortcode created by thie plugin
	@params:
	@shortcode:  
	@return:
	*/
	public function __shortcode($shortcode,$html){
	
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		$scms = $this;
		
		// Let's look for some buttons		
		preg_match_all("@" . $shortcode . "@",$html,$tmp);

		// Check which one it if
		if( stristr($shortcode,"scms:switch:") ){

			// Loop through it
			foreach( $tmp[1] as $lan ){
			
				$text = $this->link( array("slug"	=>	"language","variables"	=>	$lan ), false );
				$html = str_replace("<!--scms:switch:" . $lan . "-->",$text,$html);
			
			// foreach
			}
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:button:") ){

			// Loop through it
			foreach( $tmp[1] as $button ){
			
				$text = $this->button( array("slug"	=>	$button ),false );
				$html = str_replace("<!--scms:button:" . $button . "-->",$text,$html);
			
			// foreach
			}
			
			//die();
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:link:") ){

			// Loop through it
			foreach( $tmp[1] as $link ){
			
				$text = $this->link( array("slug"	=>	$link ),false );
				$html = str_replace("<!--scms:link:" . $link . "-->",$text,$html);
			
			// foreach
			}
		
		// if	
		}

		// Check which one it if
		if( stristr($shortcode,"scms:admin:") ){

			// Loop through it
			foreach( $tmp[1] as $admin ){
			
				$text = $this->link( array("slug"	=>	$admin, "variables"	=>	array("page"	=>	$tmp[1]) ),false );
				$html = str_replace("<!--scms:link:" . $admin . "-->",$text,$html);
			
			// foreach
			}
			
			//die();
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:q:") ){

			// Loop through it
			foreach( $tmp[1] as $q ){
			
				$text = $this->q( $q );
				$html = str_replace("<!--scms:q:" . $q . "-->",$text,$html);
			
			// foreach
			}
			
			//die();
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:querystring:") ){

			// Loop through it
			foreach( $tmp[1] as $q ){
			
				$text = $this->q( $q );
				$html = str_replace("<!--scms:querystring:" . $q . "-->",$text,$html);
			
			// foreach
			}
			
			//die();
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:url:") ){

			// Loop through it
			foreach( $tmp[1] as $url ){
				
				// Start the output buffer
				ob_start();
			
				// The button
				echo $this->url( array("slug"	=>	$url ) );
			
				// Take the contents from the php files		
				$text = ob_get_contents();
				ob_end_clean();
	
				$html = str_replace("<!--scms:url:" . $url . "-->",$text,$html);
			
			// foreach
			}
			
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:include:") ){
		
			// Loop through it
			foreach( $tmp[1] as $f ){
				
				// Include the file
				$include = $this->inc( $f );
			
				// Replace the shortcode with the executed include
				$html = str_replace("<!--scms:include:" . $f . "-->",$include,$html);
			
			// foreach
			}
			
		// if
		}
		
		// Check which one it if
		if( $form->post("scms_feed_variables") == "" && stristr($shortcode,"scms:feed:") ){
			
			// Loop through it
			foreach( $tmp[1] as $f ){
				
				// Check if we have a wrapper or not
				if( stristr($f,",") ){
					
					// Break it into 2
					$k = explode(",",$f);
					
					// Set the feed
					$g = $k[0];
					
					// Set the wrapper
					$wrapper = $k[1];
					
				// Check it
				} else {
					
					// Set the wrapper
					$wrapper = "span";
					$g = $f;
					
				// if
				}
			
				// Start the output buffer
				ob_start();
			
				// Get the feed
				$this->feed( $g, (object)array("wrap"	=>	true,"wrapper"	=>	$wrapper) );
			
				// Take the contents from the php files		
				$feed = ob_get_contents();
				
				// Clean the output buffer
				ob_end_clean();
				
				$html = str_replace("<!--scms:feed:" . $f . "-->",$feed,$html);
			
			// foreach
			}
			
			//die();
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:page") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			$this->page();
		
			// Take the contents from the php files		
			$include = ob_get_contents();
			ob_end_clean();
			
			
			$html = str_replace("<!--scms:page-->",$include,$html);
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"account:name") && isset($tmp[0]) ){
				
			// Start the output buffer
			ob_start();
		
			// The button
			$this->account_name(true);
		
			// Take the contents from the php files		
			$text = ob_get_contents();
			ob_end_clean();

			$html = str_replace("<!--scms:account:name-->",$text,$html);

		// if			
		}
		
		// Check which one it if
		if( stristr($shortcode,"profile_image") && isset($tmp[0]) ){
				
			// Start the output buffer
			ob_start();
		
			// The button
			echo $this->profile_image();
		
			// Take the contents from the php files		
			$image = ob_get_contents();
			ob_end_clean();

			$html = str_replace("<!--scms:profile_image-->",$image,$html);

		// if			
		}

		// Check which one it if
		if( stristr($shortcode,"scms:meta:title") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			$this->meta("title");
		
			// Take the contents from the php files		
			$meta = ob_get_contents();
			ob_end_clean();
			
			$html = str_replace("<!--scms:meta:title-->",$meta,$html);
		
		// if	
		}

		// Check which one it if
		if( stristr($shortcode,"scms:meta:description") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			$this->meta("description");
		
			// Take the contents from the php files		
			$meta = ob_get_contents();
			ob_end_clean();
			
			
			$html = str_replace("<!--scms:meta:description-->",$meta,$html);
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:language") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			echo $language->selected("code");
		
			// Take the contents from the php files		
			$code = ob_get_contents();
			ob_end_clean();
			
			// Replace it
			$html = str_replace("<!--scms:language-->",$code,$html);
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:breadcrumb") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			$this->breadcrumb();
		
			// Take the contents from the php files		
			$breadcrumb = ob_get_contents();
			ob_end_clean();
			
			// Replace it
			$html = str_replace("<!--scms:breadcrumb-->",$breadcrumb,$html);
		
		// if	
		}
		
		// Check which one it if
		if( stristr($shortcode,"scms:sitemap") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			$this->sitemap();
		
			// Take the contents from the php files		
			$sitemap = ob_get_contents();
			ob_end_clean();
			
			// Replace it
			$html = str_replace("<!--scms:sitemap-->",$sitemap,$html);
		
		// if	
		}
		
		// Return the html
		return $html;
	
	// method
	}

	/*
	@method: plugins()
	@description: This will load plugins
	@params:
	@shortcode:  
	@return:
	*/
	public function plugins( $type="setup" ){
	
		global $bento;
		
		// Check what we're doing
		if( $type == "setup"){

			// These are the assets
			$assets = array();
			
			// Find what kind of core assets we have to play with
			if ($handle = opendir( $this->private->directory->plugins ) ){
		
				// Loop through the files assets
				while (false !== ($asset = readdir($handle))){
					
					// Make sure it's not a directory
					if( $asset != "." && $asset != ".." && in_array($asset,$this->private->plugins) ){
						
						echo $asset;
					
						// This is the directory
						$directory = $this->private->directory->plugins . "/" . $asset;
	
						// Check the file
						if( file_exists( $directory . "/" . $bento->public->assets->php . "/" . $bento->private->auto . "/" . $asset . ".php" ) ){
							
							// Get the file name
							$assets[ $asset ]["file"] = $asset . ".php";
							$assets[ $asset ]["path"] = $directory . "/" . $bento->public->assets->php . "/" . $bento->private->auto . "/" . $asset . ".php";
												
						// and index file
						} else if( file_exists( $directory . $bento->public->assets->php . "/" . $bento->private->auto . "/" . "index.php" ) ){
							
							// Get the file name
							$assets[ $asset ]["file"] = "index.php";
							$assets[ $asset ]["path"] = $directory . "/" . $bento->public->assets->php . "/" . $bento->private->auto . "/" . "index.php";
												
						// if						
						} 		
						
						// Assign the rest
						if( isset($assets[ $asset ]["file"]) ){
						
							$assets[ $asset ]["config"] = $directory . "/" . $bento->private->assets->config;	
							$assets[ $asset ]["install"] = $directory . "/" . $bento->private->assets->install . "/" . $bento->private->assets->install;
							$assets[ $asset ]["scms"] = $directory . "/" . $bento->public->assets->php . "/" . $bento->private->auto;	
	
			
						// if
						}
			
					// if
					}
					
				// while
				}
				
			// if
			}
			
			// Loop through it all
			foreach( $assets as $asset => $f ){
			
				// Include the library file
				$bento->add_php(
								array(
									"file"	=> $f["path"]
									)
								);
	
				// Add this
				$GLOBALS[$asset] = new $asset($this);
				$GLOBALS[$asset]->file = $f["path"];
				$GLOBALS[$asset]->config = $f["config"] . "/" . $asset . ".php";
				
			// foreach
			}
			
			// Make sure it's installed
			foreach( $assets as $asset => $f ){
			
				// Loop through the files
				if( file_exists( $f["config"] ) && $handle = opendir( $f["config"] ) ) {
	
					// Loop through the files
					while (false !== ($f2 = readdir($handle))){
					
						// Make sure it's not a directory
						if( $f2 != "." && $f2 != ".." && stristr($f2,".php") ){
												
							// this will include this functio
							$variables = $bento->configuration( $f["config"] . "/" . $f2 );
							
							// If public and private variables aren't declared in the class
							if( !isset($GLOBALS[$asset]->private) ){ $GLOBALS[$asset]->private = (object)array(); }
							if( !isset($GLOBALS[$asset]->public) ){ $GLOBALS[$asset]->public = (object)array(); }
	
							$GLOBALS[$asset]->version = $variables->version;
							$GLOBALS[$asset]->state = $variables->state;
							$GLOBALS[$asset]->private = ( isset($variables->private) ) ? (object)array_merge((array)$variables->private, (array)$GLOBALS[$asset]->private) : (object)array();
							$GLOBALS[$asset]->public = ( isset($variables->public) ) ? (object)array_merge((array)$variables->public, (array)$GLOBALS[$asset]->public) : (object)array();
	
							// Clear this up
							unset($variables);
							
						// if
						}
	
					// while
					}
					
				// if
				}
			
			// foreach
			}
			
			// Assign variables in the class if need be
			foreach( $assets as $asset => $f ){
			
				// Check if there's a load
				if( method_exists($GLOBALS[$asset],"__configure") ){
							
					// Check if we're able to configure it
					if( !$GLOBALS[$asset]->__configure($this) && $this->state == "installed"  ){
					
						// Tell the program we need to do an install before the rest
						$this->state = "installing";
						
						// Here you gp
						$this->public->js->variable[ $asset ] = (object)array("install" => (object)array());
		
						// Load the javascript and css for bento
						foreach( array($this->public->assets->js,$this->public->assets->css ) as $type ){
				
							// This will add files from a directory
							$this->add_files(
											array(
												"type"	=>	$type,
												"directory"	=>	$f["install"] . "/" . $type . "/" . $this->private->auto . "/"	
											)
										);
							
						// foreach
						}
									
						// Check if there's a load
						if( method_exists($GLOBALS[$asset],"__install") ){
									
							// Add this
							if( !$GLOBALS[$asset]->__install($this) ){
														
								break;
							
							// if
							}							
							
						// Check if there's an install script
						} else if( file_exists( $f["install"] . "/" . $this->public->assets->php . "/" . $this->private->auto . "/" . $f["file"] ) ){
	
							// Start the output buffer so we can get the install file into the template					
							ob_start();
							
							// Include the library file
							$bento->add_php(
											array(
												"file"	=> $f["install"] . "/" . $this->public->assets->php . "/" . $this->private->auto . "/" . $f["file"]
												)
											);
							
							// Output the installer
							$bento->template(  ob_get_clean() );
							
							break;
		
						// if
						} else {
						
							// Else
							$bento->error( "Could not configure the " . $asset . " plug-in. This means the plugin failed to configure at start up and there is not and install script or an __install method in the class." );
							
							break;
						
						// if
						}
					
					// if
					}
	
				// if
				}
			
			// foreach
			}
			
			// Check if there are scms (page, handler, url, feed) files
			foreach( $assets as $asset => $f ){
				
				// Loop through the support files
				foreach( array("page","handler","url","feed") as $support ){
					
					// Check if the file exists
					if( file_exists( $assets[ $asset ]["scms"] . "/" . $support . ".php" ) ){
						
						// Add the file
						$bento->add_php(
									array(
										"file"	=> $assets[ $asset ]["scms"] . "/" . $support . ".php"		
									)
								);
						
						// Merge this class with the parent	
						$support_class = $asset . "_" . $support;
		
						// Add this into the fray
						$GLOBALS[ $support_class ] = new $support_class();	
						
					// if
					}
	
				// foreach
				}
			
			// foreach
			}
				
			// Assign variables in the class if need be
			foreach( $assets as $asset => $f ){
			
				// Check if there's a load
				if( method_exists($GLOBALS[$asset],"__assign") ){
							
					// Add this
					$GLOBALS[$asset]->__assign($this);
	
				// if
				}
			
			// foreach
			}

		// Now we can load the plugins up
		} else if( $type == "load" ) {

			// Load the class if possible
			foreach( $this->private->plugins as $plugin ){
			
				// Check if there's a load
				if( method_exists($GLOBALS[ $plugin ],"__load") ){
							
					// Add this
					$GLOBALS[ $plugin ]->__load($this);
	
				// if
				}
			
			// foreach
			}

			// Load the assets
			foreach( $this->private->plugins as $plugin ){
	
				// Load the javascript and css
				foreach( array($bento->public->assets->js,$bento->public->assets->css ) as $type ){
	
					// Get the 2 places it could be
					$auto = array(
								$this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $type . "/" . $bento->private->auto,
								$this->private->directory->theme . "/" . $type . "/" . $bento->private->auto
							);
					
					// Loop through the auto directories
					foreach( $auto as $dir ){
						
							// Get the auto files, plugin, mode, template
							$hs = array(
										$this->is_plugin(),
										$this->is_mode(),
										$this->is_template()
										);
						
							// Loop through them 
							foreach( $hs as  $h ){
							
									// This is the file we're looking for
									$add = $dir . "/" . $h . "." . $type;
									
									// Check if it exists
									if( file_exists( $add ) ){
										
										// Add the file
										$bento->add_file( 
														array( 
															"type"	=>	$type,
															"file"	=>	$add
														)
												);
										
									// if
									}
								
							// foreach
							}
								
					// for each directory
					}
					
				// for each
				}
				
				// Check it out
				if( isset($GLOBALS[$plugin]->public) ){
	
					// We may be installing
					if( !isset( $bento->public->js->variable[ $plugin ] ) ){
					
						// Assign js variables
						$bento->public->js->variable[ $plugin ] = $GLOBALS[$plugin]->public;
				
				
					// if
					}
					
				// if
				}
				
			// foreach
			}
	
		// if
		}

	// method
	}

	/*
	@method: error( $type )
	@description: If there is an error it will rRedirect to an error page
	@params:
	@shortcode:  
	@return:
	*/
	public function error( $type ){

		// redirect the site
		if( $type != "setup" ){
		
			// Get the url
			$url = parse_url($_SERVER['REQUEST_URI']);
		
			// Check if it's an authentication errror
			if( $type == 403 ){
					
				// Check where we're going
				if( $this->is_slug() != $this->private->page[ $this->is_slug() ]["forward"] ){

					// Redirect to the error page
					$this->redirect($type,$this->is_forward(),array("error"	=>	$type,"bento_scms_modal"	=> (bool)$this->q("bento_scms_modal"),"url"	=>	$url["path"] ));
	
				// Otherwise to the error page batman
				} else {

					// Redirect to the error page
					$this->redirect($type,"/error/",array("error"	=>	$type,"bento_scms_modal"	=> (bool)$this->q("bento_scms_modal"),"url"	=>	$url["path"] ));
				
				// if
				}
			
			// Otherwise to the error page
			} else {
		
				// Redirect to the error page
				$this->redirect($type,"/error/",array("error"	=>	$type,"bento_scms_modal"	=> (bool)$this->q("bento_scms_modal"),"url"	=>	$url["path"] ));

			// if
			}

		// Here you go
		} else {
		
			global $bento;
		
			// Fatal error
			$bento->error("Please be sure a home page, an error page, and are selected and both files exist on the filesystem.");
		
		// if
		}
		
		// Kill this off
		die();

	// method
	}

	/*
	@method: redirect( $type,$url,$vars="" )
	@description: Handles redirects
	@params:
	@shortcode:  
	@return:
	*/
	public function redirect( $type,$url,$vars="" ){
	
		// Return it if there's problem
		if( !$url ){ die(); return; }
	
		// Check it there are vars
		if( $vars != "" ){ 
			if( !is_array($vars) ){ $vars = array("id"	=>	$vars); }
			$vars = "?" . json_encode($vars);
		}

		// redirect the site
		header('HTTP/1.1 " . $type . " Moved Permanently');
		header('Location: ' .  $url . $vars );

	// method
	}

	/*
	@method: remember()
	@description: This will remember where we were (if we need to return)
	@params:
	@shortcode:  
	@return:
	*/
	public function remember(){

		// Get them back to the last safe place!
		if( (!$this->is_error()) && (!$this->is_modal()) && (!$this->is_hidden()) ){

			// set it up	
			$_SESSION["bento"]["scms"]["remember"] = $_SERVER['REQUEST_URI']; 

		// if
		}

	// method
	}

	/*
	@method: remembered( $echo=false )
	@description: This will remember where we were (if we need to return)
	@params:
	@shortcode:  
	@return:
	*/
	public function remembered( $echo=false ){
	
		// Check it out
		if( !isset( $_SESSION["bento"]["scms"]["remember"] ) ){ return "/"; }

		// Return it
		return $echo ? print $_SESSION["bento"]["scms"]["remember"] : $_SESSION["bento"]["scms"]["remember"];

	// method
	}

	/*
	@method: template()
	@description: This will load the template (based on the top)
	@params:
	@shortcode:  
	@return:
	*/
	public function template(){
	
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		$scms = $this;

		// Create a breadcrum trail
		$this->trail();
				
		// Let's check if we're on an error page
		if( $this->is_error() && $this->is_modal() ){

			// The default for the error page
			$this->private->page[ $this->is_slug() ]["template"] = "modal-small";
		
		// if
		}
		
		// If this is the admin page, we have a different file
		if( $this->is_slug("admin") ){
		
			$template = $_SERVER['DOCUMENT_ROOT'] . "/" . $bento->private->directory . "/scms/php/admin/" . $this->private->directory->templates . "/" . $this->is_template() . ".php";
		
		// if
		} else {
	
			$template = $this->private->directory->theme . "/" . $this->private->directory->templates . "/" . $this->is_mode() . "/" . $this->is_template() . ".php";
		
		// if
		}
		
		// Check if the file exists
		if( file_exists($template) ){
		
			// Start the output buffer
			ob_start();
	
			// this will include this functio
			include $template;
			
			// Take the contents from the php files		
			$html = ob_get_contents();
			ob_end_clean();
			
			// Check if this is data for an app, add it as a data variables
			if( $this->is_mode("app") && $this->public->app->mode == "data" ){

				$this->public->app->template = $html;
			
			// Just output it
			} else {
							
				// Just do it
				$this->html = $html;

			// if
			}

		// Otherwise
		} else {
		
			$bento->error("The theme or template file selected don't exist.<pre>" . $template . "</pre>");	
			
		// if
		}
		
	// method
	}
	
	/*
	@method: page()
	@description: Loads the page content
	@params:
			$reload (boolean): Checks if we're reloading the page via the feeds section (dynamic content)
	@shortcode: <!--scms:page-->
	@return:
	*/
	public function page( $reload=false ){
	
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		global $scms_url;
		$scms = $this;
		
		// Check if this is the admin or not
		if( $this->is_slug("admin")){
		
			// This is the admin file, if there is one
			$mode = "admin";
			$f = $this->q("page") . ".php";

		// Check if this is a help page or not		
		} else if( $this->is_slug("help") ){
			
			// Check if this is a plugin or not
			$mode = "help";
			$f = $this->q("slug") . ".php";
		
		// This is just a regular page
		} else {
		
			// Set the file and the mode
			$mode = $this->is_mode();
			$f = $this->is_slug() . ".php";
		
		// if
		}
			
		// Check if maybe the translation hasn't been completed
		if( ($this->public->language != $language->selected()) && !file_exists( $f ) ){
		
			// First we'll try and copy the contents
			if( $file->writable( $this->private->directory->page . "/" . $mode. "/" ) ){
		
				// Then we'll check if there's a translation directory
				if( !file_exists( $this->private->directory->page . "/" . $mode. "/" . strtolower($this->is_language()) . "/" ) ){
				
					// If not we'll create one
					$file->create_directory( $this->private->directory->page . "/" . $mode. "/" . strtolower($this->is_language()) . "/" );
				
				// if
				}
				
				// Make sure out translation directory is written
				if( $file->writable( $this->private->directory->page . "/" . $mode. "/" . strtolower($this->is_language()) . "/" ) ){
		
					ob_start();
					
					include $this->private->directory->page . "/" . $mode. "/" . strtolower($this->public->language) . "/" . $f;
							
					// Take the contents from the php files		
					$html = ob_get_contents();
					ob_end_clean();	
					
					// Get the default language
					$db->select("language.name=" . $this->public->language );
					$code = $db->record("language.code");
					$db->clear("language");
		
					// Translate the page
					$translated = $language->html(
												array(
													"from"	=>	$code,
													"to"	=>	$language->selected("code"),
													"html"	=>	$html	
												)
											);
					
					// If we were able to do the translation
					if( $translated ){
					
						$file->write( $this->private->directory->page . "/" . $mode . "/" . strtolower($this->is_language()) . "/" . $f, $translated );
		
					// We couldn't translate it
					} else {
		
						// Copy the contents
						copy( $this->private->directory->page . "/" . $mode . "/" . $this->public->language . "/" . $f, $this->private->directory->page . "/" . strtolower($this->is_language()) . "/" . $f );
		
					// if
					}
		
				// if not we'll use the default language to avoid an error
				} else {
				
					$f = $this->private->directory->page . "/" . $this->is_mode(). "/" . $this->public->language . "/" . $f;
				
				// if
				}
				
			// There's a problem with writing the contents, so we'll just use english to avoid an error
			} else {
			
				$f = $this->private->directory->page . "/" . $mode . "/" . $this->public->language . "/" . $f;
					
			// if
			}
		
		// it's in the regular language, it's not in the regular mode
		} else if ( !file_exists( $this->private->directory->page . "/" . $mode . "/" . $this->public->language . "/" . $f ) && $this->is_mode() != $this->public->default_mode ) {
			
			// Get the file from the default mode
			$f = $this->private->directory->page . "/" . $this->public->default_mode . "/" . strtolower( $this->public->language ) . "/" . $f;
		
		// if
		} else {
			
			if( !$this->is_admin() ){

				// Get the reight file
				$f = $this->private->directory->page . "/" . $mode . "/" . strtolower( $this->public->language ) . "/" . $f;
		
			} else {

				// Get the reight file
				$f = $this->private->directory->page . "/" . strtolower( $this->public->language ) . "/" . $f;				
				
			// if
			}
		
		// if
		}
		
		// Check and make sure the file exists
		if( file_exists( $f ) ){

			// Log an error if we're on the error page
			if( $this->is_error() ){
			
				// Log an error
				$bento->log("error","Error loading page.",array("error"	=>	$this->q("error"), "url"	=>	$this->q("url")));
			
			// if
			}
			
			// Set the theme as a session (this will come in handy for mail)
			$_SESSION["bento"]["scms"]["theme"] = $this->is_theme();
			
			// Add an event to the database
			$this->add_event(
							array(
								"parent_id"	=>	$this->is_slug(),
								"method"	=>	"page"
							)
						);

			// Remember the page
			$this->remember();

			// Start the output buffer
			ob_start();
	
			// this will include this functio
			include $f;
			
			// Take the contents from the php files		
			$html = ob_get_contents();
			ob_end_clean();

			// Check if this is reloaded content or not
			if( $reload ){

				// Translate and return the page
				return $language->translate($html);
			
			} else {

				// return the page				
				echo $html;
							
			// if
			}
		
		// Redirect
		} else {
		
			// If reloading, we have a problem
			if( $reload ){ return; }
		
			// Make sure this isn't the error page
			if( !$scms->is_home() && !$scms->is_error() ){ 
		
				// There's a 401 error
				$this->error(404);
		
			} else {
			
				// Tell them there's a serious problem
				$bento->error("We could not find the page you were looking for and the error page is missing.");
			
			// if
			}
		
		// if
		}
		
	// method
	}
	
	/*
	@method: content()
	@description: Loads the page from a slug (for search)
	@params:
			$variables
				$variables["slug"] (required, string), the slug of the content to load
				$variables["exerpt"] (optional, boolean), shortents the content
	@shortcode: 
	@return:
	*/
	public function content( $variables ){
	
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		global $scms_url;
		$scms = $this;
		
		if( !isset($variables["slug"]) ){ return false; }
		if( !isset($variables["excerpt"]) ){ $variables["excerpt"] = true; }

		// Get the html
		$html = $this->private->page[ $variables["slug"] ]["description"];
			
		// Chek if we're shortening it
		if( $variables["excerpt"] ){
		
			$html = substr($html,0,300);
		
		// if
		}
		
		echo $html;
		
	// method
	}

	/*
	@method: inc()
	@description: includes a page from the includes directories
	@params:
			$f (required, string) file name excluding directory
	@shortcode: 
	@return: executed html or empty string
	*/
	public function inc( $f ){

		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		global $scms_url;
		$scms = $this;

		// Get rid of the php if there is ont
		$f = str_replace(".php","",$f);
		
		//echo $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->includes . "/" . $f . ".php"; die();
		
		// Check if the file exists
		if( !$this->is_plugin("scms") && file_exists($this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->includes . "/" . $f . ".php") ){
			
			$f = $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->includes . "/" . $f . ".php";
			
		// if
		} else if ( ($this->is_plugin("scms") || !file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->includes . "/" . $f . ".php")) || file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->includes . "/" . $f . ".php") ){
		
			$f = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->includes . "/" . $f . ".php";
			
		// if
		} else {
		
			return "";
	
		// if
		}
		
		// Start the output buffer
		ob_start();
						
		// Include the file	
		require_once $f;
	
		// Take the contents from the php files		
		$html = ob_get_contents();
		ob_end_clean();
	
		// Return the text
		return $bento->replace_shortcode("scms",$html);
				
	// method
	}

	/*
	@method: output()
	@description: This will output the html
	@params:
	@shortcode:  
	@return:
	*/
	public function output(){
		
		global $bento;
	
		// Output the html
		echo $this->html;

	// method
	}

	/*
	@method: app()
	@description: Generates the app assets from the first two (mootools) js files
	@params:
	@shortcode:  
	@return:
	*/
	private function app( $type ){
	
		global $bento;
	
		// Get the filecount
		$files = count($bento->files( $type ));
	
		// Set what we're removing
		$bottom = array(
						"js"	=>	2,
						"css"	=>	0
						);
	
		// Remove all the files
		for($i=$bottom[ $type ];$i<$files;$i++){
		
			// Remove the file
			$bento->remove_file(
							array(
								"type"	=>	$type,
								"place"	=>	2
							)
						);
			
		// foreach
		}
		
		// Check which file we need to add
		$bento->add_file(
						array(
							"plugin"	=>	"scms",
							"type"	=>	$type,
							"name"	=>	"device"
						)
					);
	
		// Get the content
		$bento->html = $bento->content(
									array(
										"type"	=>	$type,
										"compress"	=> true
										)
									);

		// Get the url 
		$url = parse_url((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);	
	
		// Add some loading stuffs
		if( $type == "js" ){
		
			// Add the js to the output
			$bento->html = " var device = " . json_encode( 
														array(
																"host"	=> $url["scheme"] . "://" . $url["host"],
																"url" 	=>	$url["path"]
																)
															) . ";" . $bento->html;
			
		// if
		} else {
		
			// Set the host variable
			$bento->html = "@variables host { url: " . $url["scheme"] . "://" . $url["host"] . "; }\r\n" . $bento->html;
			
		// if
		}
							
		// Output it now
		$bento->output( $type );

	// method
	}

	/*
	@method: is_slug( $slug="" )
	@description: Checks to see if we're on a page
	@params:
	@shortcode:  
	@return:
	*/
	public function is_slug( $slug="" ){ return $this->on_slug( $slug ); }
	public function on_slug( $slug="" ){
		
		// Return it if there's an issue
		if( !isset($this->private->on_slug) ){ return false; }
	
		// Check if a boolean check or not
		if( $slug != "" ){
	
			return $this->private->on_slug == $slug;
			
		} else {
			
			return $this->private->on_slug;
		
		// if
		}
		
	// method
	}

	/*
	@method: is_page( $page="" )
	@description: Checks to see if we're on a page
	@params:
	@shortcode:  
	@return:
	*/
	public function is_page( $page="" ){ return $this->on_page( $page ); }
	public function on_page( $page="" ){
	
		// Check if a boolean check or not
		if( $page != "" ){
	
			return $this->private->on_page == $page;
			
		} else {
			
			return $this->private->on_page;
		
		// if
		}
		
	// method
	}

	/*
	@method: is_name( $page="" )
	@description: This will return the name of the page we're on
	@params:
	@shortcode:  
	@return:
	*/
	public function is_name( $page="" ){ return $this->on_name( $page ); }
	public function on_name( $page="" ){

		// Check if a boolean check or not
		if( $page != "" ){
	
			return $this->private->page[ $this->is_slug() ]["name"] == $this->private->page[ $page ]["name"];
			
		} else {
			
			return $this->private->page[ $this->is_slug() ]["name"];
		
		// if
		}
			
	// method
	}

	/*
	@method: is_title( $page="" )
	@description: This will return the title of the page we're on
	@params:
	@shortcode:  
	@return:
	*/
	public function is_title( $slug="" ){ return $this->on_title( $slug ); }
	public function on_title( $slug="" ){

		// Check if a boolean check or not
		if( $slug != "" ){
			
			if( isset($this->private->page[ $slug ]["title"]) ){
	
				return $this->private->page[ $slug ]["title"];
			
			} else {
				
				return "";
				
			}
			
		} else {
			
			return $this->private->page[ $this->is_slug() ]["title"];
		
		// if
		}
			
	// method
	}

	/*
	@method: is_anchor( $page="" )
	@description: This will return the title of the page we're on
	@params:
	@shortcode:  
	@return:
	*/
	public function is_anchor( $slug="" ){ return $this->on_anchor( $slug ); }
	public function on_anchor( $slug="" ){

		// Check if a boolean check or not
		if( $slug != "" ){
	
			return $this->private->page[ $this->is_slug() ]["anchor"] == $this->private->page[ $slug ]["anchor"];
			
		} else {
			
			return $this->private->page[ $this->is_slug() ]["anchor"];
		
		// if
		}
			
	// method
	}

	/*
	@method: get_anchor( $slug="" )
	@description: Get the anchor of the selected
	@params:
	@shortcode:  
	@return:
	*/
	public function get_anchor( $slug ){

		// Check if a boolean check or not
		return $this->private->page[ $slug ]["anchor"];
			
	// method
	}

	/*
	@method: is_template( $template="" )
	@description: Checks is we're ona certain template
	@params:
	@shortcode:  
	@return:
	*/
	public function is_template( $template="" ){ return $this->on_template( $template ); }
	public function on_template( $template="" ){

		// Check if a boolean check or not
		if( $template != "" ){
	
			return $this->private->page[ $this->is_slug() ]["template"] == $this->private->page[ $template ]["template"];
			
		} else {
			
			return $this->private->page[ $this->is_slug() ]["template"];
		
		// if
		}
			
	// method
	}

	/*
	@method: is_theme( $theme="" )
	@description: Checks is we're ona certain template
	@params:
	@shortcode:  
	@return:
	*/
	public function is_theme( $theme="" ){ return $this->on_theme( $theme ); }
	public function on_theme( $theme="" ){

		// Check if a boolean check or not
		if( $theme != "" ){
	
			return $this->private->theme == $theme;
			
		} else {
			
			return $this->private->theme;
		
		// if
		}
			
	// method
	}

	/*
	@method: is_mode( $mode="" )
	@description: Checks which more we're on web/mobile/facebook
	@params:
	@shortcode:  
	@return:
	*/
	public function is_mode( $mode="" ){ return $this->on_mode( $mode ); }
	public function on_mode( $mode="" ){
		
		// Check if a boolean check or not
		if( $mode != "" ){
	
			return $this->public->mode == $mode;
			
		} else {
			
			return $this->public->mode;
		
		// if
		}
		
	// method
	}

	/*
	@method: is_plugin( $mode="" )
	@description: Checks which plugin this page belongs to
	@params:
	@shortcode:  
	@return:
	*/
	public function is_plugin( $plugin="" ){ return $this->on_plugin( $plugin ); }
	public function on_plugin( $plugin="" ){
		
		global $form;
		
		// Check if we're using xhr or not
		if( $form->post("scms_feed_slug") ){
			
			$slug = $form->post("scms_feed_slug");
			
		} else {
			
			$slug = $this->is_slug();
			
		// if
		}

		// Check if a boolean check or not
		if( $plugin != "" ){
	
			return $this->private->page[ $slug ]["plugin"] == $plugin;
			
		} else {
			
			return $this->private->page[ $slug ]["plugin"];
		
		// if
		}
		
	// method
	}
	/*
	@method: is_modal( $modal="" )
	@description: Checks is this is a modal window
	@params:
	@shortcode:  
	@return:
	*/
	public function is_modal( $slug="" ){ return $this->on_modal( $slug ); }
	public function on_modal( $slug="" ){
	
		// Check if a boolean check or not
		if( $slug != "" ){
	
			return (bool)$this->private->page[ $slug ]["modal"] && $this->querystring("bento_scms_modal");
			
		} else {
			
			return (bool)$this->private->page[ $this->is_slug() ]["modal"] && $this->querystring("bento_scms_modal");
		
		// if
		}
		
	// method
	}

	/*
	@method: is_error()
	@description: Checks if a page is an error page
	@params:
			$slug (optional, string), the slug to check if it's the error page, defaults to the current page
	@shortcode:  
	@return:
	*/
	public function is_error( $slug="" ){ return $this->on_error( $slug ); }
	public function on_error( $slug="" ){

		// Check if a boolean check or not
		if( $slug == "" ){
	
			return (bool)$this->private->page[ $this->is_slug() ]["error"];
			
		} else {
			
			if( isset( $this->private->page[ $slug ] ) ){
			
				return (bool)$this->private->page[ $slug ]["error"];
		
			} else {
			
				return false;
			
			// if
			}
		
		// if
		}
		
	// method
	}

	/*
	@method: is_help()
	@description: Checks if a page is a help page
	@params:
	@shortcode:  
	@return:
	*/
	public function is_help( $slug="" ){ return $this->on_help( $slug ); }
	public function on_help( $slug="" ){

		// Check if a boolean check or not
		return $this->is_slug("help");
		
	// method
	}

	/*
	@method: is_admin()
	@description: Checks we're in the administration
	@params:
	@shortcode:  
	@return:
	*/
	public function is_admin(){ return $this->on_admin(); }
	public function on_admin(){

		// Check if a boolean check or not
		return $this->is_slug("admin");
		
	// method
	}

	/*
	@method: is_hidden()
	@description: Checks if a page is hidden or not (hidden page aren't "remembered" and excluded from the sitemap.
	@params:
			$slug (optional, string), the slug to check if it's the hidden page, defaults to the current page
	@shortcode:  
	@return:
	*/
	public function is_hidden( $slug="" ){ return $this->on_hidden( $slug ); }
	public function on_hidden( $slug="" ){

		// Check if a boolean check or not
		if( $slug == "" ){
	
			return (bool)$this->private->page[ $this->is_slug() ]["hidden"];
			
		} else {
			
			if( isset( $this->private->page[ $slug ] ) ){
			
				return (bool)$this->private->page[ $slug ]["hidden"];
		
			} else {
			
				return false;
			
			// if
			}
		
		// if
		}
		
	// method
	}

	/*
	@method: language( $language="" )
	@description: Checks which language is selected
	@params:
	@shortcode:  
	@return:
	*/
	public function language( $language="" ){ return $this->on_language( $language ); }
	public function is_language( $language="" ){ return $this->on_language( $language ); }
	public function on_language( $language="" ){
	
		global $language;
	
		// Check it
		return $language->selected();

	// method
	}

	/*
	@method: domain( $domain="" )
	@description: Checks which language is selected
	@params:
	@shortcode:  
	@return:
	*/
	public function domain( $domain="" ){ return $this->on_domain( $domain ); }
	public function is_domain( $domain="" ){ return $this->on_domain( $domain ); }
	public function on_domain( $domain="" ){ 

		return $this->public->{ "domain_" . $this->is_mode() };

	// method
	}

	/*
	@method: tagline( $tagline="" )
	@description: Outputs the tagline
	@params:
	@shortcode:  
	@return:
	*/
	public function tagline( $tagline="" ){ return $this->on_tagline( $tagline ); }
	public function is_tagline( $tagline="" ){ return $this->on_tagline( $tagline ); }
	public function on_tagline( $tagline="" ){ 

		return $this->public->tagline;

	// method
	}
	
	/*
	@method: is_home()
	@description: Checks if we're on the homepage
	@params:
			$slug (optional, string): The slug of the page you want to return, defaults to current page if none is given
	@shortcode:
	@return: returns true or false
	*/
	public function is_home( $slug="" ){ return $this->on_home( $slug ); }
	public function on_home( $slug="" ){

		// Check if a boolean check or not
		if( $slug != "" ){
	
			return $this->private->page[ $slug ]["home"] && $this->private->page[ $slug ]["home"] == $home;
			
		} else {
			
			return $this->private->page[ $this->is_slug() ]["home"];
		
		// if
		}
		
	// method
	}

	/*
	@method: is_forward( $slug="" )
	@description: Get's the forward url when not authenticated
	@params:
	@shortcode:  
	@return:
	*/
	public function is_forward( $slug="" ){ return $this->on_forward( $slug ); }
	public function on_forward( $slug="" ){
	
		// Check if a boolean check or not
		if( $slug != "" ){
	
			return $this->url(
							array(
								"slug"	=>	$this->private->page[ $slug ]["forward"],
								"variables"	=>	array(
													$this->private->message->querystring	=>	$this->private->message->text
								)
							)
						);
			
		} else {
			
			return $this->url(
							array(
								"slug"	=>	$this->private->page[ $this->is_slug() ]["forward"],
								"variables"	=>	array(
													$this->private->message->querystring	=>	$this->private->message->text
								)
							)
						);
		
		// if
		}
		
	// method
	}

	/*
	@method: page_id()
	@description: Returns the id of a page
	@params:
			$slug (optional, string): The slug of the page you want to return, defaults to current page if none is given
	@shortcode:
	@return: parent_id or false is none is found
	*/
	public function page_id( $slug="" ){

		// Check if a boolean check or not
		if( $slug != "" ){
	
			return $this->private->page[ $this->is_slug() ]["id"];
			
		} else {
			
			if( isset($this->private->page[ $slug ]) ){
			
				return $this->private->page[ $slug ]["id"];
			
			} else {
			
				return 0;
			
			// if
			}
		
		// if
		}
		
	// method
	}

	/*
	@method: parent_id()
	@description: Returns a parent if of a page
	@params:
			$slug (optional, string): The slug of the page you want to return, defaults to current page if none is given
	@shortcode:
	@return: parent_id or 0 is none is found
	*/
	public function parent_id( $slug="" ){

		// Check if a boolean check or not
		if( $slug != "" ){
	
			return $this->private->page[ $slug ]["parent_id"];
			
		} else {
			
			if( isset($this->private->page[ $this->is_slug() ]) ){
			
				return $this->private->page[ $this->is_slug() ]["parent_id"];
			
			} else {
			
				return 0;
			
			// if
			}
		
		// if
		}
		
	// method
	}

	/*
	@method: get_parent()
	@description: Returns a parent slug of a page
	@params:
			$slug (optional, string): The slug of the page you want to return, defaults to current page if none is given
	@shortcode:
	@return: slug or false is none is found
	*/
	public function get_parent( $id="" ){
	
		// Loop through the pages
		foreach( $this->private->page as $slug => $page ){
		
			if( $page["id"] == $id ){
			
				return $slug;
			
			// if
			}
		
		// foreach
		}
		
		return false;
		
	// method
	}

	/*
	@method: has_feed()
	@description: Check if there's a feeed on this page
	@params:
	@shortcode:  
	@return:
	*/
	public function has_feed(){
	
		return isset($this->private->page[ $this->is_slug() ]) && (bool)$this->private->page[ $this->is_slug() ]["feed"];
		
	// method
	}

	/*
	@method: meta( $type, $echo=true )
	@description: Returns the meta information for the page
	@params:
	@shortcode:  
	@return:
	*/
	public function meta( $type, $echo=true ){

		// this will include this function
		$tmp_return =  htmlentities($this->private->page[ $this->is_slug() ][ $type ]);
		
		// Make sure theres meta info
		if( $tmp_return == "" ){ return ""; }
		
		// Otherwise, return a translated tag
		$tmp_return = "<!--language:translate:" .  $tmp_return . "-->";
		
		// Check if we have any notifications
		if( $type == "title" && $this->notifications() > 0 ){
			
			$tmp_return = "(" . $this->notifications() . ") " . $tmp_return;
			
		// if
		}
		
		// Return it
		return $echo ? print $tmp_return : $tmp_return;
		
	// method
	}

	/*
	@method: set_meta( $type, $text )
	@description: Set's the meta for a page
	@params:
	@shortcode:  
	@return:
	*/
	public function set_meta( $type, $text ){

		// this will include this function
		$this->private->page[ $this->is_slug() ][ $type ] = $text;
		
		return true;
		
	// method
	}

	/*
	@method: javascript( $echo=true )
	@description: This loads up the javascript
	@params:
	@shortcode:  
	@return:
	*/
	public function javascript( $echo=true ){
		
		// Output it
		$tmp_return = "<!--bento:javascript-->";
		
		// Return it
		return $echo ? print $tmp_return : $tmp_return;
		
	// method
	}

	/*
	@method: css( $echo=true )
	@description: This loads up the css
	@params:
	@shortcode:  
	@return:
	*/
	public function css( $echo=true ){
	
		// Output it
		$tmp_return = "<!--bento:css-->";
		
		// Return it
		return $echo ? print $tmp_return : $tmp_return;
		
	// method
	}

	/*
	@method: add_file( $options )
	@description: Adds a file to the program
	@params:
	@shortcode:  
	@return:
	*/
	public function add_file( $options ){ return $this->add( $options ); }
	public function add( $options ){
		
		global $bento;
	
		// Check it
		if( !isset($options["type"]) ){ return false; }

		// Guilty til proven innocent
		$f = NULL;
		
		// Clear this up
		if( !isset($options["file"]) && isset($options["name"]) && !isset($options["plugin"]) ){
			
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
		// if
		}

		// Check if we're loading by plugin
		if( isset($options["plugin"]) && isset($options["name"]) ){
	
			// Get the file name
			$options["file"] = $options["name"] . "." . $options["type"];
			$options["file"] = str_replace($_SERVER['DOCUMENT_ROOT'],"",$options["file"]);
			
			// Check what kind of file we're working with
			if( $options["type"] == "php" ){
	
				// Potential location for the files - start with manual
				$manual = $this->private->directory->plugins ."/" . $options["plugin"]. "/" . $bento->public->assets->{ $options["type"] } . "/" . $bento->private->manual . "/" . $options["file"];
				$auto = $this->private->directory->plugins . "/" . $options["plugin"] . "/" . $bento->public->assets->{ $options["type"] } . "/" . $bento->private->auto . "/" . $options["file"];

				// Check if this is the auto plugin
				if( file_exists( $manual ) ){
			
					$f = $manual;
		
				// Check if this exists
				} else if( file_exists( $auto ) ){
				
					$f = $auto;
				
				// if
				}

			// Let's check this js and css			
			} else {

				// Get the 2 places it could be
				$dirs = array(
							$this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $options["type"],
							$this->private->directory->theme . "/" . $options["type"]
						);
				
				// Go through the load types	
				foreach( $dirs as $dir ){
					
					// Let's go through it
					foreach( array("manual","auto") as $load ){
						
						// Go through here
						if( file_exists( $dir . "/" . $load . "/" . $options["file"] ) ){
						
							// Get the file
							$f = $dir . "/" . $load . "/" . $options["file"];
							
							break;
							
						// if
						}
						
					// foreach
					}
					
				// foreach
				}
					
			// if
			}
	
		// if
		} else if( isset( $options["file"] ) ){
		
			$f = $options["file"];
			
		// if
		} 
		
		// Check if we found a file
		if( is_null($f) ){ return false; }
	
		// Now add the file
		$bento->add_file(
						array(
							"type"	=>	$options["type"],
							"file"	=>	$f
						)
					);
		
	// method
	}

	/*
	@method: add_files( $options )
	@description: Adds files from directory (without support from the file class)
	@params:
	@shortcode:  
	@return:
	*/
	public function add_files( $options ){

		// Set it up
		$library = $options["directory"];
		$files = array();

		// Loop through the filesanual
		if( file_exists( $library ) && $handle = opendir( $library ) ) {

			// Loop through the files
			while (false !== ($f1 = readdir($handle))){
		
				// Make sure it's not a directory
				if( $f1 != "." && $f1 != ".." && is_file( $library . "/" . $f1) ){

					$files[] = $f1;					
	
				// if
				}
				
			// while
			}
			
			// Sort the files
			sort($files);
			
			// Not that they're sorted, add them
			foreach( $files as $f1 ){
			
					// Add the css and js files	
					$this->add(
								array(
									"type"	=>	$options["type"],
									"file"	=>	$library . "/" . $f1
								)
							);	
							
			// foreach
			}
		
		// if
		}

	// method
	}

	/*
	@method: add_php( $options )
	@description: Adds js to the js registry for outputting
	@params:
	@shortcode:  
	@return:
	*/
	public function add_php( $options ){
	
		// Set that it's css
		$options["type"] = "php";
	
		// Clear this up
		if( !isset($options["file"]) && isset($options["name"]) && !isset($options["plugin"]) ){
			
			// Get the referring class if there isn't one
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
			// remove the support files stuffs
			$options["plugin"] = str_replace("_page","",$options["plugin"]);
			$options["plugin"] = str_replace("_feed","",$options["plugin"]);
			$options["plugin"] = str_replace("_url","",$options["plugin"]);
			$options["plugin"] = str_replace("_handler","",$options["plugin"]);
			
		// if
		}
	
		// Add the file type
		return $this->add($options);
	
	// method
	}

	/*
	@method: add_javascript( $options )
	@description: Adds js to the js registry for outputting
	@params:
	@shortcode:  
	@return:
	*/
	public function add_javascript( $options ){ $this->add_js( $options ); }
	public function add_js( $options ){
	
		// Set that it's css
		$options["type"] = "js";

		// Clear this up
		if( !isset($options["file"]) && isset($options["name"]) && !isset($options["plugin"]) ){
			
			// Get the referring class if there isn't one
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
			// remove the support files stuffs
			$options["plugin"] = str_replace("_page","",$options["plugin"]);
			$options["plugin"] = str_replace("_feed","",$options["plugin"]);
			$options["plugin"] = str_replace("_url","",$options["plugin"]);
			$options["plugin"] = str_replace("_handler","",$options["plugin"]);
			
		// if
		}
	
		// Add the file type
		return $this->add($options);
	
	// method
	}

	/*
	@method: add_css( $options )
	@description: Adds css to the css registry for outputting
	@params:
	@shortcode:  
	@return:
	*/
	public function add_css( $options ){
	
		// Set that it's css
		$options["type"] = "css";
		
		// Clear this up
		if( !isset($options["file"]) && isset($options["name"]) && !isset($options["plugin"]) ){
			
			// Get the referring class if there isn't one
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
			// remove the support files stuffs
			$options["plugin"] = str_replace("_page","",$options["plugin"]);
			$options["plugin"] = str_replace("_feed","",$options["plugin"]);
			$options["plugin"] = str_replace("_url","",$options["plugin"]);
			$options["plugin"] = str_replace("_handler","",$options["plugin"]);
			
		// if
		}
	
		// Add the file type
		return $this->add($options);
	
	// method
	}

	/*
	@method: url( $variables )
	@description: Returns a url for a page
	@params:
	@shortcode:  
	@return:
	*/
	public function url( $variables ){
	
		global $scms_url,$form,$language;
	
		// Convert a strng to an array if it is so
		if( !is_array($variables) ){
		
			// Set the page as the one and only
			$variables = array("slug"	=>	$variables);
			
		// if
		}
	
		// Break it out
		( isset($variables["slug"]) ) ? $slug = $variables["slug"] : $slug = "";
		( isset($variables["title"]) ) ? $title = $variables["title"] : NULL;
		( isset($variables["variables"]) ) ? $vars = $variables["variables"] : $vars = "";
		
		// If the current plugin as this
		$support_class = $this->is_plugin() . "_url";
		
		// Check if there's a custom url constructor
		if( class_exists( $support_class ) && method_exists($GLOBALS[ $support_class ],$variables["slug"]) ){
		
			// Make sure we pass it something
			if( !isset($variables["variables"]) ){ $variables["variables"] = array(); }
		
			// Get the url
			return $GLOBALS[ $support_class ]->$variables["slug"]( $variables["variables"] );
		
		// if
		}

		// Check if it's the home or error page
		if( strtolower($slug) == "home" || strtolower($slug) == "error" ){
		
			// Return it
			// $this->private->page[ $slug ]["url"] = $this->private->setup[ $slug ];

		// Check if it's the home or error page
		} else if( strtolower($slug) == "facebook" ){
		
			global $facebook;

			// If		
			if( !class_exists("facebookConnect") ){ return; }
	
			// Return it
			$this->private->page["facebook"]["url"] = $facebook->url();
			
		// Check if it's the home or error page
		} else if( strtolower($slug) == "twitter" ){
		
			global $twitter;

			// If		
			if( !class_exists("twitterConnect") ){ return; }
		
			// Return it
			$this->private->page["twitter"]["url"] = $twitter->url();
			
		// if
		} else if( strtolower($slug) == "help" ) {

			// Tell them what we're loading info for
			if( !isset($vars["slug"]) ){
				$vars["slug"] = $this->is_slug();
			// if
			}
		
		// if
		}
		
		// IF we don't have this page, let's get rid of it
		if( !isset($this->private->page[ $slug ]) ){ return false; }

		// Check if this is right
		if( (bool)$this->private->page[ $slug ]["modal"] ){
			$vars["bento_scms_modal"] = true;
		}
		if( $vars != "" ){
			if( !is_array($vars) ){ $vars = array("id"	=>	$vars); }
			$vars = json_encode($vars);
			if( !$form->submitting() ){
				$vars = htmlentities($vars);
			}
		} else {
			if( (bool)$this->private->page[ $slug ]["modal"] ){
				$vars = "";
			}
		}
		
		// Page doesn't exist
		if( isset($this->private->page[ $slug ]) ){
		
			// Check the title
			if( !isset($title) || is_null($title) ){ $title = $this->private->page[ $slug ]["title"];}

			// this will include this functio
			$str = $this->private->page[ $slug ]["url"];
			$str = trim(str_replace("!--scms:link:vars--",trim($vars),$str ));
	
			// Assign the new title
			$str = trim(str_replace("!--scms:modal:title--",$title,$str ));

			// Check if it contains a variable
			if( !stristr($title,"%") ){
				$title= "<!--language:translate:" . $title . "-->";
			// There's are variables here
			} else {
				// Find the variables	
				preg_match_all("@%([^%]+|)%@",$title,$tmp);
				// Loop through it
				foreach( $tmp[0] as $variable ){
					// Set it up
					$title = str_replace( $variable,"",$title );
				// foreach
				}
				$title= "<!--language:translate:" . trim($title) . "-->" . implode("",$tmp[1]);
			// if
			}

			// Translate it
			$str = $language->translate($str);
			
			// Here you go
			if( substr(trim($str), -1) == "?"){
				$str = str_replace("?","",$str);
			}else if( substr(trim($str), -2) == "?"){
				$str = str_replace("?","",$str);
			}else if( substr($str, -3) == '?'){
				$str = str_replace('?',"",$str);
			}
			
			return htmlspecialchars($str);
			
		} else {
		
			false;
			
		}
		
	// method
	}

	/*
	@method: part( $number )
	@description: Breaks apart a friendly url into pieces to use as variables
	@params:
	@shortcode:  
	@return:
	*/
	public function p( $number ){ return $this->part( $number ); }
	public function part( $number ){

		return ( isset($this->private->parts) && isset($this->private->parts[ $number ]) ) ? $this->private->parts[ $number ] : false;

	// method
	}

	/*
	@method: link( $variables=array(), $echo=true )
	@description: Outputs a link for a page
	@params:
	@shortcode:  
	@return:
	*/
	public function link( $variables=array(), $echo=true ){

		global $db;

		// Break it out
		( isset($variables["slug"]) ) ? $slug = $variables["slug"] : $slug = $db->record("page.slug");
		( isset($variables["anchor"]) ) ? $anchor = $variables["anchor"] : $anchor = "";
		( isset($variables["variables"]) ) ? $vars = $variables["variables"] : $vars = "";
		( isset($variables["id"]) ) ? $id = "id=\"" . $variables["id"] . "\"" : $id = "";
		( isset($variables["class"]) ) ? $class = $variables["class"] : $class = "";
		( isset($variables["echo"]) ) ? $echo = $variables["echo"] : $echo = $echo;

		// Check if this is a admin
		if( strtolower($slug) == "admin" ){

			// Add the page we're working on
			$vars = array(
						"page"	=>	isset($variables["variables"]["page"]) ? $variables["variables"]["page"] : "page_edit" ,
						"slug"	=>	!isset($variables["variables"]["slug"]) ? $this->is_slug() : $variables["variables"]["slug"],
						"id"	=>	!isset($variables["variables"]["id"]) ? 0 : $variables["variables"]["id"]
						);
		
		// if
		} else if( strtolower($slug) == "help" ) {

			// Set which plugin it is
			$vars["plugin"] = $this->is_plugin();

			// Tell them what we're loading info for
			if( !isset($variables["variables"]["slug"]) ){
				$vars["variables"]["slug"] = $this->is_slug();
			// if
			}
		
		// if
		} else if( strtolower($slug) == "facebook" ){
		
			global $facebook;

			// If		
			if( !class_exists("facebookConnect") ){ return; }

			// Add the page we're working on
			$vars = array("scms_facebook"	=>	true );
			$this->private->page["facebook"]["name"] = "Facebook";
			$this->private->page["twitter"]["anchor"] = "Facebook";
			$this->private->page["twitter"]["title"] = "Facebook";
			$this->private->page["facebook"]["modal"] = false;
			$this->private->page["facebook"]["url"] = $facebook->url();
			$this->private->page["facebook"]["permission"] = array();
			$this->private->page["facebook"]["link"] = "<a href='<!--scms:link:url-->' <!--scms:link:id--> class='<!--scms:link:class-->'><!--scms:link:anchor--></a>";

		// if
		} else if( strtolower($slug) == "twitter" ){
		
			global $twitter;

			// If		
			if( !class_exists("twitterConnect") ){ return; }
			
			// Add the page we're working on
			$vars = array("scms_twitter"	=>	true );
			$this->private->page["twitter"]["name"] = "Twitter";
			$this->private->page["twitter"]["anchor"] = "Twitter";
			$this->private->page["twitter"]["title"] = "Twitter";
			$this->private->page["twitter"]["modal"] = false;
			$this->private->page["twitter"]["url"] = $twitter->url();
			$this->private->page["twitter"]["permission"] = array();
			$this->private->page["twitter"]["link"] = "<a href='<!--scms:link:url-->' <!--scms:link:id--> class='<!--scms:link:class-->'><!--scms:link:anchor--></a>";
	
		// Check if this is a page
		} else if( $slug == "language" ){
		
			// No button for this language
			if( $vars == $this->is_language() ){
			
				// Echo or return it
				return $echo ? print "" : "";
				
			// if
			}
		
			// Assign the current page
			$slug = $this->is_slug();
			$vars = array("language"	=>	$vars);
			
		// if
		} 
	
		// Make sure we have this page	
		if( !isset( $this->private->page[ $slug ] ) ){ return ""; }
		
		// Set up the title (for modals)
		( isset($variables["title"]) ) ? $title = $variables["title"] : $title = $this->private->page[ $slug ]["title"];
		
		// Check it
		foreach( array("anchor") as $var ){
			// Check the language
			if( isset($vars["language"])){
				$$var = ucwords($vars["language"]);
			// if
			}
			if( !isset($$var) || $$var=='' ){
				$$var = "<!--language:translate:" . $this->private->page[ $slug ]["anchor"] . "-->";
			} else {
				// Make sure it's not a tag
				if( !stristr($$var,"<") ){
					// Check if it contains a variable
					if( !stristr($$var,"%") ){
						$$var = "<!--language:translate:" . $$var . "-->";
					// There's are variables here
					} else {
						// Find the variables	
						preg_match_all("@%([^%]+|)%@",$$var,$tmp);
						// Loop through it
						foreach( $tmp[0] as $variable ){
							// Set it up
							$$var = str_replace( $variable,"",$$var );
						// foreach
						}
						$$var = "<!--language:translate:" . trim($$var) . "-->" . implode("",$tmp[1]);
					// if
					}
				// if
				}
			// if
			}
		// foreach
		}
		// Check if this is right
		if( (bool)$this->private->page[ $slug ]["modal"] ){
			$vars["bento_scms_modal"] = true;
		}
		
		if( !isset($variables["variables"]) ){ $variables["variables"] = array(); }

		// Output it
		$str = str_replace("<!--scms:link:anchor-->",$anchor,$this->private->page[ $slug ]["link"]);
		$str = str_replace("<!--scms:link:url-->",$this->url(array("slug"	=>	$slug,"title"	=>	$title,	"variables"	=>	$vars)),$str );
		$str = str_replace("<!--scms:link:id-->",$id,$str );
		$str = str_replace("<!--scms:link:class-->",$class,$str );
		$str = str_replace('?""',"",$str );
		
		// Output it?
		$output = true;
		
		// Check it 
		if( count($this->private->page[ $slug ]["permission"]) > 0 ){
			
			// Output it?
			$output = false;
			
			// Loop through the permissions
			foreach( $this->private->page[ $slug ]["permission"] as $permission_id ){
	
				// if authenticate we're good
				if( $this->authenticated( $permission_id ) ){
				
					$output = true;
					break;
				
				// if	
				}
	
			// foreach
			}
			
		// if
		} else  {
			
			$output = true;
			
		// if
		}

		// Make sure we have permissions
		if( !$output ){
	
			// Check it out
			$str = "";
		
		// if
		}
		
		// Echo or return it
		return $echo ? print $str : $str;
		
	// method
	}

	/*
	@method: button( $variables=array(), $echo=true )
	@description: Outputs a button for a page
	@params:
	@shortcode:  
	@return:
	*/
	public function button( $variables=array(), $echo=true ){

		global $db;

		// Break it out
		( isset($variables["slug"]) ) ? $slug = $variables["slug"] : $slug = $db->record("page.slug");
		( isset($variables["anchor"]) ) ? $anchor = $variables["anchor"] : $anchor = "";
		( isset($variables["title"]) ) ? $title = $variables["title"] : $title = $this->is_title( $slug );
		( isset($variables["variables"]) ) ? $vars = $variables["variables"] : $vars = "";
		( isset($variables["id"]) ) ? $id = "id=\"" . $variables["id"] . "\"" : $id = "";
		( isset($variables["class"]) ) ? $class = $variables["class"] : $class = "";
		( isset($variables["echo"]) ) ? $echo = $variables["echo"] : $echo = $echo;
		( isset($variables["type"]) ) ? $type = $variables["type"] : $type = "button";
		
		// Add teh button class to it
		$class .= " button";

		// Check if this is a admin
		if( strtolower($slug) == "admin" ){

			// Add the page we're working on
			$vars = array("page"	=>	"page_edit","slug"	=>	$this->is_slug());
		
		// if
		} else if( strtolower($slug) == "help" ) {

			// Set which plugin it is
			$vars["plugin"] = $this->is_plugin();

			// Tell them what we're loading info for
			if( !isset($variables["variables"]["slug"]) ){
				$vars["variables"]["slug"] = $this->is_slug();
			// if
			}
		
		// if
		} else if( strtolower($slug) == "facebook" ){
		
			global $facebook;

			// If		
			if( !class_exists("facebookConnect") ){ return; }

			// Add the page we're working on
			$vars = array("scms_facebook"	=>	true );
			$this->private->page["facebook"]["name"] = "Facebook";
			$this->private->page["facebook"]["anchor"] = "Facebook";
			$this->private->page["facebook"]["title"] = "Facebook";
			$this->private->page["facebook"]["modal"] = false;
			$this->private->page["facebook"]["url"] = $facebook->url();
			$this->private->page["facebook"]["permission"] = array();
			$this->private->page["facebook"]["button"] = "<a href='<!--scms:link:url-->' <!--scms:link:id--> class='<!--scms:link:class-->'><!--scms:link:anchor--></a>";

		// if
		} else if( strtolower($slug) == "twitter" ){
		
			global $twitter;

			// If		
			if( !class_exists("twitterConnect") ){ return; }
			
			// Add the page we're working on
			$vars = array("scms_twitter"	=>	true );
			$this->private->page["twitter"]["name"] = "Twitter";
			$this->private->page["twitter"]["anchor"] = "Twitter";
			$this->private->page["twitter"]["title"] = "Twitter";
			$this->private->page["twitter"]["modal"] = false;
			$this->private->page["twitter"]["url"] = $twitter->url();
			$this->private->page["twitter"]["permission"] = array();
			$this->private->page["twitter"]["button"] = "<a href='<!--scms:link:url-->' <!--scms:link:id--> class='<!--scms:link:class-->'><!--scms:link:anchor--></a>";
	
		// Check if this is a page
		} else if( strtolower($slug) == "language" ){
		
			// No button for this language
			if( $vars == $this->is_language() ){
			
				// Echo or return it
				return $echo ? print "" : "";
				
			// if
			}
		
			// Assign the current page
			$slug = $this->is_slug();
			$vars = array("language"	=>	$vars);
			
		// Check it
		} else if( 
					strtolower($slug) == "close" ||
					strtolower($slug) == "no" ||
					strtolower($slug) == "hide" ||
					strtolower($slug) == "back" ||
					strtolower($slug) == "cancel"
		
					){
					
			// Check if we're in a modal window or not	
			if( $this->is_modal() ){
				
				$url = "javascript:bento.scms.modal.close();";
				
			} else {
			
				$url = "javascript:history.go(-1);";
			
			// if	
			}
		
			// Add the page we're working on
			$this->private->page[ $slug ]["name"] = "Close";
			$this->private->page[ $slug ]["anchor"] = ucwords($slug);
			$this->private->page[ $slug ]["title"] = "Close";
			$this->private->page[ $slug ]["modal"] = false;
			$this->private->page[ $slug ]["url"] = "javascript:;";
			$this->private->page[ $slug ]["permission"] = array();
			$this->private->page[ $slug ]["button"] = '<input onclick="' . $url . '" type="button" class="<!--scms:link:class--> red close" value="<!--scms:link:anchor-->">';

		// Submit button
		} else if( 
					strtolower($slug) == "submit" ||
					strtolower($slug) == "search" ||
					strtolower($slug) == "yes" ||
					strtolower($slug) == "continue" || 
					strtolower($slug) == "retrieve" ||
					strtolower($slug) == "apply" ||
					strtolower($slug) == "register"
					){
		
			// Add the page we're working on
			$this->private->page[ $slug ]["name"] = "Submit";
			$this->private->page[ $slug ]["title"] = "Submit";
			$this->private->page[ $slug ]["anchor"] = ucwords($slug);
			$this->private->page[ $slug ]["modal"] = false;
			$this->private->page[ $slug ]["url"] = "javascript:;";
			$this->private->page[ $slug ]["permission"] = array();
			$this->private->page[ $slug ]["button"] = '<input type="submit"  class="<!--scms:link:class--> blue submit" value="<!--scms:link:anchor-->">';

		// if
		}
		
		// Check it out
		if( !isset($this->private->page[ $slug ]) ){ return false; }
		
		// Check it
		foreach( array("anchor") as $var ){
			// Check the language
			if( isset($vars["language"])){
				$$var = ucwords($vars["language"]);
			// if
			}
			if( !isset($$var) || $$var=='' ){
				$$var = "<!--language:translate:" . $this->private->page[ $slug ]["anchor"] . "-->";
			} else {
				// Make sure it's not a tag
				if( !stristr($$var,"<") ){
					// Check if it contains a variable
					if( !stristr($$var,"%") ){
						$$var = "<!--language:translate:" . $$var . "-->";
					// There's are variables here
					} else {
						// Find the variables	
						preg_match_all("@%([^%]+|)%@",$$var,$tmp);
						// Loop through it
						foreach( $tmp[0] as $variable ){
							// Set it up
							$$var = str_replace( $variable,"",$$var );
						// foreach
						}
						$$var = "<!--language:translate:" . trim($$var) . "-->" . implode("",$tmp[1]);
					// if
					}
				// if
				}
			// if
			}
		// foreach
		}
		// Check if this is right
		if( (bool)$this->private->page[ $slug ]["modal"] ){
			$vars["bento_scms_modal"] = true;
		}
		
		if( !isset($variables["variables"]) ){ $variables["variables"] = array(); }

		// Output it
		$str = str_replace("<!--scms:link:anchor-->",$anchor,$this->private->page[ $slug ]["button"]);
		$str = str_replace("<!--scms:link:url-->",$this->url(array("slug"	=>	$slug,"title"	=>	$title,	"variables"	=>	$vars)),$str );
		$str = str_replace("<!--scms:link:id-->",$id,$str );
		$str = str_replace("<!--scms:link:class-->",$class,$str );
		$str = str_replace("<!--scms:link:type-->",$type,$str );
		$str = str_replace('?""',"",$str );
		
		// Check it 
		if( count($this->private->page[ $slug ]["permission"]) > 0 ){
			
			// Output it?
			$output = false;
			
			// Loop through the permissions
			foreach( $this->private->page[ $slug ]["permission"] as $permission_id ){
	
				// if authenticate we're good
				if( $this->authenticated( $permission_id ) ){
				
					$output = true;
					break;
				
				// if	
				}
	
			// foreach
			}
			
		// if
		} else  {
			
			$output = true;
			
		// if
		}

		// Make sure we have permissions
		if( !$output ){
	
			// Check it out
			$str = "";
		
		// if
		}
		
		// Echo or return it
		return $echo ? print $str : $str;
		
	// method
	}
	
	/*
	@method: q( $var='id' )
	@description: Gets a querstring from a json
	@params:
	@shortcode:  
	@return:
	*/
	public function q( $var='id' ){ return $this->querystring($var); }
	public function querystring( $var='id' ){
		
		// Check it
		if( isset($_SERVER['QUERY_STRING']) ){
			// Set it up
			$value = trim($_SERVER['QUERY_STRING']);
			// Decode it
			$value = json_decode((string)htmlspecialchars_decode(urldecode($value)));
			if( isset($value->{ $var }) ){
				$value = $value->{ $var };
			} else {
				$value = false;
			}
		} else {
			$value = false;
		// if
		}

		// Return the button
		return $value;
		
	// method
	}

	/*
	@method: https( $http="" )
	@description: Returns the protcol (http, or https)
	@params:
	@shortcode:  
	@return:
	*/
	public function https( $http="" ){ return $this->http( $http  ); }
	public function http( $http="" ){
	
		// Comparison check
		if( $http != "" ){
	
			return $this->private->http == (( !stristr($http,"://") ) ? ($http . "://") : $http); die();
			
		// Just return it
		} else {
		
			return $this->private->http;
		
		// if
		}

	// method
	}

	/*
	@method: open( $variables )
	@description: Like form->open, this will create a form with the page name
	@params:
	@shortcode:  
	@return:
	*/
	public function open( $variables ){	
	
		global $form,$db;
		
		// Set this up 
		$variables["id"] = $this->form();
		$variables["handler"] = $this->handler();
	
		// Open the form
		$form->open( $variables );

	// method
	}

	/*
	@method: close()
	@description: Like form->close, this will close a form
	@params:
	@shortcode:  
	@return:
	*/
	public function close(){
	
		global $form;
		
		$form->close();	

	// method
	}

	/*
	@method: form()
	@description: Creates a form id for the modal window system
	@params:
	@shortcode:  
	@return:
	*/
	public function form(){	
	
		return "form_" . $this->is_page();

	// method
	}

	/*
	@method: handler()
	@description: Creates a form handler
	@params:
	@shortcode:  
	@return:
	*/
	public function handler(){	
	
		global $handler;
		
		// Check if there's one for scms
		if( method_exists($this,$this->is_page()) ){
	
			return "scms->" . $this->is_page();
			
		// if
		}
		
		// Check if the method exists
		if( method_exists($handler,$this->is_page()) ){
		
			return "handler->" . $this->is_page();
		
		// Check it out
		}

	// method
	}	

	/*
	@method: mail( $variables )
	@description: Creates multitype email
	@params:
	@shortcode:  
	@return:
	*/
	public function mail( $variables ){
	
		global $bento,$db,$form,$language;
		
		// Set this up for ease of use
		$scms = $this;
		
		// Make sure all the variables exist
		if( !isset($variables["to"]) || !isset($variables["slug"]) ){ return false; }
		
		// Check if we have the mail or not
		if( isset( $this->private->mail[ $variables["slug"] ]) ){
		
			// Assign thins
			if( !isset($variables["subject"]) ){ $variables["subject"] = $this->private->mail[ $variables["slug"] ]["subject"]; }
			if( !isset($variables["from_name"]) ){ $variables["from_name"] = $this->private->mail[ $variables["slug"] ]["from_name"] ; }
			if( !isset($variables["from_email"]) ){ $variables["from_email"] = $this->private->mail[ $variables["slug"] ]["from_email"] ; }
			if( !isset($variables["template"]) ){ $variables["template"] = $this->private->mail[ $variables["slug"] ]["template"]; }
		
		// if
		} else {
		
			return false;
		
		// if
		}	
		
		// Check it it's set
		if( !isset($this->private->mail[ $variables["slug"] ]) ){ return; }
		
		// Check the files
		if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->mail . "/" . $this->is_language() . "/" . strtolower($variables["slug"]) . ".php") ){

			$variables["file"] = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->mail . "/" . $this->is_language() . "/" . strtolower($variables["slug"]) . ".php";

		// Not language specific	
		} else if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->mail . "/" . strtolower($variables["slug"]) . ".php") ){
		
			$variables["file"] = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->mail . "/" . strtolower($variables["slug"]) . ".php";
			
		// if
		} else {
		
			return false;
			
		}
				
		// Translated the subject
		if( isset($variables["subject"]) ){
		
			$variables["subject"] = $language->translate( $variables["subject"] );
		
		// Used the assigned
		} else {
		
			$variables["subject"] = $language->translate( $this->private->mail[ $variables["slug"] ]["subject"] );
		
		// if
		}
		
		// Check if thre's a default template
		if( !isset($variables["template"]) ){
		
			$variables["template"] = $this->private->mail[ $variables["slug"] ]["template"];
		
		// if
		}
		
		// Check if we're loading a template
		if( $variables["template"] != "" ){
		
			// Make sure the template exists
			if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->is_theme() . "/" . $this->private->directory->templates . "/" . $this->private->directory->mail . "/" . strtolower($variables["template"]) . ".php")){
		
				// Here we're going to output buffer the php file
				ob_start();
				
				// Include the stuffs
				include $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->is_theme() . "/" . $this->private->directory->templates . "/" . $this->private->directory->mail . "/" . strtolower($variables["template"]) . ".php";
				
				// Take the contents from the php files		
				$variables["template"] = ob_get_contents();
				
				// stop the buffer
				ob_end_clean();			
				
			// The template file is missing
			} else {
			
				$variables["template"] = "";
			
			// if
			}
		
		// if
		}
		
		// Here we're going to output buffer the php file mail body
		ob_start();
		
		// Include the stuffs
		include $variables["file"];
		
		// Take the contents from the php files		
		$variables["body"] = ob_get_contents();
		
		// stop the buffer
		ob_end_clean();
		
		// Check if there's a custom file for this mail
		if( file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->is_theme(). "/" . $this->private->directory->templates . "/mail.php" ) ){
		
			// Add the custom class
			$bento->add(
						array(
							"type"	=>	"php",
							"file"	=>	$_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->themes . "/" . $this->is_theme() . "/" . $this->private->directory->templates . "/mail.php"
							)
						);
		
			// Add it
			$GLOBALS["scms_mail"] = new scms_mail();
			global $scms_mail;
			
			// Set up the method
			$method = $variables["slug"];
			
			// Check if there's a custom methof
			if( method_exists($scms_mail,$method) ){
			
				$scms_mail->$$method();
			
			// if
			}	
		
		// if
		}
		
		// Now combine the templateand body is need be
		if( $variables["template"] != "" ){
		
			$variables["body"] = str_replace("<!--scms:page-->",$variables["body"],$variables["template"]);
		
		// if
		}
		
		/*
		// Now we're going to run some things to add the css
		$bento->html = $variables["body"];
		
		// Now add the 
		$bento->assets("css");
		
		// Let's get the html
		ob_start();
		
		// Clean it up a bit
		echo "\r\n";
	
		// Output the css
		echo "<style>\r\n";
		//$bento->css( true );
		echo '<link rel="stylesheet" href="/bento/bento/css/manual/template.css" type="text/css">';
		echo "</style>";
	
		// Take the contents from the php files		
		$text = ob_get_contents();
		ob_end_clean();
	
		// Now replace the css
		$bento->html = str_replace("<!--bento:css-->",$text,$bento->html);
		
		// Now get all the css, and output it inline because some mail characters
		preg_match_all('@<link rel="stylesheet" href="([^>]+|)" type="text/css">@',$bento->html,$csss);
		
		// Here is the inline css
		$inline = "";
		
		// Check it out
		if( isset($csss[1]) ){
		
			// Let's get all the file to output inline
			foreach( $csss[1] as $i => $css ){
			
				// Try and get the contents
				if( !stristr($csss[1][$i],"http://") && !stristr($csss[1][$i],"https://") ){
				
					$inline = file_get_contents( "http://" . $_SERVER['HTTP_HOST'] . $csss[1][$i] );
			
				// Otherwise
				} else {
				
					$inline = file_get_contents( $csss[1][$i] );

				// if
				}
			
				// Now replace it
				$bento->html = str_replace($csss[0][$i],$inline,$bento->html);
				
			// foreach
			}
		
		// if
		}
		
		echo $bento->html;

		// We have everything sorted, great!
		$variables["body"] = $bento->html;

		*/
		
		// Now setup the boundaries
		$rand = md5(date('r', time()));	// Boundry to seperate potions of the message
		$boundary = "--" . $rand . "\n";	// Boundry to seperate potions of the message				
		$last = "--" . $rand . "--\n";	// Boundry to seperate potions of the message		

		// Set up the headers
		$headers = "From: " . $variables["from_name"] . " <" . $variables["from_email"] . ">\n";
		$headers .=	"MIME-Version: 1.0\n" .
					"Content-Type: multipart/mixed; boundary=\"" . $rand . "\"";

		// The plain text message						
		$message = "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
				   "Content-Transfer-Encoding: 7bit\n\n" . 
				   strip_tags($variables["body"]) . "\n";
		
		// The rich text message
		$message .= $boundary .
					"Content-Type: text/html; charset=\"iso-8859-1\"\n" . 
					"Content-Transfer-Encoding: 7bit\n\n" . 
					$variables["body"] . "\n";

		// Close off the message		
		$message .= $last;
		
		// Check it out
		$message = str_replace("<!--scms:mail:subject-->",$variables["subject"],$message);
					
		// Find all the replacement text			
		preg_match_all("@<!--scms:mail:variable:([^>]+|)-->@",$message,$vars);
		
		// Loop through the variables
		foreach( $vars[1] as $var ){
		
			// Make sure we check for it
			$found = false;
			
			// Make suer there's no problem
			if( isset($variables["variables"]) ){
				
				//Loop though the forms
				foreach( $variables["variables"] as $field => $value ){
					
					// Check if we have a winner
					if( $var == $field ){
					
						// Replace it
						$message = str_replace("<!--scms:mail:variable:" . $var . "-->",htmlentities($value),$message);
						
					}
						
				// foreach
				}
				
			// if
			}
		
			//Loop though the forms
			foreach( $_POST as $field => $value ){

				// decrypt it - decipher it
				$field = $form->decrypt($field,true);
				$field = str_replace("|",".", $field);
				
				// Check if we have a winner
				if( $var == $field ){
				
					// Replace it
					$message = str_replace("<!--scms:mail:variable:" . $var . "-->",$value,$message);
					
				}
					
			// foreach
			}
			
			// Check it
			if( !$found ){
			
				$message = str_replace("<!--scms:mail:variable:" . $var . "-->",$var,$message);
			
			}
			
		// for
		}
			
		// Send out the mail		
		$response = mail($variables["to"],$variables["subject"], $message, $headers); 
		
		// Return ut
		return $response;
		
	// method
	}
	
	/*
	@method: logged_in( $type="account",$redirect=false )
	@description: Check if the user is logged in
	@params:
	@shortcode:  
	@return:
	*/
	public function logged_in( $type="account",$redirect=false ){
	
		if( $type == "account" ){
		
			$logged_in = (bool)isset($_SESSION["bento"]["scms"]["account"]["id"]);
			
		} else if( $type == "facebook" ){
		
			$logged_in = (bool)(isset($_SESSION["bento"]["scms"]["account"]["facebook"]) && count($_SESSION["bento"]["scms"]["account"]["facebook"]) == 3 && $_SESSION["bento"]["scms"]["account"]["facebook"]["id"] != 0 );
		
		} else if( $type == "twitter" ){
		
			$logged_in = (bool)(isset($_SESSION["bento"]["scms"]["account"]["twitter"]) && count($_SESSION["bento"]["scms"]["account"]["twitter"]) == 3 && $_SESSION["bento"]["scms"]["account"]["twitter"]["id"] != 0 );
		
		// if
		}
	
		// Check if we're cool
		if( !$logged_in ){
		
			// Redirect if told to do so
			if( $redirect ){
			
				$this->redirect(301,"http://" . $_SERVER['HTTP_HOST'] . "/");
			
			// if
			}				
			
			// We're not cool
			return false;
			
		// if
		} else {
		
			return true;
			
		// if
		}
	
	// method
	}

	/*
	@method: account_id()
	@description: Returns the account id if there is one (if logged in)
	@params:
	@shortcode:  
	@return:
	*/
	public function account_id(){
		
		// Check if logged in
		if( self::logged_in() ){

			return $_SESSION["bento"]["scms"]["account"]["id"];
			
		} else {
		
			return 1;
			
		}

	// method
	}		

	/*
	@method: name( $echo=true )
	@description: Returns the account name (first+last) if there is one (if logged in)
	@params:
	@shortcode:  
	@return:
	*/
	public function name( $echo=true ){ return $this->account_name( $echo ); }
	public function account_name( $echo=false ){
		
		// Check if logged in
		if( $this->logged_in() ){

			$tmp_return = $_SESSION["bento"]["scms"]["account"]["name"];
			
		} else {
		
			$tmp_return = false;
			
		}
		
		// Return it
		return $echo ? print $tmp_return : $tmp_return;

	// method
	}

	/*
	@method: login( $response=true )
	@description: Logs an account in (account record must be open)
	@params:
	@shortcode:  
	@return:
	*/
	public function login( $response=true ){
	
		global $db,$form;
	
		// Check if acceptable
		if( $db->recordcount("account") != 0 || $db->select(
														array(
															"table"	=>	"account.email=" . $form->post("account.email") . " and account.password=" . $form->encrypt( $form->post("account.password") ),
															"join"	=>	"account_permission_x,account_mail_x"
															) 
														)
													){
		
			// Here is it
			$_SESSION["bento"]["scms"]["account"]["id"] = $db->record("account.id");
			$_SESSION["bento"]["scms"]["account"]["facebook"]["id"] = $db->record("account.facebook_id");
			$_SESSION["bento"]["scms"]["account"]["facebook"]["token"] = $db->record("account.facebook_token");
			$_SESSION["bento"]["scms"]["account"]["facebook"]["secret"] = $db->record("account.facebook_secret");
			$_SESSION["bento"]["scms"]["account"]["twitter"]["id"] = $db->record("account.twitter_id");
			$_SESSION["bento"]["scms"]["account"]["twitter"]["token"] = $db->record("account.twitter_token");
			$_SESSION["bento"]["scms"]["account"]["twitter"]["secret"] = $db->record("account.twitter_secret");
			$_SESSION["bento"]["scms"]["account"]["name"] = $db->record("account.name_first") . " " . $db->record("account.name_last");
			$_SESSION["bento"]["scms"]["account"]["email"] = $db->record("account.email");
			$_SESSION["bento"]["scms"]["account"]["extension"] = $db->record("account.extension");
			$_SESSION["bento"]["scms"]["account"]["confirm"] = $db->record("account.confirm");
			$_SESSION["bento"]["scms"]["account"]["logged_out"] = false;
			$_SESSION["bento"]["scms"]["account"]["permission"] = $db->recordset("account_permission_x.permission_id");
			$_SESSION["bento"]["scms"]["account"]["mail"] = $db->recordset("account_mail_x.mail_id");
			$_SESSION["bento"]["scms"]["account"]["timezone"] = $db->record("account.zone");
			
			// This isn't a form
			if( $response ){
			
				// respond to javascript
				$form->response(
								array(
									"response"	=>	true,
									"message"	=>	"Logged in successfully.",
									"variables"	=>	$_SESSION["bento"]["scms"]["account"]
									)
								);
			
			// if
			}
			
		// no good			
		} else {

			// here we go
			unset($_SESSION["bento"]["scms"]["account"]["id"]);
			
			// respond to javascript
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The email or password supplied is incorrect."
								)
							);
				
		// if
		}
	
	// method
	}

	/*
	@method: logout( $response=true )
	@description: Logs an account out
	@params:
	@shortcode:  
	@return:
	*/
	public function logout( $response=true ){
	
		global $form;

		// here we go
		unset($_SESSION["bento"]["scms"]["account"]);
		
		// Check it
		if( $response ){
			
			// Here is the response
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"Logged out."
								)
							);
		
		// if
		}
		
		// respond to javascript
		return true;
	
	// method
	}

	/*
	@method: feed( $name )
	@description: This will generate a feed from a form
	@params:
	@shortcode:  
	@return:
	*/
	public function feed( $slug, $variables=array() ){
		
		global $scms_feed_core;

		// Now get the slug passed over
		$this->method = $slug;
		
		// Now get the html from the feed
		$scms_feed_core->feed( true, false, $variables );

	// method
	}

	/*
	@method: feed_type()
	@description: Checks what type of feed (push, pull) we use
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_type( $type="" ){
		
		// Check if we're doing a boolean check or not
		if( $type != "" ){
			
			return $this->public->feed_type == $type;
			
		} else {
		
			return $this->public->feed_type;	
			
		// if
		}
		
	// method
	}

	/*
	@method: feed_form()
	@description: Creates a feed form for submitting for new feeds
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_form(){
		
		// Include the other classes
		global $bento,$form;

		// This is the feed form
		if( $this->has_feed() ){ 
				
			// Check what type of output we're looking for here
			if( $this->is_mode("app")  && $this->public->app->mode == "data" ){
				
				// Remove the core librarys (native on the apps)
				$bento->remove_file(
									array(
										"type"	=>	"js",
										"place"	=>	0	
									)
								);
	
				// Remove the more librarys (native on the apps)
				$bento->remove_file(
									array(
										"type"	=>	"js",
										"place"	=>	0	
									)
								);
			
				// Add a new variables
				$this->public->app->feed_form = str_replace("\r\n","",$html);
			
				// Add the html to the file
				$bento->add_action("bento.scms.app.load();");
				
			// if
			}

			// Start the output buffer
			ob_start();
		
			// This will look for new information
			$form->open(
					array(
							"id"	=>	$this->public->feed_form,
							"retrieve"	=>	$this->feed_type(),
							"handler"	=>	"scms_feed_core->feed",
							"javascript"	=>	array(
													"oncomplete"	=>	"bento.scms.feed.check();"
													)						
						)
					);
	
			// The page we're on
			$form->hidden(
					array(
						"name"	=>	"scms_feed_plugin",
						"value"	=>	$this->is_plugin()
					)
			);
			
			// The page we're on
			$form->hidden(
					array(
						"name"	=>	"scms_feed_theme",
						"value"	=>	$this->is_theme()
					)
			);
					
			// The page we're on
			$form->hidden(
					array(
						"name"	=>	"scms_feed_slug",
						"value"	=>	$this->is_slug()
					)
			);
			
			// The page we're on
			$form->hidden(
					array(
						"name"	=>	"scms_feed_time",
						"value"	=>	$this->public->feed_time
					)
			);
	
			// The page we're on
			$form->hidden(
					array(
						"id"	=>	"scms_feed_variables",
						"name"	=>	"scms_feed_variables",
						"value"	=>	htmlentities(json_encode($this->private->feed_variables))
					)
			);
					
			// Close it up
			$form->close();
	
			// This will look for new information
			$form->open(
					array(
							"id"	=>	$this->public->notification_form,
							"handler"	=>	"handler->notifications"						
						)
					);
	
			// The page we're on
			$form->hidden(
					array(
						"id"	=>	"scms_feed_notifications",
						"name"	=>	"scms_feed_notifications",
						"value"	=>	$this->notifications()
					)
			);
					
			// Close it up
			$form->close();
		
			// Take the contents from the php files		
			$html = ob_get_contents();
			ob_end_clean();
			
			// Return it
			$bento->html = str_replace("</body>", $html . "</body>",$bento->html);
			
		// if
		}
	
	// method
	}

	/*
	@method: feed_html( $feed, $method=NULL )
	@description: Get's the html for a feed
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_html( $feed ){
	
		global $bento;
		
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		$scms = $this;
		
		// This is the feed file
		$f = false;
	
		// This is the method we're loading
		if( !isset($feed["feed"]) ){
		
			$method = $this->method;
		
		// Check it out
		} else {
		
			$method = $feed["feed"];
		
		// if
		}

		// Set the base directory
		if( 
			!$this->is_plugin("scms") && 
				(
					file_exists( $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->feeds . "/" . $this->is_mode() . "/" . $method . ".php" ) ||
					file_exists( $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->feeds . "/" . $this->public->default_mode . "/" . $method . ".php" )
				)
			){
			
			// Check where we are
			if( file_exists( $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->feeds . "/" . $this->is_mode() . "/" . $method . ".php" ) ){
			
				$f = $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->feeds . "/" . $this->is_mode() . "/" . $method . ".php";
			
			// if
			} else if( file_exists( $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->feeds . "/" . $this->public->default_mode . "/" . $method . ".php" ) ){
			
				$f = $this->private->directory->plugins . "/" . $this->is_plugin() . "/" . $this->private->directory->feeds . "/" . $this->public->default_mode . "/" . $method . ".php";
			
			// if
			}
			
		// Check for plugin feeds
		}
		
		// Default to the root directory
		if ( 
			!$f && 
				(
					file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->feeds. "/" . $this->is_mode() . "/" . $method . ".php" ) ||
					file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->feeds . "/" . $this->public->default_mode. "/" . $method . ".php")
				)
			){
			
			// Check where we are
			if( !$this->is_mode("web") && file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->feeds . "/" . $this->is_mode() . "/" . $method . ".php" ) ){
			
				$f = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->feeds . "/" . $this->is_mode() . "/" . $method . ".php";
			
			// if
			} else if( file_exists($this->private->directory->feeds . "/" . $this->public->default_mode. "/" . $method . ".php") ){
			
				$f = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory->feeds . "/" . $this->public->default_mode. "/" . $method . ".php";
				
			// if
			}
	
		// if
		}
	
		// Make sure the feed html exists
		if( file_exists($f) ){
				
			// Check this out
			ob_start();
			
			// include the feed file
			include $f;
			
			// Take the contents from the php files		
			$html = ob_get_clean();
				
		// There's nothing left
		} else {
			
			return false;
			
		// if
		}
	
		// Check it
		$html = $bento->replace_shortcodes( $html );
	
		// Return the html
		return array(
					"feed"	=>	$method,
					"html"	=>	$html
					);
		
	// method
	}

	/*
	@method: feed_combine( $vars )
	@description: Will comine html and feed data into one (for inline feeds)
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_combine( $options, $internal=false ){
		
		global $bento,$form;
		
		// Holds loaded html, so we don't load it twice
		$log = array("feeds"	=> array(),"html"	=>	array());
		
		// Check if it's internal
		if( !$internal ){
			
			// We'll switch this back
			$revert = false;
			
			// Check if it's a single array,
			if( array_keys($options) !== range(0, count($options) - 1) ){
				
				// Revert this
				$revert = true;
				
				// Turn it into an array
				$tmp = $options;
				
				// Rid of this.
				unset($options);
			
				// Set this up
				$feeds["feeds"][0] = $tmp;
				
			// if
			} else {
				
				// Set this up
				$feeds = array("feeds"	=> $options);
				
			// here it is	
			}
			
			// Now merge the two
			foreach( $feeds["feeds"] as $feed ){
				
				// We only need to get the html once
				if( !in_array($feed["feed"],$log["feeds"]) ){
			
					// Get the html
					$log["html"][] = $this->feed_html( $feed );	
					
					// Log it so we don't do it again
					$log["feeds"][] = $feed["feed"];						
				
				// if
				}
				
			// foreach
			}
			
			// Set the html
			$feeds["html"] = $log["html"];
					
		// if
		} else {
			
			$feeds = $options;
			
		// if
		}
		
		// Don't need this anymore
		unset($log);

		// Loop through it all
		foreach( $feeds["feeds"] as $feed ){
			
			// Make sure it's properly formatted
			if( isset($feed["feed"]) ){
			
				// Loop through the htmls
				foreach( $feeds["html"] as $html ){
					
					// Check if this is the html
					if( $html["feed"] == $feed["feed"] ){
		
						// Find all the replacement text	
						preg_match_all("@<!--scms:variable:([^>]+|)-->@",$html["html"],$tmp);
						
						// Loop through it
						foreach( $tmp[1] as $variable ){
						
							// Make sure we have a translation
							if( isset($feed[ $variable ]) ){
				
								$tmp_html = $feed[ $variable ];	
							
							// if
							} else {
							
								$tmp_html = "";
							
							}
															
							// Now assing the new goodies
							$html["html"] = str_replace("<!--scms:variable:" . $variable . "-->",$tmp_html,$html["html"]);
				
						// if html feed and feed feed match
						}
						
						// Loop through all the plugins to replace short code
						$html["html"] = $bento->replace_shortcodes( $html["html"] );
						
						// If it's internal
						if( $internal ){
			
							// Add the html
							return $html["html"];
							
						// Otherwise
						} else {
							
							// If reverting to one record
							if( $revert ){
							
								// Set that it's combined
								$feed["combined"] = $html["html"];
								
							// if
							}
							
							// Return it
							return $feed;
							
						// if
						}
				
					// if
					}
			
				// foreach
				}
			
			// if
			}
			
		// if
		}
		
		// Return it
		return $options;

	// method
	}

	/*
	@method: feed_search()
	@description: Checks if this is a manual search
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_search(){

		// Set what we searched for
		return isset( $this->private->feed_new ) ? $this->private->feed_new : false;

	// method
	}

	/*
	@method: feed_set( $plugin )
	@description: Returns a set from memory
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_set( $plugin, $set=NULL ){

		// Set what we searched for
		if( is_null($set) && isset( $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ] ) ){
		
			// Set it
			return $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ];

		// Set what we searched for
		} else if( !is_null($set) && isset( $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ][ $set ] ) ){
		
			// Set it
			return $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ][ $set ];
	
		} else {
		
			return array();
			
		// if
		}

	// method
	}

	/*
	@method: feed_unset( $plugin )
	@description: Unsets a set of feeds in memory
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_unset( $plugin, $set ){

		// Check if we're clearing everything
		unset($_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ][ $set ]);

	// method
	}

	/*
	@method: feed_set( $plugin )
	@description: Returns a set from memory
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_clear( $plugin=false ){

		// Check if we're clearing everything
		if( !$plugin ){

			$_SESSION["bento"]["scms"]["feeds"]["logs"] = array("html"	=>	array(),"data"	=>	array(),"combined"	=>	array());
			$_SESSION["bento"]["scms"]["feeds"]["search"] = array();

		// Set what we searched for
		} else if( isset( $_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ] ) ){
		
			// Set it
			$_SESSION["bento"]["scms"]["feeds"]["logs"]["data"][ $plugin ] = array();
			
		// if
		}

	// method
	}

	/*
	@method: feed_variables( $vars )
	@description: This will set the variables for the feed
	@params:
	@shortcode:  
	@return:
	*/
	public function feed_variables( $vars ){
	
		global $bento;

		// Set the feed for variables
		$this->private->feed_variables = (object)$vars;

	// method
	}

	/*
	@method: add_variable( $variables )
	@description: This will add a new variables for the feed
	@params:
	@shortcode:  
	@return:
	*/
	public function add_variable( $variables ){
	
		global $bento;

		// Make sure it's been defined
		if( !isset($this->private->feed_variables) ){ $this->private->feed_variables = array(); }

		// Set the feed for variables
		$this->private->feed_variables = array_merge($this->private->feed_variables,$variables);

	// method
	}

	/*
	@method: get_variable( $key )
	@description: This will get a variable from the feed
	@params:
	@shortcode:  
	@return:
	*/
	public function get_variable( $key ){
	
		global $form;
		
		if( $form->post("scms_feed_variables") ){

			// Get the variables
			$variables = json_decode(html_entity_decode($form->post("scms_feed_variables")));
	
		} else {
		
			$variables = (object)$this->private->feed_variables;
			
		}
	
		// NOW output the one
		if( isset($variables->{ $key }) ){
		
			return $variables->{ $key };
		
		// Otherwise
		} else {
		
			return false;
			
		// if
		}
	
	// method
	}

	/*
	@method: profile( $account_id=NULL, $extension=NULL )
	@description: Return a profile picture url
	@params:
	@shortcode:  
	@return:
	*/
	public function profile( $account_id=NULL, $extension=NULL ){
	
		// Something to send bacl
		$tmp = "";
	
		// Check thi sout
		if( is_null($account_id) || is_null($extension) ){
	
			// There you go
			if( $this->logged_in() ){
			
				$account_id = $this->account_id();
				$extension = $_SESSION["bento"]["scms"]["account"]["extension"];
			
			} else {
			
				$account_id = "0";
				$extension = "png";
			
			// if
			}
		
		// if		
		}
	
		// Get the image
		$profile = "/images/profile/" . $account_id . "." . $extension;

		// Make sure the image exists
		if( !file_exists( $_SERVER['DOCUMENT_ROOT'] . $profile ) ){

			$profile = "/images/profile/0.png";
			
		}

		// Check for the image or the source
		return $this->http() . $this->is_domain() . $profile;
	
	// method
	}

	/*
	@method: profile_image( $account_id=NULL, $extension=NULL )
	@description: Return a profile picture image tage
	@params:
	@shortcode:  
	@return:
	*/
	public function profile_image( $account_id=NULL, $extension=NULL ){
	
		return "<img src='" . $this->profile( $account_id, $extension ) . "' >";
	
	// method
	}
	
	/*
	@method: timestamp( $sTime=NULL, $echo = true )
	@description: Creates a timestamp
	@params:
	@shortcode:  
	@return:
	*/
	public function timestamp( $time=NULL, $echo = true ){
	
		// There you go
		$time = ( is_null($time) ) ? time() : $time;

		// The string
		$diff = time() - $time;
		$day_diff = floor($diff / 86400);
		
		// Less than a day
		if ($day_diff < 1 ){
			
			if( $diff < 86400 ){ $text = floor( $diff / 3600 ) . " " . $this->public->timestamp->hours_ago; }
			if( $diff < 7200 ){ $text = "1 " . $this->public->timestamp->hour_ago; }
			if( $diff < 3600 ){ $text = floor( $diff / 60 ) . " " . $this->public->timestamp->minutes_ago; }
			if( $diff < 120 ){ $text = "1 " . $this->public->timestamp->minute_ago; }
			if( $diff < 60 ){ $text = $this->public->timestamp->seconds_ago; }
			if( $diff < 1 ){ $text = $this->public->timestamp->just_now; }
			
		// More than a day ago
		} else {
					
			if( $day_diff > 59 ){ $text = $this->public->timestamp->months_ago;; }
			if( $day_diff < 60 ){ $text = ceil( $day_diff / 7 ) . " " . $this->public->timestamp->weeks_ago; }
			if( $day_diff < 7 ){ $text = $day_diff . " " . $this->public->timestamp->days_ago; }
			if( $day_diff == 1){ $text = $this->public->timestamp->yesterday; }
		
		// if
		}

		// Make sure there's a recordset
		$tmp = "<span class=\"scms_timestamp\" alt=\"" . $time . "\">" . $text . "</span>";
		
		// here's the output
		return $echo ? print $tmp : $tmp;
		
	// method
	}

	/*
	@method: add_action( $action )
	@description: Adds a javascript action to fire on page load
	@params:
	@shortcode:  
	@return:
	*/
	public function add_action( $action ){
	
		$this->public->actions[] = $action;
	
	// method
	}

	/*
	@method: notifications()
	@description: Return number of notifications for the account
	@params:
	@shortcode:  
	@return:
	*/
	public function notifications(){

		// Set this up
		return (int)count($this->public->notification);

	// method
	}

	/*
	@method: notify()
	@description: Adds notification records for an account or accounts
	@params:
	@shortcode:  
	@return:
	*/
	public function notify( $options ){
		
		global $db;

		// Set this up
		if( !isset($options["account_id"]) ){ return NULL; }
		if( !isset($options["session_id"]) ){ session_id(); }
		if( !isset($options["parent_id"]) ){ $options["parent_id"] = 1; }
		if( !isset($options["plugin"]) ){ $options["plugin"] = "scms"; }
		if( !isset($options["message"]) ){ $options["message"] = ""; }
		if( !isset($options["url"]) ){ $options["url"] = ""; }
		
		// Add a push event
		$this->add_event( $options );
		
		// Turn this into a string
		if( is_string($options["account_id"]) ){ $options["account_id"] = array( $options["account_id"] ); }
		
		// Now loop through the accounts
		foreach( $options["account_id"] as $account_id ){
			
			// We don't notify outselves
			if( $account_id != 1 && $account_id != $this->account_id() ){
				
				// Inser the notification
				$db->insert(
							array(
									"table"	=>	"notification",
									"values"	=>	array( 
														"account_id"	=>	$account_id,
														"parent_id"	=>	$options["parent_id"],
														"time"	=>	time(),
														"plugin"	=>	$options["plugin"],
														"message"	=>	$options["message"],
														"url"	=>	$options["url"]
														)
													)
												);
			
			// if
			}
			
		// foreach
		}

	// method
	}

	/*
	@method: unnotify()
	@description: Remove any notifications
	@params:
	@shortcode:  
	@return:
	*/
	public function unnotify(){
		
		global $db,$form;
		
		// Check if we need to update the notify table
		if( $this->logged_in() && $form->post("scms_feed_notifications") == 0 ){
		
			// Let's update the notification table
			$db->update(
						array(
							"table"	=>	"notification",
							"values"	=>	array(
												"state"	=>	0							
												),
							"criteria"	=>	"account_id=" . $this->account_id()
							)
						);
		
		// if
		}
						
		return true;

	// method
	}	
	
	/*
	@method: add_event( $action )
	@description: Adds an event to the database for a session for push events
	@params:
	@shortcode:  
	@return:
	*/
	public function add_event( $options=array() ){
		
		global $db,$form;
		
		// Make sure we have a push form
		if( $this->feed_type("push") ){

			// Clear up stuff we don't need
			if( !isset($options["account_id"]) ){ unset($options["account_id"]); }
			if( !isset($options["message"]) ){ unset($options["message"]); }
			if( !isset($options["url"]) ){ unset($options["url"]); }
		
			// Check what's set
			if( !isset($options["session_id"]) ){ $options["session_id"] = session_id(); }
			if( !isset($options["parent_id"]) ){ $options["parent_id"] = 0; }
			if( !isset($options["plugin"]) ){ $options["plugin"] = "scms"; }
			if( !isset($options["method"]) ){ return false; }
			
			// Remove the events first
			// $this->delete_event( $options );
		
			// Insert the event into the datbase
			$db->insert(
						array(
							"table"	=>	"event",
							"values"	=>	$options
							)
						);
	
		// if
		}
	
	// method
	}
	
	/*
	@method: events( $action )
	@description: Checks and returns events
	@params:
	@shortcode:  
	@return:
	*/
	public function events( $options=array() ){

		global $db;

		$tmp = "";
		
		// Loop through the options
		foreach( $options as $field => $value ){
			
			$tmp .= "event." . $field . "=" . $value . " and ";
			
		// foreach
		}
		
		// Remove the events first
		$db->select(
					array(
						"table"	=>	substr($tmp,0,-5)
						)
					);
					
		// Set if there's an event			
		if( (bool)$db->recordcount("event") ){
			
			$tmp_return = $db->recordset("event");
			
		} else {
		
			$tmp_return = array();	
			
		// if
		}
		
		// Clear this up
		$db->clear("event");
		
		// Return the status
		return $tmp_return;

	// method
	}

	
	/*
	@method: has_event( $action )
	@description: Checks if we have an event
	@params:
	@shortcode:  
	@return:
	*/
	public function has_event( $options=array() ){
		
		// Return the status
		return (count($this->events($options)) > 0);

	// method
	}
	
	/*
	@method: remove_event( $action )
	@description: Removes from the database for a session for push events
	@params:
	@shortcode:  
	@return:
	*/
	public function delete_event( $options=array() ){
		
		global $db,$form;
	
		// Check what's set
		if( !isset($options["session_id"]) ){ $options["session_id"] = session_id(); }
		if( !isset($options["plugin"]) ){ $options["plugin"] = "scms"; }
		
		// This will add the file system reference that there's an update
		$form->delete_event( $options["session_id"] );
				
		// Remove the events first
		$db->delete(
					array(
						"table"	=>	"event",
						"criteria"	=>	$options
						)
					);

	// method
	}

	/*
	@method: fire_events( $action )
	@description: fire events for a number of sessions
	@params:
	@shortcode:  
	@return:
	*/
	public function fire_events( $options ){
		
		global $db,$form,$file;
	
		// Check what's set
		if( !isset($options["parent_id"]) ){ $options["parent_id"] = 0; }
		if( !isset($options["plugin"]) ){ $options["plugin"] = "scms"; }
		
		$tmp = "";
		
		// Loop through the options
		foreach( $options as $field => $value ){
			
			$tmp .= "event." . $field . "=" . $value . " and ";
			
		// foreach
		}
		
		$file->write( "debug.log", substr($tmp,0,-5) );
		
		// Remove the events first
		$db->select(
					array(
						"table"	=>	substr($tmp,0,-5)
						)
					);			
					
		// Now let's fire an event
		foreach( $db->recordset("event") as $event ){
			
			// Now add a new event for the sessions logged
			$form->add_event( $event["session_id"] );
			
		// foreach
		}
		
		$db->clear("event");

	// method
	}

	/*
	@method: short_id($in, $to_num = false, $pad_up = false, $passKey = null)
	@description: Shortens an id to alphanumberic
	@params:
	@shortcode:  
	@return:
	*/
	public function short_id($in, $to_num = false, $pad_up = false, $passKey = null){ return $this->s($in, $to_num, $pad_up, $passKey); }
	public function short($in, $to_num = false, $pad_up = false, $passKey = null){  return $this->s($in, $to_num, $pad_up, $passKey); }
	public function s_id($in, $to_num = false, $pad_up = false, $passKey = null){  return $this->s($in, $to_num, $pad_up, $passKey); }
	public function s($in, $to_num = false, $pad_up = false, $passKey = null){

	  $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	  if ($passKey !== null) {
		// Although this function's purpose is to just make the
		// ID short - and not so much secure,
		// with this patch by Simon Franz (http://blog.snaky.org/)
		// you can optionally supply a password to make it harder
		// to calculate the corresponding numeric ID
	 
		for ($n = 0; $n<strlen($index); $n++) {
		  $i[] = substr( $index,$n ,1);
		}
	 
		$passhash = hash('sha256',$passKey);
		$passhash = (strlen($passhash) < strlen($index))
		  ? hash('sha512',$passKey)
		  : $passhash;
	 
		for ($n=0; $n < strlen($index); $n++) {
		  $p[] =  substr($passhash, $n ,1);
		}
	 
		array_multisort($p,  SORT_DESC, $i);
		$index = implode($i);
	  }
	 
	  $base  = strlen($index);
	 
	  if ($to_num) {
		// Digital number  <<--  alphabet letter code
		$in  = strrev($in);
		$out = 0;
		$len = strlen($in) - 1;
		for ($t = 0; $t <= $len; $t++) {
		  $bcpow = bcpow($base, $len - $t);
		  $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
		}
	 
		if (is_numeric($pad_up)) {
		  $pad_up--;
		  if ($pad_up > 0) {
			$out -= pow($base, $pad_up);
		  }
		}
		$out = sprintf('%F', $out);
		$out = substr($out, 0, strpos($out, '.'));
	  } else {
		// Digital number  -->>  alphabet letter code
		if (is_numeric($pad_up)) {
		  $pad_up--;
		  if ($pad_up > 0) {
			$in += pow($base, $pad_up);
		  }
		}
	 
		$out = "";
		for ($t = floor(log($in, $base)); $t >= 0; $t--) {
		  $bcp = bcpow($base, $t);
		  $a   = floor($in / $bcp) % $base;
		  $out = $out . substr($index, $a, 1);
		  $in  = $in - ($a * $bcp);
		}
		$out = strrev($out); // reverse
	  }
	 
	  return $out;

	// method
	}

	/*
	@method: token()
	@description: Generates a token (for registrations and forgots)
	@params:
	@shortcode:  
	@return:
	*/
	public function token(){

		return $this->s(time());
		
	// method
	}

	/*
	@method: timezones()
	@description:
	@params:
	@shortcode:  
	@return:
	*/
	public function timezones(){
	
		// Get the timezones
		$timezones = DateTimeZone::listAbbreviations();
		
		$cities = array();
		foreach( $timezones as $key => $zones )
		{
			foreach( $zones as $id => $zone )
			{
				// Only get timezones explicitely not part of "Others"
				if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'] ) && $zone['timezone_id']) {
					$tmp = array();
					$tmp = explode("/",$zone['timezone_id']);
					$cities[$zone['timezone_id']] = str_replace("_"," ",$tmp[1]);
				}
			}
		}
		
		// Only keep one city (the first and also most important) for each set of possibilities. 
		$cities = array_unique( $cities );
		
		// Sort by area/city name.
		asort( $cities );

		// Here you go		
		$timezones = array();
		
		// Loop through them
		foreach( $cities as $value => $text ){
		
			// Check this out
			$timezones[] = array(
								"value"	=>	$value,
								"text"	=>	$text 
								);
		
		// foreach
		}
		
		return $timezones;

	// method
	}

	/*
	@method: timezone( $set=false )
	@description: Sets default timezones for users
	@params:
	@shortcode:  
	@return:
	*/
	public function timezone( $set=false ){
		
		//session_destroy();
		
		//print_r( $_SESSION["bento"]["scms"] );die();
		
		// Make sure it's set or not
		if( $set ){
		
			// Check this out
			if( $this->logged_in() ){
		
				date_default_timezone_set($_SESSION["bento"]["scms"]["account"]["timezone"]);
				
			} else {
			
				date_default_timezone_set('America/Edmonton');
			
			}
		
		} else {
		
			return $_SESSION["bento"]["scms"]["account"]["timezone"];
		
		// if
		}
		
	// method
	}

	/*
	@method: trail()
	@description: generates a breadcrumb trail
	@params:
	@shortcode:
	@return: nothing
	*/
	public function trail($start=true,$id=0,$count=0){

		// Check if we're starting or ending
		if( $start ){

			// Setup the breadcrumb
			$this->private->breadcrumb = array();
			
			// The slug is this page
			$slug = $this->is_slug();

		// if
		} else {
		
			// Get the parent
			$slug = $this->get_parent( $id );
		
		// if
		}
        
		// Check if it's the home page
		if( $this->is_home() || $start ){
		
			// Check it out
			$this->private->breadcrumb[] = (object)array(
														"active"	=>	true,
														"anchor"	=>	$this->is_anchor(),
														"url"	=>	$this->url( array("slug"	=>	$this->is_slug() ),false ),
														"link"	=>	$this->link( array("slug"	=>	$this->is_slug() ),false ),
														"button"	=>	$this->button( array("slug"	=>	$this->is_slug() ),false )
													);
		
		// Check it
		} else if( !$start ){

			// Check it out
			$this->private->breadcrumb[] = (object)array(
														"active"	=>	false,
														"anchor"	=>	$this->get_anchor( $slug ),
														"url"	=>	$this->url( array("slug"	=>	$slug ),false ),
														"link"	=>	$this->link( array("slug"	=>	$slug ),false ),
														"button"	=>	$this->button( array("slug"	=>	$slug ),false )
													);
		
		// if
		}
		
		// Look for the next one
		if( !$this->is_home() && $slug ){
		
			// Check if we have a parent
			$parent_id = $this->parent_id( $slug );
		
			// Check if 
			if( (bool)$parent_id ){

 				$this->trail( false,$parent_id );
				
			// if
			}
				
		// if
		}
		
		// At the end we reverse it from left to right
		if( $start ){
		
			// Reorder the breadcrumb
			$this->private->breadcrumb = array_reverse($this->private->breadcrumb);

		// if
		}

	// method
	}

	/*
	@method: breadcrumb()
	@description: Outputs a breadcrumb trail
	@params:
	@shortcode: <!--scms:breadcrumb-->
	@return: echos the breadcrumb trail
	*/
	public function breadcrumb( $echo=true ){
			
		// Check if we're starting or ending
		if( $echo ){
		
			// Output the start of the breadcrumb
			echo '<ul class="breadcrumb">';
			
			// Loop through to create the links
			foreach( $this->private->breadcrumb as $crumb ){
				
				// Check if it's active or not
				if( $crumb->active ){
					
					echo "<li class=\"active\">" . $crumb->anchor . "</li>";
				
				// Otherwise
				} else {
					
					echo "<li>" . $crumb->link . "</li><span class=\"divider\">/</span>";
				
				// if
				}
				
			// foreach
			}

			echo '</ul>';

		// if
		} else {
			
			return $this->private->breadcrumb;
			
		// if
		}

	// method
	}

	/*
	@method: add_crumb()
	@description: Adds a crumb into the trail
	@params:
	@shortcode:
	@return:
	*/
	public function add_crumb( $options ){
		
		// Check if we've passed what we need
		if( !isset($options["url"]) && !isset($options["anchor"]) ){
			
			return false;
			
		// if
		}
		
		// Check it out
		foreach( array("link","button") as $type ){
			
			// Check it out
			if( !isset( $options[ $type ] ) ){
				
				$options[ $type ] = "<a href=\"" . $options["url"] . "\">" . $options["anchor"] . "</a>";
				
			// if	
			}
		
		// if
		}
				
		// Options
		$place = isset($options["place"]) ? $options["place"] : count($array);

		// This is the complete crumb we're inserting
		$insert = (object)array(
								"active"	=>	false,
								"anchor"	=>	$options["anchor"],
								"url"	=>	$options["url"],
								"link"	=>	$options["link"],
								"button"	=>	$options["button"]
								);
 
 		// Make sure we can do what we want
		if($place != (count($this->private->breadcrumb))) {
			
			// Check i tout
			$ta = $this->private->breadcrumb;
			
			// Loop 
			for($i = $place; $i < (count($this->private->breadcrumb)); $i++) {
			
				if(!isset($this->private->breadcrumb[$i])) {
					return false;
				}
			
				$tmp[$i+1] = $this->private->breadcrumb[$i];
				
				// destroy it
				unset($ta[$i]);
			
			// if
			}
			
			$ta[$place] = $insert;
			$this->private->breadcrumb = $ta + $tmp;
		
		// Check this out
		} else {
			
			$this->private->breadcrumb[$place] = $insert;
		
		// if
		}

		// sort the key
		ksort($this->private->breadcrumb);
		
		// Activate the last one
		$this->activate_crumb();
		
		// Return it		
		return true;
		
	// method	
	}

	/*
	@method: insert_crumb()
	@description: Inserts a crumb into the trail
	@params:
	@shortcode:
	@return:
	*/
	public function edit_crumb( $options ){

		// Check if we've passed what we need
		if( !isset($options["url"]) && !isset($options["anchor"]) ){
			
			return false;
			
		// if
		}
		
		// Check it out
		foreach( array("link","button") as $type ){
			
			// Check it out
			if( !isset( $options[ $type ] ) ){
				
				$options[ $type ] = "<a href=\"" . $options["url"] . "\">" . $options["anchor"] . "</a>";
				
			// if	
			}
		
		// if
		}
				
		// Options
		$place = isset($options["place"]) ? $options["place"] : count($array);

		// This is the complete crumb we're inserting
		$insert = (object)array(
								"active"	=>	false,
								"anchor"	=>	$options["anchor"],
								"url"	=>	$options["url"],
								"link"	=>	$options["link"],
								"button"	=>	$options["button"]
								);

		// Insert it
		$this->private->breadcrumb[ $place ] = $insert;
		
		return true;
		
	// method	
	}

	/*
	@method: delete_crumb()
	@description: Delete a crumb into the trail
	@params:
	@shortcode:
	@return:
	*/
	public function delete_crumb( $place ){

		// Check if we've passed what we need
		if( !isset( $place ) && isset($this->private->breadcrumb[ $place ]) ){
			
			return false;
			
		// if
		}
		
		// remove this
		unset($this->private->breadcrumb[ $place ]);
		
		// Reset the keys
		$this->private->breadcrumb = array_merge($this->private->breadcrumb,array());
		
		// Activate the last crumb
		$this->activate_crumb();
		
	// method	
	}

	/*
	@method: activate_crumb()
	@description: Activates the last piece of bread
	@params:
	@shortcode:
	@return:
	*/
	public function activate_crumb(){

		// Somethign to count
		$i = 1;
		
		// Loop through
		foreach( $this->private->breadcrumb as $j => $k ){
			
			// Guilty until proven innocent
			$this->private->breadcrumb[ $j ]->active = false;
			
			// Activate
			if( $i == count($this->private->breadcrumb) ){ $this->private->breadcrumb[ $j ]->active = true;}
			
			// add it up
			$i++;
			
		// foreach
		}
		
		return true;

	// method	
	}

	/*
	@method: sitemap()
	@description: Outputs a sitemap
	@params:
	@shortcode: <!--scms:sitemap-->
	@return: echos the breadcrumb trail
	*/
	public function sitemap( $parent_id=0 ){
	
		echo "<ul>\r\n";

		// Loop through the pages
		foreach( $this->private->page as $slug => $page ){
		
			// Check it out
			if( $page["parent_id"] == $parent_id ){

				// let's exclude some stuff from the menu
				if( $this->is_hidden( $slug ) ){
					
					// Get the link
					$link = $this->link(array("slug"	=>	$slug ),false);
					
					// Makre sure we have permissions
					if( $link ){
					
						echo "<li>\r\n";
						echo $link . "\r\n";
						
							// Now get the children
							$this->sitemap( $page["id"] );
						
						echo"</li>\r\n";
				
					// if
					}
				
				// if
				}
				
			// if
			}

		// foreach
		}	
		
		echo "</ul>";
		
	// method
	}

	/*
	@method: admin()
	@description: Generates the admin menu, breadcrumb and anything else we might need
	@params:
	@shortcode: <!--scms:sitemap-->
	@return: echos the breadcrumb trail
	*/
	public function admin(){

		global $db,$file,$bento;
	
		// Get the themes
		$themes = $file->read_directory("themes");

		// Loop through them
		foreach( $themes as $theme ){

			$this->private->themes[] = array(
											"value"	=>	str_replace(".php","",$theme)
											);

		// foreach
		}
		
		// Get the page templates
		foreach( array("web","mobile","app","facebook","kiosk","narrowcast","mail") as $type ){
		
			$templates = $file->read_directory( "themes/" . $this->private->theme . "/" . $this->private->directory->templates . "/" . $type . "/" );

			// Loop through them
			foreach( $templates as $template ){
	
				$this->private->admin_templates[$type][] = array(
														"value"	=>	str_replace(".php","",$template)
														);
	
			// foreach
			}
			
		// foreach
		}

		// Add the admin stylesheet
		$bento->add_js(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"admin"
						)
					);

		// Add the admin stylesheet
		$bento->add_css(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"admin"
						)
					);

		// Create the admin menu
		if( $this->is_slug("admin") ){
		
			$this->private->admin_menu = array(
											array(
												"name"	=>	"Pages",
												"page"	=>	"page_list",
												"menu"	=>	array(
																array(
																	"name"	=>	"Add New",
																	"page"	=>	"page_add"
																	),
																array(
																	"name"	=>	"List All Pages",
																	"page"	=>	"page_list"
																),
																array(
																	"name"	=>	"Edit " . $db->record("page.name"),
																	"page"	=>	"page_edit",
																	"hidden"	=>	true
																),
																array(
																	"name"	=>	"Delete " . $db->record("page.name"),
																	"page"	=>	"page_delete",
																	"hidden"	=>	true
																)
																
															)
																	
												),
											array(
												"name"	=>	"User Accounts",
												"page"	=>	"account_list",
												"menu"	=>	array(
																array(
																	"name"	=>	"Add New",
																	"page"	=>	"account_add"
																	),
																array(
																	"name"	=>	"List All Users",
																	"page"	=>	"account_list"
																),
																array(
																	"name"	=>	"Edit " . $db->record("account.email"),
																	"page"	=>	"account_edit",
																	"hidden"	=>	true
																),
																array(
																	"name"	=>	"Delete " . $db->record("account.email"),
																	"page"	=>	"account_delete",
																	"hidden"	=>	true
																)
															)
																	
												),
											array(
												"name"	=>	"Mail",
												"page"	=>	"mail_list",
												"menu"	=>	array(
																array(
																	"name"	=>	"Add New",
																	"page"	=>	"mail_add"
																	),
																array(
																	"name"	=>	"List All Mail",
																	"page"	=>	"mail_list"
																),
																array(
																	"name"	=>	"Edit " . $db->record("mail.name"),
																	"page"	=>	"mail_edit",
																	"hidden"	=>	true
																),
																array(
																	"name"	=>	"Delete " . $db->record("mail.name"),
																	"page"	=>	"mail_delete",
																	"hidden"	=>	true
																)
															)
																
												),
											array(
												"name"	=>	"Permissions",
												"page"	=>	"permission_list",
												"menu"	=>	array(
																array(
																	"name"	=>	"Add New",
																	"page"	=>	"permission_add"
																	),
																array(
																	"name"	=>	"List All Permissions",
																	"page"	=>	"permission_list"
																),
																array(
																	"name"	=>	"Edit " . $db->record("permission.name"),
																	"page"	=>	"permission_edit",
																	"hidden"	=>	true
																),
																array(
																	"name"	=>	"Delete " . $db->record("permission.name"),
																	"page"	=>	"permission_delete",
																	"hidden"	=>	true
																)
															)
																	
												),
											array(
												"name"	=>	"Setup",
												"page"	=>	"setup",
												"menu"	=>	array(
																array(
																	"name"	=>	"Setup",
																	"page"	=>	"setup"
																	),
																array(
																	"name"	=>	"Facebook",
																	"page"	=>	"facebook"
																),
																array(
																	"name"	=>	"Twitter",
																	"page"	=>	"twitter"
																)
															)
																	
												)
											);
						
			// Set this up for links					
			$name = "";
			$page = "";

			// Get the link
			foreach( $this->private->admin_menu as $menu ){
			
				// Check if we're on the root
				if( $menu["page"] == $this->q("page") ){
				
					$current = $menu["name"];
					break;
				
				// if
				}
			
				// Add the menu
				if( isset($menu["menu"]) ){
				
					foreach( $menu["menu"] as $sub ){
					
						if( $sub["page"] == $this->q("page") ){
						
							$name = $menu["name"];
							$page = $menu["page"];
							$current = $sub["name"];
							break;
						
						// if
						}
					
					// foreach
					}
					
				// if
				}
					
			// foreach
			}
			
		
			// Setup the breadcrumb
			$this->private->admin_breadcrumb = '<ul class="breadcrumb">';
			
			// Check if we're up one
			if( $name != "" ){
			
				// Add this to the bradcrumb
				$this->private->admin_breadcrumb .= '<li>' . 
								$this->link(
										array(
											"slug"	=>	"admin",
											"anchor"	=>	$name,
											"title"	=>	$name,
											"variables"	=>	array(
																"page"	=>	$page
											)
										),false
									)  . 							
							'<span class="divider">/</span>
						</li>';
						
			// if
			}

			// JEre it is
			$this->private->admin_breadcrumb .= '<li class="active">' . $current . '</li></ul>';

		//
		}

	// method
	}

	/*
	@method: authenticated( $ids )
	@description: Checks if a user is authenticated or not against permission ids
	@params:
	@shortcode:  
	@return:
	*/
	public function authenticated( $ids ){
	
		// Make sure this account has some permissions
		if( !isset($_SESSION["bento"]["scms"]["account"]["permission"]) ){ return false; }
		
		// Make sure everything is good
		if( !is_array($ids) ){ $ids = array($ids); }

		// No permissions, no problem
		if( count( $ids ) == 0 ){
		
			return true;
			
		// if
		}
	
		// Loop through all the emails 
		foreach( $ids as $id ){
	
			// check if the account has permissions and if the specific rule is in there
			if ( !in_array( $id, $_SESSION["bento"]["scms"]["account"]["permission"]) ){
			
				return false;
			
			// if
			}
	
		// foreach
		}
		
		return true;		
		
	// method
	}

	/*
	@method: set_permissions( $action )
	@description: sets permissions for an account
	@params:
	@shortcode:  
	@return:
	*/
	public function set_permissions( $options ){
		
		global $db;

		// Clear up stuff we don't need
		if( !isset($options["account_id"]) ){ return false; }
		if( !isset($options["permissions"]) ){ return false; }
		
		// Make it an array if it's not
		if( !is_array($options["permissions"]) ){ $options["permissions"] = array($options["permissions"]); }
	
		// If the account already exists kablamo it
		$db->delete(
					array(
						"table"	=>	"account_permission_x",
						"criteria"	=>	array(
											"account_id"	=>	$options["account_id"]
						)
					)
				);
				
		// Add an event for this account
		$this->fire_events(
							array(
								"method"	=>	"account",
								"parent_id"	=>	$options["account_id"]
								)
							);
	
		// Insert the event into the datbase
		foreach( $options["permissions"] as $permission_id ){

			// Add the permission
			$this->add_permission(
								array(
									"account_id"	=>	$options["account_id"],
									"permission_id"	=>	$permission_id 
								)
							);			
			
		// foreach
		}
					
		return true;
	
	// method
	}

	/*
	@method: add_permission( $action )
	@description: Adds a permission to an account
	@params:
	@shortcode:  
	@return:
	*/
	public function add_permission( $options ){
		
		global $db;

		// Clear up stuff we don't need
		if( !isset($options["account_id"]) ){ return false; }
		if( !isset($options["permission_id"]) ){ return false; }
	
		// Insert the event into the datbase
		$db->insert(
					array(
						"table"	=>	"account_permission_x",
						"values"	=>	$options
						)
					);
					
		return true;
	
	// method
	}

	/*
	@method: update_permissions $action )
	@description: Updates an accounts permissions on the fly
	@params:
	@shortcode:  
	@return:
	*/	
	public function update_permissions(){
	
		global $db;
	
		// Check if we're logged in
		if( $this->logged_in() ){
			
			// Check it out
			$db->select(
						array(
							"table"	=>	"account.id=" . $this->account_id(),
							"join"	=>	"account_permission_x,permission"
							)
						);
						
			// Update the permissions
			$this->login(false);

			// Add an event for permission changes
			$this->add_event(
							array(
								"parent_id"	=>	$this->account_id(),
								"method"	=>	"account"
							)
						);
			
			// Clear this up
			$db->clear("account,permission,account_permission_x");
		
		// if
		}

		return true;
		
	// if
	}
	/*
	@method: check_permissions $action )
	@description: Checks the permissions on page load or feed
	@params:
	@shortcode:  
	@return:
	*/	
	public function check_permissions( $slug="" ){
		
		// Kill it
		if( $slug == "" ){
			
			// if one isn't set, use the current page
			$slug = $this->is_slug();
			
		// if
		}

		// If there are no permissions on this page, then no problem
		if( $this->authenticated(1) || !isset($this->private->page[ $slug ]["permission"]) || count($this->private->page[ $slug ]["permission"]) == 0 ){
			
			// Good to go
			$authenticated = true;	
		
		//	Otherwise, let's assume they've broken in
		} else {
	
			// Temporary authentication
			$authenticated = false;	
	
			// Loop through each of them
			foreach( $this->private->page[ $slug ]["permission"] as $id ){
			
				// Here's an error
				if( $this->authenticated( $id ) ){
				
					// Set that we're good
					$authenticated = true;
					
					// Break the loop
					break;
				
				// if
				}
			
			// foreach
			}
			
		// if
		}

		// return it		
		return $authenticated;

	// method
	}

// class
}?>