<?php
/*
@class: bento
@description: Control class, deals with all plugins, installs, asset management (css/js), event handling, shortcode, compression, cron jobs, remote access, and logging
@params:
*/

// Allow this
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *"); 	
header("Access-Control-Allow-Headers: *");

// Start up bento
$bento = new bento();
$bento->__load();

// Class
class bento{

	// Set up the variables
	public $public;
	public $private;
	
	// Set up the styles
	public $style;
	
	// This is where the html goes
	public $html;
	
	// Somewhere to store event hooks
	var $events;
	
	// Somewhere to store shortcode handlers
	var $shortcodes;

	/*
	@method: __load()
	@description: This handles pretty much everything
	@params:
	@shortcode:  
	@return:
	*/
	public function __load(){
	
		// Set up the erros
		ini_set('error_reporting',E_STRICT); 
		ini_set('display_errors' , true );
		ini_set('memory_limit','2048M');
		error_reporting(6143);
		
		// Check if there's a trailing / on the document root
		if( $_SERVER['DOCUMENT_ROOT'][strlen($_SERVER['DOCUMENT_ROOT'])-1] == "/" ){ substr($_SERVER['DOCUMENT_ROOT'],0,-1); }

		// Some more things we need
		$this->config = $_SERVER['DOCUMENT_ROOT'] . "/bento/bento/config/bento.php";
		$this->cron = $_SERVER['DOCUMENT_ROOT'] . "/bento/bento/cron/bento.php";

		// Configuration - reads php protected json into a variable
		$variables = $this->configuration( $this->config );
	
		// Assign the vars for this main library
		$this->version = $variables->version;
		$this->license = $variables->license;
		$this->state = $variables->state;	
		$this->public = $variables->public;
		$this->private = $variables->private;	
		
		// These are the actions we're going to undertake at startup
		$this->public->js->action = array();
		
		// Clear it up
		unset($variables);

		// Start a session. We need one.
		if( isset( $_GET[ $this->private->session_id ] ) && $_GET[ $this->private->session_id ] != "" ){
	
			// Check if this is a new session
			session_id( $_GET[ $this->private->session_id ] );
			session_start();

		// Set it
		} else if( session_id() == "" ){
		
			session_start();	
			
		// if
		}
		
 		// Default timezone for the time being
		date_default_timezone_set( $this->private->timezone );
		
		// Before we do anything else, check if we're using cache control
		if( isset($_GET[ $this->private->cache->querystring ]) ){
		
			// Get the file contents
			$this->cache(
						array(
							"operation"	=>	"output",
							)
						);
			
		// if
		}
		
		// Check if we're installed yet
		if( $this->license == "" ){
	
			// Tell the program we need to do an install before the rest
			$this->state = "not installed";
			
		// if
		}
				
		// Load the javascript and css for bento
		foreach( array($this->public->assets->js,$this->public->assets->css ) as $type ){

			// This will add files from a directory
			$this->add_files(
							array(
								"type"	=>	$type,
								"directory"	=>	$_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory . "/" . $this->private->directory . "/" . $type . "/" . $this->private->auto		
							)
						);
			
		// foreach
		}
		
		// Load this when
		$this->add_event('bento','loaded','assets');

		// Setup the shortcode to replace the javascript and css
		$this->add_shortcode("<!--bento:javascript-->");
		$this->add_shortcode("<!--bento:css-->");
	
		// These are the assets
		$assets = array();
		
		// Normal errors when not html
		if( !isset($_GET["bento"]) ){
		
			// This will help wit graceful error checking
			// set_error_handler("errors");

		// if
		}
		
		// Add cookie library
		$this->add_php(
						array(
							"plugin"	=>	"bento",
							"name"	=> "cookie"
							)
						);
						
		// Check it
		$GLOBALS["cookie"] = new cookie();		

		// Loop through the files assets
		foreach( $this->private->plugins as $asset ){
		
			// Make sure it's not a directory
			if( $asset != "." && $asset != ".." ){
			
				// This is the directory
				$directory = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory . "/" . $asset . "/";
			
				// Check the file
				if( file_exists( $directory . $this->public->assets->php . "/" . $this->private->auto . "/" . $asset . ".php" ) ){
					
					// Get the file name
					$assets[ $asset ]["file"] = $asset . ".php";
					$assets[ $asset ]["path"] = $directory . $this->public->assets->php . "/" . $this->private->auto . "/" . $asset . ".php";
										
				// and index file
				} else if( file_exists( $directory . $this->public->assets->php . "/" . $this->private->auto . "/" . "index.php" ) ){
					
					// Get the file name
					$assets[ $asset ]["file"] = "index.php";
					$assets[ $asset ]["path"] = $directory . $this->public->assets->php . "/" . $this->private->auto . "/" . "index.php";
										
				// if						
				} 		
				
				// Assign the rest
				if( isset($assets[ $asset ]["file"]) ){
				
					$assets[ $asset ]["js"] = $directory . $this->public->assets->js;
					$assets[ $asset ]["css"] = $directory . $this->public->assets->css;		
					$assets[ $asset ]["config"] = $directory . $this->private->assets->config;
					$assets[ $asset ]["cron"] = $directory . $this->private->assets->cron;	
					$assets[ $asset ]["install"] = $directory . $this->private->assets->install . "/" . $this->private->assets->install;	

				// if
				}

			// if
			}
			
		// while
		}
					
		// Loop through it all
		foreach( $assets as $asset => $f ){
		
			// Include the library file
			$this->add_php(
							array(
								"file"	=> $f["path"]
								)
							);

			// Add this
			$GLOBALS[$asset] = new $asset($this);
			$GLOBALS[$asset]->file = $f["path"];
			$GLOBALS[$asset]->config = $f["config"] . "/" . $asset . ".php";
			$GLOBALS[$asset]->cron = $f["cron"] . "/" . $asset . ".php";
			
		// foreach
		}
		
		// Set that it's loaded
		$this->event($assets, 'construct');	
		
		// The last oneis loaded
		$this->fire_event("all","constructed"); 
					
		// Loop through it all
		foreach( $assets as $asset => $f ){
		
			// Loop through the files
			if( file_exists( $f["config"] ) && $handle = opendir( $f["config"] ) ) {

				// Loop through the files
				while (false !== ($f2 = readdir($handle))){
				
					// Make sure it's not a directory
					if( $f2 != "." && $f2 != ".." && stristr($f2,".php") ){
											
						// this will include this functio
						$variables = $this->configuration( $f["config"] . "/" . $f2 );
						
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
		
		// print_r($assets); die();

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
						$this->add_php(
										array(
											"file"	=> $f["install"] . "/" . $this->public->assets->php . "/" . $this->private->auto . "/" . $f["file"]
											)
										);
						
						// Output the installer
						$this->template(  ob_get_clean() );
						
						break;
	
					// if
					} else {
					
						// Else
						$this->error( "Could not configure the " . $asset . " plug-in. This means the plugin failed to configure at start up and there is not and install script or an __install method in the class." );
						
						break;
					
					// if
					}
				
				// if
				}

			// if
			}
		
		// foreach
		}

		// Set that it's loaded
		$this->event($assets, 'configure');
		
		// The last oneis loaded
		$this->fire_event("all","configured"); 

		// Now load them all
		foreach( $assets as $asset => $f ){
		
			// Load the javascript and css
			foreach( array($this->public->assets->js,$this->public->assets->css ) as $library ){

				$f[ $library ] = $f[ $library ] . "/" . $this->private->auto  . "/";

				// Loop through the files
				if( file_exists( $f[ $library ] ) && $handle = opendir( $f[ $library ] ) ) {

					// Loop through the files
					while (false !== ($f2 = readdir($handle))){
					
						// Make sure it's not a directory
						if( $f2 != "." && $f2 != ".." && is_file($f[ $library ] . $f2) ){

							// Add the css and js files							
							$this->add( 
									array( 
										"type"	=>	$library,
										"file"	=>	$f[ $library ] . $f2
										)
									);
			
						// if
						}
						
					// while
					}
					
				// if
				}
				
			// foreach
			}
			
			// Check out if there are any php js
			if( isset($GLOBALS[$asset]->public) ){

				// We may be installing
				if( !isset( $this->public->js->variable[ $asset ] ) ){
				
					// Assign js variables
					$this->public->js->variable[ $asset ] = $GLOBALS[$asset]->public;
			
			
				// if
				}
				
			// if
			}

			// Check if there are any inline stylings
			if( isset($GLOBALS[$asset]->style) ){

				// We may be installing
				if( !isset( $this->style->css[ $asset ] ) ){
				
					// Assign js variables
					$this->style->css[ $asset ] = $GLOBALS[ $asset ]->style;
			
				// if
				}
				
			// if
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
		
		// Set that it's loaded
		$this->event($assets, 'assign');
		
		// The last oneis loaded
		$this->fire_event("all","assigned");
		
		// Check if there's a cron running, we don't need to do any more
		if( isset($_GET[ $this->private->cron->querystring ]) ){
	
			// Start with bento
			$responses = $this->cron("bento");
			
			// Turn it into an array
			if( !$responses ){ $responses = array(); }

			// Assign variables in the class if need be
			foreach( $assets as $asset => $f ){
				
				// Do the cron and remembed the response
				$response = $this->cron( $asset );
				
				// Turn it into an array if it's false
				if( !$response ){ $response = array(); } 
			
				// Check if there's a load
				$responses = array_merge( $responses, $response );
			
			// foreach
			}
			
			// If we have done anything, report it
			if( count($responses) > 0 ){
			
				// Log the cron job
				$this->log("cron", "", $responses );
			
			// if
			}
			
			// Set that it's loaded
			$this->event($assets, 'cron');
			
			// The last oneis loaded
			$this->fire_event("all","cronned"); 
			
			// Kill it off
			die();

		// if
		}

		// Check if we're installed yet
		if( $this->state == "not installed" ){
	
			// Start the output buffer so we can get the install file into the template					
			ob_start();

			// Include the library file
			$this->add_js(
							array(
								"file"	=> "/bento/bento/" . $this->private->assets->install . "/" . $this->private->assets->install . "/" . $this->public->assets->js . "/" . $this->private->auto . "/bento.js"
								)
							);
			
			// Include the library file
			$this->add_php(
							array(
								"file"	=> "/bento/bento/" . $this->private->assets->install . "/" . $this->private->assets->install . "/" . $this->public->assets->php . "/" . $this->private->auto . "/bento.php"
								)
							);
			
			// Output the installer
			$this->template( ob_get_clean() );
			
		// if
		}

		// Check if we've got a complete install
		if( $this->state == "installed" ){
	
			// Start the output buffer
			ob_start();
			
			// Now load them all
			foreach( $assets as $asset => $f ){
			
				// Check if there's a load
				if( method_exists($GLOBALS[$asset],"__load") ){
							
					// Add this
					$GLOBALS[$asset]->__load($this);
	
				// if
				}
			
			// foreach
			}
	
			// Set that it's loaded
			$this->event($assets, 'load');
			
			// The last oneis loaded
			$this->fire_event("all","loaded"); 

			// Take the contents from the php files		
			$this->html = ob_get_contents();
			ob_end_clean();

			// The last oneis loaded
			$this->fire_event("bento","loaded");
			
			// Replace all shortcodes
			$this->html = $this->replace_shortcodes( $this->html );
		
			// Set that it's loaded
			$this->event($assets, 'shortcode');
			
			// The last oneis loaded
			$this->fire_event("all","shortcoded"); 
			
			// Assign variables in the class if need be
			foreach( $assets as $asset => $f ){
			
				// Check if there's a load
				if( method_exists($GLOBALS[$asset],"__deconstruct") ){
							
					// Add this
					$GLOBALS[$asset]->__deconstruct($this);

				// if
				}
			
			// foreach
			}

			// Set that it's loaded
			$this->event($assets, 'deconstruct');
			
			// The last oneis loaded
			$this->fire_event("all","deconstructed"); 
	
		// if
		} else {
		
			// The last oneis loaded
			$this->fire_event("bento","loaded"); 
		
			// Just output the install code
			$this->html = $this->replace_shortcode("bento",$this->html);
		
		// Check it
		}

		// Finish it up
		$this->output();

	// method
	}

	/*
	@method: __shortcode( $shortcode,$html )
	@description: Replaces any shortcode in the document
	@params:
	@shortcode:  
	@return:
	*/
	public function __shortcode( $shortcode,$html ){
			
		// Replace the javascript
		if( stristr($html,"<!--bento:javascript-->") ){

			// Start the output buffer
			ob_start();
		
			// Add the javascript
			$this->javascript( true );
		
			// Take the contents from the php files		
			$text = ob_get_contents();
			ob_end_clean();
		
			$html = str_replace("<!--bento:javascript-->",$text,$html);
			
		// if
		}
		
		// Replace the javascript
		if( stristr($html,"<!--bento:css-->") ){

			// Start the output buffer
			ob_start();
		
			$this->css( true );
		
			// Take the contents from the php files		
			$text = ob_get_contents();
			ob_end_clean();
		
			$html = str_replace("<!--bento:css-->",$text,$html);
			
		// if
		}
		
		return $html;
	
	// method
	}

	/*
	@method: event( $assets, $event )
	@description: Tell the program an event has occured
	@params:
	@shortcode:  
	@return:
	*/
	public function event( $assets, $event ){

		// Loop through it all
		foreach( $assets as $asset => $file ){
		
			$this->fire_event($asset,$event);
			
		// foreach
		}	
			
	// method
	}

	/*
	@method: add_event($target,$event,$class=null,$method=null,$args=null)
	@description: Tell the program to watch for an event
	@params:
	@shortcode:  
	@return:
	*/
	public function add_event($target,$event,$method=null,$args=null){
		
		// get the referring class
		$plugin = debug_backtrace(false);
		
		// get the plugin
		$class = $plugin[1]["class"];
		
		if (empty($target)) die("You must include a method (function) when using addEvent.");
		if (empty($method)) die("You must include a method (function) when using addEvent.");
		$this->events[$target][$event][]=array((!empty($class)?array($class,$method):$method),$args);
		return $this;
	// method
	}

	/*
	@method: clear_event($target,$eventl)
	@description: Clears any events
	@params:
	@shortcode:  
	@return:
	*/
	public function clear_event($target,$eventl){ 
	
		if (!empty($event)) unset($this->events[$target][$event]); else $this->event=null;

	// method
	}

	/*
	@method: fire_event($target,$event)
	@description: Outputs javascript loadeds
	@params:
	@shortcode:  
	@return:
	*/
	public function fire_event($target,$event){
		
		if (empty($this->events[$target][$event])) return $this;
		foreach ($this->events[$target][$event] as $hkey=>$hvalue) {
			$class = (string)$hvalue[0][0];
			$method = (string)$hvalue[0][1];
			
			// Call it
			$GLOBALS[ $class ]->$method();
		}
		return $this;
		
	// method
	}

	/*
	@method: add_shortcode($code,$class=null,$method="__shortcode")
	@description: Adds shortcode to be replaced before outputting
	@params:
	@shortcode:  
	@return:
	*/
	public function add_shortcode($code,$method="__shortcode"){
		
		// get the referring class
		$plugin = debug_backtrace(false);
		
		// get the plugin
		$class = $plugin[1]["class"];
	
		if (empty($code)) die("You must include a method (function) when defining add_hook.");
		if (empty($method)) die("You must include a method (function) when defining add_hook.");
		$this->private->shortcode[$class][]=array($method,$code);
		
		return $this;
		
	// method
	}

	/*
	@method: replace_shortcodes( $class,$html )
	@description: Recursive functiont to remove all shortcodes
	@params:
	@shortcode:  
	@return:
	*/
	public function replace_shortcodes( $html, $first=true ){
				
		global $bento;
	
		// Loop through all the plugins to replace short code
		foreach( $bento->private->plugins as $class ){

			// Check it
			$html = $this->replace_shortcode($class,$html);

		// foreach
		}
		
		// Loop through it
		foreach( $bento->private->shortcode as $plugin => $codes ){
							
			// Loop through it all
			foreach( $codes as $code ){
				
				// Do bento at the end
				if( !stristr($code[1],"<!--bent") ){
			
					// Let's look for some buttons		
					preg_match_all("@" . $code[1] . "@",$html,$tmp);
					
					// If there are still shortcodes, we'll 
					if( count($tmp[0]) > 0 ){
						
						// Execute another search
						$html = $this->replace_shortcodes( $html, false );
						
					// if
					}
					
				// if
				}
				
			// foreach
			}
				
		// foreach	
		}
	
		// On the end of the first request we 
		if( $first ){
			
			// Return this
			$html = $this->replace_shortcode("bento",$html);
	
		// if
		}
		
		// Return it
		return $html;

	// method
	}

	/*
	@method: replace_shortcode( $class,$html )
	@description: Replaces the shortcode, utilized a function
	@params:
	@shortcode:  
	@return:
	*/
	public function replace_shortcode( $class,$html ){
	
		if (empty($this->private->shortcode[$class])) return $html;
		foreach ($this->private->shortcode[$class] as $key => $value ) {
		
			$method = (string)$value[0];
			$shortcode = (string)$value[1];
			
			// Call it
			$html = $GLOBALS[ $class ]->$method($shortcode,$html);
		}
		
		return $html;
		
	// method
	}

	/*
	@method: add_action($code)
	@description: Adds javascript code to launch on startup
	@params:
	@shortcode:  
	@return:
	*/
	public function add_action($code){
		
		// get the referring class
		$plugin = debug_backtrace(false);
		
		// get the plugin
		$class = $plugin[1]["class"];

		// Assign js variables
		$this->public->js->action[] = $code;
			
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
		if( !isset($options["file"]) && isset($options["plugin"]) && isset($options["name"]) ){
	
			// Get the file name
			$options["file"] = $options["name"] . "." . $options["type"];
			$options["file"] = str_replace($_SERVER['DOCUMENT_ROOT'],"",$options["file"]);
	
			// Potential location for the files - start with manual
			$manual = str_replace("////","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory . "/" . $options["plugin"]. "/" . $this->public->assets->{ $options["type"] } . "/" . $this->private->manual . "/" . $options["file"]);
			$auto = str_replace("////","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory . "/" . $options["plugin"]. "/" . $this->public->assets->{ $options["type"] } . "/" . $this->private->auto . "/" . $options["file"]);
	
			// Check if this is the auto plugin
			if( file_exists( $manual ) ){
		
				$f = $manual;
	
			// Check if this exists
			} else if( file_exists( $auto ) ){
			
				$f = $auto;
			
			// if
			}
	
		// if
		} else if( isset( $options["file"] ) ){
		
			$f = $options["file"];
			
		// if
		}
		
		// Check if we found a file
		if( is_null($f) ){ return false; }
	
		// Add a php file
		if( $options["type"] == "php" ){
		
			// Include all the classes
			foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
			$bento = $this;
			
			// Check if the file exists before including it
			if( file_exists( $_SERVER['DOCUMENT_ROOT'] . str_replace($_SERVER['DOCUMENT_ROOT'],"",$f) ) ){ 
			
				require_once $_SERVER['DOCUMENT_ROOT'] . str_replace($_SERVER['DOCUMENT_ROOT'],"",$f);

			// There is an error
			} else {
			
				// Output an error
				$this->error( $_SERVER['DOCUMENT_ROOT'] . str_replace($_SERVER['DOCUMENT_ROOT'],"",$f) . " does not exist." );
			
			// if
			}

		// Add a javascript file
		} else if( $options["type"] == "js" || $options["type"] == "javascript" || $options["type"] == "css" ){
			
			// Otherise, let's work with a name
			if( !isset($options["name"]) ){
			
				// Break it apart
				$name = explode("/",$f);
				$name = end($name);	
					
			// if
			} else {
			
				$name = $options["name"];
			
			}
			
			// These are the file options
			$f = (object)array(
								"name"	=>	$name,
								"file"	=>	str_replace($_SERVER['DOCUMENT_ROOT'],"",$f)
								);
			
			// Loop through the files to search for an existing object
			$add = true;
			
			// Check to make sure it's not already in the array
			foreach( $this->private->{ $options["type"] }->file as $added ){
				
				// Check if the files already added
				if( $added->file == $f->file ){
				
					// Change it
					$add = false;
				
					break;
					
				// if
				}
				
			// if
			}
								
			// Make sure there's no duplicates
			if( $add ){
		
				// Check it
				if( !isset($options["place"]) ){
				
					$this->private->{ $options["type"] }->file[] = $f;
					
				// if
				} else {
				
					$this->private->{ $options["type"] }->file[ $options["place"] ] = $f;
				
				// if
				}
			
			// if
			}
			
		// if
		}
	
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
		
		// Check if we need the document root
		if( !stristr($library,$_SERVER['DOCUMENT_ROOT']) ){
			
			// Check it
			$library = $_SERVER['DOCUMENT_ROOT'] . $library;
			
		// if
		}

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
			
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
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
			
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
		// if
		}
	
		// Add the file type
		return $this->add($options);
	
	// method
	}

	/*
	@method: remove_file( $options )
	@description: Remove a file from the registry
	@params:
	@shortcode:  
	@return:
	*/
	public function remove_file( $options ){

		// Check it
		if( !isset($options["type"]) || !isset($options["place"]) ){ return false; }
		
		// Let's remove the mootools libraries
		unset( $this->private->{ $options["type"] }->file[ $options["place"] ] );
		
		// Reset the array inthe event there we files removed
		$this->private->{ $options["type"] }->file = array_merge($this->private->{ $options["type"] }->file);
		
	// method
	}

	/*
	@method: remove_files( $options )
	@description: Remove a all files of one asset type from the registry
	@params:
	@shortcode:  
	@return:
	*/
	public function remove_files( $options ){

		// Check it
		if( !isset($options["type"]) ){ return false; }
		
		// Let's remove the mootools libraries
		$this->private->{ $options["type"] }->file = array();
		
	// method
	}

	/*
	@method: files( $options )
	@description: Returns the asset files
	@params:
	@shortcode:  
	@return:
	*/
	public function files( $type ){

		return $this->private->{ $type }->file;

	// method
	}

	/*
	@method: add_variable( $options )
	@description: Adds a js or css variable to the program
	@params:
	@shortcode:  
	@return:
	*/
	public function add_variable( $options ){
	
		// Check it
		if( (!isset($options["name"]) || !isset($options["id"]) || !isset($options["class"])) || !isset($options["value"]) || !isset($options["type"]) ){ return false; }
	
		// Allow the plugins
		foreach( $GLOBALS as $key => $class ){ if( is_object($class) ){ global $$key; }}
		
		// Clear this up
		if( !isset($options["plugin"]) ){
			
			$plugin = debug_backtrace(false);
			$options["plugin"] = $plugin[1]["class"];
			
		// if
		}
		
		// Now add it
		if( isset($GLOBALS[ $options["plugin"] ]) ){
			
			// Check if out
			if( $options["type"] == "js" || $options["type"] == "javascript" ){
			
				// Check what we're adding here
				if( !isset($GLOBALS[ $options["plugin"] ]->public->{ $options["name"] }) || (!is_array( $GLOBALS[ $options["plugin"] ]->public->{ $options["name"] } ) && !is_object( $GLOBALS[ $options["plugin"] ]->public->{ $options["name"] }) ) ){
			
					// There's the value
					$GLOBALS[ $options["plugin"] ]->public->{ $options["name"] } = $options["value"];
					
				// if
				}
					
			// if
			}
			
		// if
		}
		
	// method
	}

	/*
	@method: add_style( $name, $options )
	@description: Adds a css id or class style to the program
	@params:
	@shortcode:  
	@return:
	*/
	public function add_style( $name, $options ){
		
		// Add a new css style
		$this->style[] = array(
								$name => $options								
								);
		
		// return true
		return true;
		
	// method
	}

	/*
	@method: assets($type="both" )
	@description: Adds javascript and css to the output
	@params:
	@shortcode:  
	@return:
	*/
	public function assets($type="both" ){
	
		// We don't need to do anything if this is data
		if( $this->private->output == "data" ){ return; }
	
		// Include the js if need be
		if( ( $type == "both" || $type == "css" ) && (!stristr("bento:css",$this->html)) ){
		
			$this->html = str_replace("</head>","<!--bento:css--></head>",$this->html);
		
		// if
		}
		
		// Include the js if need be
		if( ( $type == "both" || $type == "js" || $type == "javascript" ) && (!stristr($this->html,"->javascript") && !stristr("bento:javascript",$this->html)) ){
		
			$this->html = str_replace("</body>","<!--bento:javascript--></body>",$this->html);
		
		// if
		}

	// method
	}

	/*
	@method: javascript( $echo=true )
	@description: Outputs javascript loadeds
	@params:
	@shortcode:  
	@return:
	*/
	public function javascript( $echo=true ){

		// Create the public varaible array and list of actioons
		$js = array_merge(
						array("bento"	=>	
										array("actions"	=>	$this->public->js->action )	
										),
						$this->public->js->variable
					);

		// This is to start up all the dynamic js
		$js = "var bento = " . json_encode($js) . ";";
		
		// Check if we're compacting or not (only the public variables)
		if( !$this->private->compress->js ){
		
			// Add the beautify php
			$this->add_php(
						array(
							"plugin"	=>	"bento",
							"name"	=>	"beautify"	
						)
					);
		
			// Beautify the json for human readability
			$beautify = new beautify();
			$js = $beautify->json( $js ); 
			
		// if
		}

		// This is what we replace
		echo "<script type=\"text/javascript\">\r\n" . $js . "\r\n</script>\r\n\r\n";
		
		// Reset what we're outputting
		$js = "";

		// Check if we're compacting or not
		if( !$this->private->cache->js ){
							
			// Loop through the included js filed
			foreach( $this->private->js->file as $f ){
	
				// Make sure to add a qs to clear it up
				if( $this->private->clear_cache ){
	
					// Check it
					( stristr($f->file,"?") ) ? $f->file .= "&" . time() : $f->file .= "?" . time();
	
				// if
				}
	
				// This is what goes out
				$js .= "<script type=\"text/javascript\" src=\"" . $f->file. "\"></script>\r\n";
			
			// foreach
			}

		// Otherwise, no
		} else {
			
			// Check it
			$url = $this->cache(
						array(
							"operation"	=>	"check",
							"type"	=>	"js"
						)
					);
			
			// Check to make sure there's not already a file
			if( !$url ){
			
				// Loop through the files
				$js = $this->content(
									array(
										"type"	=>	"js",
										"external"	=>	false
										)
									);
		
				// Create the url and the cache file
				$url = $this->cache(
								array(
									"operation"	=>	"generate",
									"type"	=>	"js",
									"content"	=>	$js
									)
								);
							
			// if
			}
			
			// This is what goes out
			$js = "<script type=\"text/javascript\" src=\"/?" . $this->private->cache->querystring . "=" . $url . "\"></script>\r\n";

			// Loop through the included js filed
			foreach( $this->private->js->file as $f ){
	
				// Check it
				if( stristr($f->file,"http://") || stristr($f->file,"https://") ){
				
					// This is what goes out
					$js .= "<script type=\"text/javascript\" src=\"" . $f->file . "\"></script>\r\n";
			
				// if
				}
			
			// foreach
			}
		
		// if
		}
		
		// Add another break
		$js .= "\r\n";

		// Return it
		return $echo ? print $js : $js;
	
    // method
	}
	
	/*
	@method: css( $echo=true )
	@description: Outputs css loadeds
	@params:
	@shortcode:  
	@return:
	*/
	public function css( $echo=true ){

		global $file;

		// THis is what we replace
		$css = "";
		
		// Check the directory 
		$directory = $this->private->directory . "/" . $this->private->directory . "/" . $this->private->assets->tmp . "/" . $this->public->assets->css . "/";
		
		// Check if we're compacting or not
		if( !$this->private->cache->css ){
		
			// Loop through the included js filed
			foreach( $this->private->css->file as $f ){
			
				// Make sure to add a qs to clear it up
				if( $this->private->clear_cache ){
	
					// Check it
					( stristr($f->file,"?") ) ? $f->file .= "&" . time() : $f->file .= "?" . time();
	
				// if
				}	
				// This is what goes out
				$css .= "\t" . trim("<link rel=\"stylesheet\" href=\"" . $f->file  . "\" type=\"text/css\">") . "\r\n";
			
			// foreach
			}

		// Otherwise, no
		} else {
	
			// Check it
			$url = $this->cache(
								array(
									"operation"	=>	"check",
									"type"	=>	"css"
								)
							);
			
			// Check to make sure there's not already a file
			if( !$url ){
						
				// Loop through the files
				$css = $this->content(
									array(
										"type"	=>	"css",
										"external"	=>	true
										)
									);
		
				// Create the url and the cache file
				$url = $this->cache(
									array(
										"operation"	=>	"generate",
										"type"	=>	"css",
										"content"	=>	$css
										)
									);
	
			// if
			}
			
			// This is what goes out
			$css = "\t<link rel=\"stylesheet\" href=\"/?" . $this->private->cache->querystring . "=" . $url . "\" type=\"text/css\">\r\n";

		// if
		}
		
		// Make sure there are styles and that they aren't compressed
		if( count($this->style) > 0  ){
			
			$inline = "";
		
			// Beautify if need be
			if( !$this->private->compress->css ){
			
				// Add the beautify php
				$this->add_php(
							array(
								"plugin"	=>	"bento",
								"name"	=>	"beautify"	
							)
						);
				
			// if
			}
			
			// This is to start up all the dynamic js
			foreach( $this->style as $style ){
			
				// Clean it all up
				$style = json_encode( $style );
				
				// Check if we should clean it up or not
				if( !$this->private->compress->css ){
				
					// Beautify the json for human readability
					$beautify = new beautify();
					$style = $beautify->json( $style ); 
				
				// if
				}
				
				// Clean it up
				$style = substr(str_replace("\"","",$style),1,-1);
				$style = str_replace(",",";",$style);
				$style = str_replace(":{","{",$style);
				
				// Lastly
				$style = str_replace("\n","\n\t",$style);
				
				// Add it all up
				$inline .= $style;
			
			// if
			}
	
			// This is what we replace
			$css .= "\t<style type=\"text/css\">" . $inline . "</style>\r\n";

		// if
		}

		// Return it
		return $echo ? print $css : $css;

	// method
	}

	/*
	@method: cahce( $create=true )
	@description: creates a cache file
	@params:
	@shortcode:  
	@return:
	*/
	public function cache( $options ){
		
		// Check if we're checking, retrieving, or outputting
		if( $options["operation"] == "check" || $options["operation"] == "generate" ){
		
			// Get JUST the path		
			$url = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
		
			// Get the url
			$path = "bento/bento/" . $this->public->assets->cache . "/" . $this->public->assets->{ $options["type"] };
			$url = base64_encode($url) . "." . $options["type"];
			
		// if
		}
	
		// Check if we're generating or not
		if( $options["operation"] == "check" ){
	
			// Check if the file exists
			if( file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $path . "/" . $url ) ){
				
				// Check if it's old or not
				if( (time()-($this->private->cache->time*86400)) < filemtime( $_SERVER['DOCUMENT_ROOT'] . "/" . $path . "/" . $url) ){
	
					return $url;
				
				// It's an old file
				} else {
					
					return false;
					
				// if
				}

			} else {
				
				return false;
				
			// if
			}
	
		// Check if we're generating or not
		} else if( $options["operation"] == "generate" ){
	
			// Put the contented
			file_put_contents( $_SERVER['DOCUMENT_ROOT'] . "/" . $path . "/" . $url,  $options["content"] );
		
			return $url;

		// This is for cache control of the cached files
		} else if( $options["operation"] == "output" ){
			
			// Break apart the url
			$url = explode(".",$_GET[ $this->private->cache->querystring ]);
			$type =  $url[1];
			
			// Get the pathc
			$path = $_SERVER['DOCUMENT_ROOT'] . "/" . "bento/bento/" . $this->public->assets->cache . "/" . $this->public->assets->{ $type };
			
			// Get the contents
			$contents = @file_get_contents( $path . "/" . $_GET[ $this->private->cache->querystring ] );
		
			// Get the last time it was modified
			$expires = ((int)time()+($this->private->cache->time*86400));
			$modified = filemtime( $path . "/" . $_GET[ $this->private->cache->querystring ]);
		
			// Compress it if possible
			ob_start('ob_gzhandler');
		
			// Now we're going to set the headers
			header("Content-type: text/" . str_replace("js","javascript",$type)  . "; charset=utf-8");
			header("Cache-Control: public");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $modified) . " GMT");
			header("Expires: " . gmdate("D, d M Y H:i:s", $expires) . " GMT");
			header("ETag: '" . md5($contents) . "'");

			// Output the content
			echo $contents;
		
			// Kill it
			die();
			
		// if
		}
			
	// method
	}
	
	/*
	@method: content( $options )
	@description: Get's the content of certain file types
	@params:
	@shortcode:  
	@return:
	*/
	public function content( $options, $echo=false ){

		// Check if it's a simple content request
		if( is_string($options) ){ $options = array("type"	=>	$options); }
		
		// Fix this up
		if( $options["type"] == "javascript" ){ $options["type"] == "js"; }
	
		// This is where we'll store everything
		$content = "";
		
		// Check if we're looking for a specific file or not
		if( !isset($options["name"] ) ){

			// Foreach
			foreach( $this->private->{ $options["type"] }->file as $file ){
				
				// Add a local file
				if( 
					(!stristr($file->file,"http://") && !stristr($file->file,"https://")) ||
					isset($options["external"]) && $options["external"] == true
					){
			
					// Include the file
					$content .= file_get_contents( ( (!stristr($file->file,"http://") && !stristr($file->file,"https://")) ? $_SERVER['DOCUMENT_ROOT'] : "" ) . $file->file) . "\r\n";
				
				// if
				}
				
			// foreach
			}
		
		// We're looking for a specific file
		} else {
			
			// Foreach
			foreach( $this->private->{ $options["type"] }->file as $file ){
				
				// Check it out
				if( $file->name == $options["name"] ){
					
					// Include the file
					$content .= file_get_contents( ( (!stristr($file->file,"http://") && !stristr($file->file,"https://")) ? $_SERVER['DOCUMENT_ROOT'] : "" ) . $file->file) . "\r\n";

				// if
				}
				
			// foreach
			}
			
		// if
		}
		
		// Check if compressing for js
		if( $options["type"] == "js" && ((isset($options["compress"]) && $options["compress"]) || $this->private->compress->js )){
	
			// Include it
			$this->add_php( array(
							"plugin" =>	"bento",
							"name"	=>	"jsmin"						
							)
						);	
					
			// Minify it all up	
			$content = ltrim(JSMin::minify($content));
		
		// if		
		}
		
		// Check if compressing for js
		if( $options["type"] == "css" && ((isset($options["compress"]) && $options["compress"]) || $this->private->compress->css )){
	
			// Include it
			$this->add_php( array(
							"plugin" =>	"bento",
							"name"	=>	"cssmin"						
							)
						);
						
			// Include the file
			$content = @CssMin::minify($content);	
		
		// if		
		}
		
		// Return it
		return $echo ? print $content : $content;

	// method
	}

	/*
	@method: configuration( $configuration )
	@description: Loads a configuration file
	@params:
	@shortcode:  
	@return:
	*/
	public function configuration( $configuration ){
	
		global $file;

		// ob it, so get the text
		ob_start();

		// Configure the bento library
		echo substr(trim(file_get_contents( trim($configuration) )),5,-2);
	
		// Get the varialbes
		$variables = ob_get_clean();
		$json = json_decode($variables);
		
		// Check it
		if( is_null($json) ){
		
			// Output an error
			$this->error("Cannot read the configuration file for " . $configuration );
		
		// if
		}

		// return it
		return $json;

	// method
	}

	/*
	@method: configure_check( $variables )
	@description: Check to see if everything we need is ready
	@params:
	@shortcode:  
	@return:
	*/
	public function configure_check( $variables ){

		// These are the vars
		foreach( array("public","private") as $type ){ 
		
			// Check if it exists
			if( isset( $variables[ $type ] ) ){
		
				// Loop through this type
				foreach( $variables[ $type ] as $post ){
		
					// Add the variables
					if( !isset( $_POST[ $post ] ) ){ return true; }

				// if
				}

			// if
			}

		// foreach
		}
		
		// Check for a license
		if( isset($variables["license"]) && !isset( $_POST["license"] ) ){ return true;}
		
		return false;

	// method
	}

	/*
	@method: configure_posts( $config, $variables )
	@description: Configures plug-ins for us
	@params:
	@shortcode:  
	@return:
	*/
	public function configure_posts( $config, $variables ){

		// These are the variables that will be sent to the confi
		$vars = array();

		// These are the vars
		foreach( array("public","private") as $type ){ 
		
			// Check if it exists
			if( isset( $variables[ $type ] ) ){
		
				// Loop through this type
				foreach( $variables[ $type ] as $post ){
		
					// Add the variables
					$vars[ $type ][ $post ] = $_POST[ $post ];

				// if
				}
				
				// Set it as an object
				$vars[ $type ] = (object)$vars[ $type ];

			// if
			}

		// foreach
		}
		
		// Check for a license
		if( isset($variables["license"]) ){ $vars["license"] = $_POST["license"];}
		if( isset($variables["state"]) ){ $vars["state"] = $variables["state"];}

		// Return the configuration
		return $this->configure( $config, (object)$vars );

	// method
	}

	/*
	@method: configure( $config, $variables )
	@description: Configures plug-ins for us
	@params:
	@shortcode:  
	@return:
	*/
	public function configure( $config, $variables ){
		
		// This is the file we're working with to read/write the confi
		$config = str_replace($_SERVER['DOCUMENT_ROOT'],"",$config);
	
		// First let's read the configuration
		$current = $this->configuration( $_SERVER['DOCUMENT_ROOT'] . "/" . $config );
		
		// These are the new variables merged with the old ones
		$new = ( isset($current) ) ? (object)array_merge((array)$current, (array)$variables) : (object)$variables;
	
		// These are the variables
		$json = trim(json_encode( $new ));
	
		// Add the beautify php
		$this->add_php(
					array(
						"plugin"	=>	"bento",
						"name"	=>	"beautify"	
					)
				);
	
		// Beautify the json for human readability
		$beautify = new beautify();
		$json = $beautify->json( $json ); 
	
		// Format to php
		$variables = trim("<?php\r" . $json . "\r?>");
	
		// Write the file
		$configuration = $_SERVER['DOCUMENT_ROOT'] . $config;
		
		// Handle the opening
		$handler = @fopen($configuration, 'w');
	
		// Write it in
		if( $handler ){

			// Write the file
			fwrite($handler, $variables);
			
			// Close it
			fclose($handler);
		
			// redirect the site
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: /');
		
		} else {
		
			return trim($variables);
		
		// if
		}

	// method
	}

	/*
	@method: install( $filename )
	@description: Installs files from a zip
	@params:
	@shortcode:  
	@return:
	*/
	public function install( $filename ){
	
		global $archive;
		
		echo $filename;
		
		// Check if the file exists or not
		if( !file_exists( $_SERVER['DOCUMENT_ROOT'] . $filename ) ){
		
			return false;
			
		// if
		}

		// Open test.tbz2 
		$archive->archive( $_SERVER['DOCUMENT_ROOT'] . $filename ); 
		
		// Unzip all the contents of the zipped file 
		$archive->unzipAll(); 

		// Return it			
		return true;

	// method
	}

	/*
	@method: template( $html )
	@description: Generates a bento template for installs and errors
	@params:
	@shortcode:  
	@return:
	*/
	public function template( $html ){

		// Add the template css
		$this->add_css(
						array(
							"plugin"	=>	"bento",
							"name"	=>	"template"	
						)
					);
					
		// We're going to get the template
		ob_start();

		// If		
		if( !file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory . "/" . $this->private->directory . "/" . $this->public->assets->php . "/" . $this->private->manual . "/template.php") ){
		
			echo "bento template is missing.";
			die();
		
		// if
		} 

		// Include the library file
		$this->add_php(
						array(
							"plugin"	=>	"bento",
							"name"	=> "template"
							)
						);
						
		// This is the template
		$this->html = str_replace("<!--bento:install-->",$html,ob_get_clean());
		
	// method
	}

	/*
	@method: error( $error, $variables )
	@description: Gracefully handles and logs errors
	@params:
	@shortcode:  
	@return:
	*/
	public function error( $error, $variables=array() ){
		
		// Make sure the world knows this is a bad thing
		header('HTTP/1.1 500 Internal Server Error');

		// If		
		if( !file_exists( $_SERVER['DOCUMENT_ROOT'] . "/" . $this->private->directory . "/" . $this->private->directory . "/" . $this->public->assets->php . "/" . $this->private->manual . "/error.php") ){
		
			// Error it 
			echo "<h2>Error page is missing.</h2>";
			echo "<p>" . $error . "</p>";
			
			// Kill it
			die();
		
		// if
		} 

		// Check
		if( !is_array($error) ){
		
			// Set it to an array
			$error = array("message"	=>	$error );
			
		// 
		}
		
		// Check it
		if( !isset($error["fatal"])){
		
			$error["fatal"] = true;
			
		// if
		}
		
		// Log this to the internal logging system
		$this->log("error",$error,$variables);
		
		// Log it to the web server log
		error_log("bento Error: " . $error["message"] );
		
		// Normal errors when not html
		if( isset($_GET["bento"]) ){

			echo $error["message"]; die();

		// if
		}

		// Kill it
		if( $error["fatal"] ){

			// We're going to get the template
			ob_start();
	
			// Include the library file
			$this->add_php(
							array(
								"plugin"	=>	"bento",
								"name"	=> "error"
								)
							);
							
			// Here is the error
			$html = ob_get_clean();
										
			// This is the template
			$this->template( str_replace("<!--bento:error-->",$error["message"],$html) );
	
			// Output the html
			$this->assets();
			
			// Output the javascript
			$this->javascript();
			
			// Then the css
			$this->css();
	
			// Output the html
			$this->output();
			
			die();

		// if
		}

	// method
	}

	/*
	@method: cron( $plugin )
	@description: This will run cron jobs for bento, and it's plugins
	@params:
	@shortcode:  
	@return:
	*/
	public function cron( $plugin ){
							
		// Check if the file exists
		if( file_exists( $GLOBALS[ $plugin ]->cron ) ){
						
			// We're going to read the file
			$cron = @file_get_contents( $GLOBALS[ $plugin ]->cron );	
			$shortcode = null; // not a shortcode pointer by default
			
			// Check if we're using a shortcode
			if( stristr($cron,"<!--bento:cron") ){
	
				// Let's look for some buttons		
				preg_match_all("@<!--bento:cron:([^>]+|)-->@",$cron,$tmp);

				// Loop through it
				foreach( $tmp[1] as $f ){
				
					// Include the file
					$cron = @file_get_contents( $_SERVER['DOCUMENT_ROOT'] . "/" . $f );
				
					// Note that this was a shortcode
					$shortcode = $_SERVER['DOCUMENT_ROOT'] . "/" . $f;
				
				// foreach
				}
				
			// if
			}
			
			// Make sure were we no errors reading the cron
			if( $cron ){
				
				// This is where we'll store the operations to executie
				$operations = array();
		
				// Let's look for some shortcodes		
				preg_match_all("@function(.*)\((.*)\)@",$cron,$tmp);
				
				// Loop through the crons
				foreach( $tmp[1] as $i => $method ){

					// Get the time
					$time = str_replace("=","",str_replace("\$time","",str_replace("\"","",strtolower($tmp[2][ $i ]))));
					
					// Save it to check and run later
					$operations[] = array("method"	=>	trim($method),	"time"	=>	trim($time));
					
				// foreach
				}
				
				// Now loop through the operations to see if we should run them
				foreach ( $operations as $i	=> $operation ){
					
					// Get the time
					$time = trim($operation["time"]);
			
					// Check the times
					if( $time == "1minute" ){
						continue;
					} else if( $time == "5minutes" && date("i")%5 ){
						continue;
					} else if( $time == "10minutes" && date("i")%10 ){
						continue;
					} else if( $time == "15minutes" && date("i")%15 ){
						continue;
					} else if( $time == "30minutes" && date("i")%30 ){
						continue;
					// if
					}
					
					// We only do this on the first minute of the hour
					if( date("i")==0 ){
						
						// Check for an hour
						if( $time == "60minutes" ){
							continue;
						} else if( $time == "1hour" ){
							continue;
						} else if( $time == "5hours" && date("H")%5 ){
							continue;
						} else if( $time == "10hours" && date("H")%10 ){
							continue;
						} else if( $time == "15hours" && date("H")%15 ){
							continue;
						} else if( $time == "20hours" && date("H")%20 ){
							continue;
						}

						// Get greater than a day
						if( date("H")==0 ){
							
							// One day
							if( $time == "1day" ){
								continue;
							} else if( $time == "24hours" ){
								continue;
							} else if( $time == "7days" && date("N") == 1 ){
								continue;
							}
						
							// Make sure we're on week one
							if( date("N") == 1 ){
						
								if( $time == "1week" ){
									continue;
								} else if( $time == "2weeks" && (date("j") == 1 || date("j") == 15) ){
									continue;
								} else if( $time == "4weeks" && date("j") == 1 ){
									continue;
								} else if( $time == "1month" && date("j") == 1 ){
									continue;
								} else if( $time == "3months" && (date("z") == 1 || date("z") == 90 || date("z") == 180 || date("z") == 270) ){
									continue;
								} else if( $time == "6months" && (date("z") == 1 || date("z") == 180) ){
									continue;
								} else if( $time == "1year" && date("z") == 1 ){
									continue;
								} 
								
							// if
							}
						
						// if
						}
						
					// if
					}
					
					// Remove this from the operation if we made it this far
					unset($operations[ $i ]);
				
				// foreach
				}
				
				// Include the cron library if we have something to do
				if( count($operations) > 0 ){
					
					// Using a default
					if( is_null($shortcode) ){
					
						// Include the library
						include $GLOBALS[ $plugin ]->cron;
					
					// Using a shortcode pointer
					} else {
						
						// Include the shortcode pointed
						include $shortcode;
						
					// if
					}
					
					// Create the cron from a plugin
					$plugin_cron = $plugin . "_cron";
				
					// Start up the cron
					$cron = new $plugin_cron();
		
				// if
				}
				
				// This is where we'll store the responses
				$responses = array();
				
				// Loop through the operations
				foreach( $operations as $operation ){
					
					// This is the method we're going to run
					$method = $operation["method"];
					
					// Keep a record of what transipred
					$responses[] = array(
										"plugin"	=>	$plugin,
										"method"	=>	$operation["method"],
										"time"	=>	$operation["time"],
										"response"	=>	$cron->$method() 
										);
					
				// forech
				}
				
				// Return this
				return $responses;
		
			// if
			}
		
		// if
		}
		
		// Return nothing so we know what to add to the log
		return false;

	// method
	}

	/*
	@method: log( $type, $message, $variables=array() )
	@description: Logs error, remove access from control server, and crons
	@params:
	@shortcode:  
	@return:
	*/
	public function log( $type, $message, $variables=array() ){

		// Get the root
		$file = $_SERVER['DOCUMENT_ROOT'] . $this->private->log->{ $type } . time() . ".php";
							
		// Write different logs
		if( $type == "error" ){
		
			$log = array(
						"time"	=>	date("Y m d H:i:s",time()),
						"timstamp"	=>	time(),
						"message"	=>	$message,
						"server"	=>	$_SERVER,
						"environment"	=>	debug_backtrace(false),
						"variables"	=>	$variables		
						);

		// Otherwise
		} else {

			$log = array(
						"time"	=>	date("Y m d H:i:s",time()),
						"timstamp"	=>	time(),
						"responses"	=>	$variables		
						);		
		
		// if
		}

		// Add the beautify php
		$this->add_php(
					array(
						"plugin"	=>	"bento",
						"name"	=>	"beautify"	
					)
				);

		// Beautify the json for human readability
		$beautify = new beautify();
		$log = $beautify->json( trim(json_encode($log)) ); 
	
		// Format to php
		$log = trim("<?php\r" . $log . "\r?>");
					
		// Write it out now		
		$file_name = fopen($file, 'w');
		if( !$file_name ){ return $file_name; }
		fwrite($file_name, $log );
		fclose($file_name);
		@chmod($file, 0774);
		
		// Return it (if need be)
		return true;

	// method
	}

	/*
	@method: licensing
	@description: This will attain a new license, or check the current license
	@params:
	@shortcode:  
	@return:
	*/
	public function licensing( $attain=false,$variables=array() ){

		// Check if we're getting a new license
		if( $attain ){
		
			//open connection
			$ch = curl_init();
			
			// Get the domain
			$variables["host"] = $_SERVER['HTTP_HOST'];
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$this->private->license);
			curl_setopt($ch,CURLOPT_POST,0);
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($variables));
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($ch,CURLOPT_TIMEOUT, 120);
  
			//execute post
			$number = curl_exec($ch);
			
			//close connection
			curl_close($ch);
			
			// There was an error
			if( !$number ){
			
				// Redirect to the install file
				header("Location: http://www.builtonbento.com/install/?error=Could not attain a new license.");

			// if
			}
			
			// Here is the license number
			return $number;
				
		// Check it out
		} else {
		
			// Check to make sure it's licensed
			if( $this->license == "" ){
			
				return false;
				
			// It's not
			} else {
			
				return $this->license;
				
			// if
			}
		
		// if
		}

	// method
	}

	/*
	@method: output()
	@description: Outputs the final html
	@params:
	@shortcode:  
	@return:
	*/
	public function output( $type="html" ){
		
		// Setup the appropriate headers
		if( $type == "html" ){
			
			$type = "text/html";
			
		} else if( $type == "js" || $type == "javascript" ){
			
			$type = "application/javascript";
			
		} else if ( $type == "json" ){
			
			$type = "application/json";
			
		} else if( $type == "css" ){
			
			$type = "text/css";
			
		// if
		}

		// Set up the headers
		header("Content-Type: " . $type . "; charset=utf-8;");
	
		// Check if we're outputting html or data
		if( $this->private->output == "html" ){

			// Compress the final products
			ob_start();
	
			// Output the contents
			$html = $this->html;
	
			// Check if we're compacting or not
			if( $this->private->compress->html ){
			
				// Include it
				$this->add_php( array(
								"plugin" =>	"bento",
								"file"	=>	"htmlmin"						
								)
							);
			
				$html = html_compress($html);
				
			// if
			}
			
			// Echo it out
			echo $html;
			
			// or in the end use
			ob_end_flush();
		
		// Otherwise, we just want data
		} else {
		
			// Create the public varaible array and list of actioons
			$output["variables"] = array_merge(
							array("bento"	=>	
											array("actions"	=>	$this->public->js->action )	
											),
							$this->public->js->variable
						);
			
			// This is likely a remote call
			foreach( array("js","css") as $asset ){
				
				// Now loop through them
				foreach( $this->private->{ $asset }->file as $key => $f ){
					
					// Chec it
					$this->private->{ $asset }->file[ $key ] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $f->file;
					
				// foreach
				}
				
			// foreach
			}
			
			// This is what we're going to output
			$output["js"] = $this->private->js->file;
			$output["css"] = $this->private->css->file;
			
			// Check if we should clean it up or not
			if( !$this->private->compress->js ){
			
				// Add the beautify php
				$this->add_php(
							array(
								"plugin"	=>	"bento",
								"name"	=>	"beautify"	
							)
						);
			
				// Beautify the json for human readability
				$beautify = new beautify();
				$output = $beautify->json( json_encode($output) );
			
			// if
			} else {
				
				$output = json_encode($output);
				
			// if
			}
			
			// JSON or JSONP
			print $output;
		
		// if
		}
		
		// The last oneis loaded
		$this->fire_event("all","output"); 
		
		// Kill it
		die();

	// method
	}

// class
}

// This will deal with errors gracefully
function errors( $errno , $errstr, $errfile, $errline, $errcontext ){

	// Set it up
	$GLOBALS["bento"]->error( $errstr . ". Line " . $errline . " in " . $errfile . "." );
	
// method
}
?>