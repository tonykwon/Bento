<?php
/*
@class: form
@description: Common form functions, validations, population, and ajax handling
@params:
*/ 
class form{

	// Load the variables 
	public $public;
	public $private;
	public $log;

	/*
	@method: __construct($bento)
	@description: Instantiates the class
	@params:
	@shortcode:  
	@return:
	*/ 
	public function __construct(){
		
		global $bento;
		
		// Load this when
		$bento->add_event('all','loaded','submit');

	// method
	}

	/*
	@method: __assign($bento)
	@description: Instantiates the class
	@params:
	@shortcode:  
	@return:
	*/ 
	public function __assign(){
		
		global $bento;
	
		// Check if we need to create a token
		if( !isset($_SESSION["bento"]["form"]["token"]["value"]) ){
		
			$_SESSION["bento"]["form"]["token"]["value"] = md5(uniqid(rand(), true));
		
		// if
		}
		
		// Set the url for js
		$this->public->url = $bento->public->url;
		
		// Setup the variables
		$this->private->token_value = $_SESSION["bento"]["form"]["token"]["value"];

	// method
	}

	/*
	@method: __configure($bento)
	@description: Configures the library
	@params:
	@shortcode:  
	@return:
	*/ 
	public function __configure(){

		global $bento;

		// include library
		$bento->add_php(
					array(
						"plugin"	=>	"form",
						"name"	=>	"handler"
					)
				);
			
		// Make sure we can play
		$GLOBALS["handler"] = new handler();
		
		// Tell the world all is bonne
		return true;
		
	// method
	}

	/*
	@method: submit()
	@description: Submits the form
	@params:
	@shortcode:  
	@return:
	*/ 
	public function submit(){
		
		global $handler;
		
		// Handles the submission
		$handler->submit();
	
	// method
	}
	
	/*
	@method: handler( $response )
	@description: This will deal with more handling
	@params:
	@shortcode:  
	@return:
	*/ 
	public function handler( $response ){

		// Let's check to see what our handler is
		if( isset($_POST[ $this->public->handler ]) ){

			// Remove brackets if need be
			$tmp_handler = str_replace( "(","", trim($this->decrypt( $_POST[ $this->public->handler ] )) );
			$tmp_handler = str_replace( ")","",$tmp_handler );
			$tmp_handler = str_replace( ";","",$tmp_handler );

			// Check if method or function
			if( strstr($tmp_handler,">") ){
			
				$tmp_array = explode("->",$tmp_handler);
				$tmp_class = $tmp_array[0];
				$tmp_handler = $tmp_array[1];
				
				// Check it out
				if( method_exists($GLOBALS[$tmp_class],$tmp_handler) ){
					
					// Try loading this a different way
					$GLOBALS[ $tmp_class ]->$tmp_handler( $response );
				
				} else {
				
					$this->response(
									array(
										"response"	=>	false,
										"message"	=>	"Method handler " . $tmp_class . "->" . $tmp_handler . " not found."
										)
									);
				
				// if
				}

			// method
			} else {				
	
				// Check if the function exists
				if( function_exists($tmp_handler) ){
				
					// Now run the handler
					call_user_func_array($tmp_handler, array( $response,$this ) );
				
				} else {
				
					// Check this out
					$this->response(
									array(
										"response"	=>	false,
										"message"	=>	"Function handler " . $tmp_class . "->" . $tmp_handler . " not found."
										)
									);
				
				// if
				}
				
			// if
			}	
			
		// We're good here
		} else {
		
			// Here we go
			return $response;
			
		// else
		}
						
	// method
	}
	
	/*
	@method: open( $options=NULL, $echo=TRUE )
	@description: Creates a new sole js handled form
	@params:
	@shortcode:  
	@return:
	*/ 
	public function open( $options=NULL, $echo=TRUE ){
	
		global $bento;
	
		$debug = (isset( $options["debug"]) && $options["debug"] ) ? $options["debug"] : NULL;
		$method = isset( $options["method"] ) ? $options["method"] : "post";
		$name = isset( $options["name"] ) ? $options["name"] : NULL;
		$action = isset( $options["action"] ) ? $options["action"] : NULL;
		$retrieve = isset( $options["retrieve"] ) ? $options["retrieve"] : "pull";
		
		// Ajax handling vs comet.
		$ajax = ( ((isset($options["ajax"]) && $options["ajax"]) || !isset($options["ajax"])) && (!isset($options["comet"]) || !$options["comet"] ) ) ? " " . $this->public->ajax  : "";
		$ajax .= ( (isset($options["validate"] ) && $options["validate"]) || !isset($options["validate"]) ) ? " " . $this->public->validate  : "";
		
		// HTML Stuffs
		$id = isset( $options["id"] ) ? $options["id"] : NULL;	
		$class = isset( $options["class"] ) ? $options["class"] . $ajax : $ajax;
		$target = isset( $options["target"] ) ? "target=\"" . $options["target"] . "\"" : NULL;
		
		// Bento specific
		$format = isset( $options["format"] ) ? $options["format"] : $this->public->format;
		$operation = isset( $options["operation"] ) ? $options["operation"] : false;
		$handler = isset( $options["handler"] ) ? $options["handler"] : NULL;
		$table = isset( $options["table"] ) ? $options["table"] : NULL;
		$criteria = isset( $options["criteria"] ) ? ("." . $options["criteria"]) : NULL;
		$redirect_onsuccess = isset( $options["onsuccess"] ) ? $options["onsuccess"] : NULL;
		$redirect_onfail = isset( $options["onfail"] ) ? $options["onfail"] : NULL;
		$redirect_oncomplete = isset( $options["oncomplete"] ) ? $options["oncomplete"] : NULL;
		$javascript = isset( $options["javascript"] ) ? $options["javascript"] : NULL;
		
		// File or text specific
		$enctype = isset( $options["file"] ) ? "enctype=\"multipart/form-data\"" : NULL;
		$directory = isset( $options["directory"] ) ?  $options["directory"] : NULL;
		$extensions = isset( $options["extensions"] ) ?  $options["extensions"] : NULL;
		$multiple = isset( $options["multiple"] ) ?  $options["multiple"] : NULL;
		$description = isset( $options["description"] ) ?  $options["description"] : NULL;
		$number = isset( $options["number"] ) ?  $options["number"] : NULL;
		$limit = isset( $options["limit"] ) ?  $options["limit"] : NULL;

		// Log how many forms we've opened
		$this->log[] = time();
	
		// Add the uploader library and css
		if( isset( $options["file"] ) ){
			
			// Now add the password confirmation
			$bento->add_css(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"uploader"
							)
						);
			
			// Now add the password confirmation
			$bento->add_js(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"uploader"
							)
						);
						
		// if
		}	

		// Go through the javascript stuff
		if( is_array( $javascript ) ){
				
			$javascript_onsuccess = isset( $javascript["onsuccess"] ) ? $javascript["onsuccess"] : NULL;
			$javascript_onfail = isset( $javascript["onfail"] ) ? $javascript["onfail"] : NULL;
			$javascript_oncomplete = isset( $javascript["oncomplete"] ) ? $javascript["oncomplete"] : NULL;
			$javascript_onsubmit = isset( $javascript["onsubmit"] ) ? $javascript["onsubmit"] : NULL;
			$javascript_onload = isset( $javascript["onload"] ) ? $javascript["onload"] : NULL;
	
		//if
		}

		// Check if we have anme
		if( $name == NULL ){

			$name = $this->public->form_name . "_" . str_replace(".","",microtime(true)) . "_" . count($this->log);

		}
		
		// Check if we have anme
		if( $id == NULL ){

			$id = $name;

		}

		// Check if we have a url
		if( $action == NULL ){

			$action = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . "/" . $bento->public->url . "?". $this->public->querystring . "&" . $bento->private->session_id  . "=" . session_id();

		// if
		}

		// Create our form
		$tmp_return = "<form method=\"" . $method . "\" id=\"" . $id . "\" name=\"" . $name . "\" action=\"" . $action . "\" " . $target . " class=\"" . trim( $class ) . "\" " . $enctype  . ">\r\n";

		// Check if this is post or not
		if( $method == "post" ){
			
			// Check it
			if( isset($options["file"]) && $options["file"] ){

				// It's an upload form
				$tmp_return .= "<input type=\"hidden\" name=\"" . $this->public->type . "\"  id=\"" . $id . "_" . $this->public->type . "\" value=\"" . $this->public->upload . "\">\r\n";				
				
			// if
			} else if( isset($options["autocomplete"]) && $options["autocomplete"] ){

				// It's an autocomplete form
				$tmp_return .= "<input type=\"hidden\" name=\"" . $this->public->type . "\"  id=\"" . $id . "_" . $this->public->type . "\" value=\"" . $this->public->autocomplete . "\">\r\n";				
				
			// It's a text form
			} else {

				// Set this as plain text
				$tmp_return .= "<input type=\"hidden\" name=\"" . $this->public->type . "\"  id=\"" . $id . "_" . $this->public->type . "\" value=\"" . $this->public->plaintext . "\">\r\n";				
				
			// if
			}
			
			// Check if we're debugging
			if( isset($debug) ){
	
				$tmp_return .= "<input type=\"hidden\" name=\"" . $this->public->debug . "\" id=\"" . $id . "_" . $this->public->debug . "\" value=\"true\">\r\n";
										
			// if
			}
			
			// This will deal with how results are retrieved
			$tmp_return .= "<input type=\"hidden\" name=\"" .  $this->public->retrieve . "\" id=\"" .  $this->public->retrieve . "_" . $id . "\" value=\"" . $retrieve . "\">\r\n";
	
			// This will deal with how long before the timeout
			$tmp_return .= "<input type=\"hidden\" name=\"" .  $this->public->timeout . "\" id=\"" .  $this->public->timeout . "_" . $id . "\" value=\"" . ($this->public->timeouts->{ $retrieve }*1000) . "\">\r\n";

			// Output the token
			$tmp_return .= "<input type=\"hidden\" name=\"" . $this->public->token_name . "\" value=\"" . $this->encrypt($this->private->token_value) . "\">\r\n";
	
			// Check if there's an operation
			if( $operation ){
			
				//Output the form operation
				$tmp_return .= "<input type=\"hidden\" name=\"" .  $this->public->operation. "\" id=\"" .  $this->public->operation . "_" . $id . "\" value=\"" . $this->encrypt( $operation ) . "\">\r\n";
	
			// if
			}
	
			// Check if there's an operation
			if( $table ){
			
				//Output the form operation
				$tmp_return .= "<input type=\"hidden\" name=\"" .  $this->public->table. "\" id=\"" .  $this->public->table . "_" . $id . "\" value=\"" . $this->encrypt( $table ) . "\">\r\n";
	
			// if
			}

			// Check if there's an operation
			if( $criteria ){
			
				//Output the form operation
				$tmp_return .= "<input type=\"hidden\" name=\"" .  $this->public->criteria. "\" id=\"" .  $this->public->criteria . "_" . $id . "\" value=\"" . $this->encrypt( $criteria ) . "\">\r\n";
	
			// if
			}
		
			// Create our handler
			if( isset($handler) && $handler != ""){
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $this->public->handler. "\" value=\"" . $this->encrypt($handler) . "\">\r\n";
			
			// if
			}
		
			//Check if we're writing the on success redirect page
			if( isset($redirect_onsuccess) ){
				
				$tmp_return .=  "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->redirect_onsuccess . "\" id=\"" . $id . "_" . $this->public->redirect_ . "\" value=\"" . $redirect_onsuccess . "\">\r\n";
			
			// if
			}
		
			// Check if we're writing the on failure redirect page
			if( isset($redirect_onfailure) ){
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->redirect_onfail . "\" id=\"" . $id . "_" . $this->public->redirect__onfail . "\" value=\"" . $redirect_onfail . "\">\r\n";
			
			// if
			}
			
			//Check if we're writing the on success redirect page
			if( isset($redirect_oncomplete) ){
				
				$tmp_return .=  "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->redirect_oncomplete . "\" id=\"" . $id . "_" . $this->public->javascript_oncomplete . "\" value=\"" . $redirect_oncomplete . "\">\r\n";
			
			// if
			}
			
			// Check if we need a javascript handler for onsuccessm
			if( isset($javascript_onsuccess) ){
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->javascript_onsuccess . "\"  id=\"" . $id . "_" . $this->public->javascript_onsuccess . "\" value=\"" . $javascript_onsuccess . "\">\r\n";
		
			// if
			}
		
			// Check if we need a javascript handler for onsuccess
			if( isset($javascript_onfail) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->javascript_onfail . "\"  id=\"" . $id . "_" . $this->public->javascript_onfail . "\" value=\"" .$javascript_onfail . "\">\r\n";
		
			// if
			}
			
			// Check if we need a javascript handler for onsuccess
			if( isset($javascript_oncomplete) ){
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->javascript_oncomplete . "\"  id=\"" . $id . "_" . $this->public->javascript_oncomplete . "\" value=\"" . $javascript_oncomplete . "\">\r\n";
		
			// if
			}
		
			// Check if we need a javascript handler for onsuccess
			if( isset($javascript_onsubmit) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->javascript_onsubmit . "\"  id=\"" . $id . "_" . $this->public->javascript_onsubmit . "\" class=\"" . $this->public->javascript_onsubmit . "\" value=\"" .$javascript_onsubmit . "\">\r\n";
		
			// if
			}

			// Check if we need a javascript handler for onsuccess
			if( isset($javascript_onload) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_" . $this->public->javascript_onload . "\"  id=\"" . $id . "_" . $this->public->javascript_onload . "\" class=\"" . $this->public->javascript_onload . "\" value=\"" .$javascript_onload . "\">\r\n";
		
			// if
			}
			
			// Extension of files we are allowed to upload
			if( isset($extensions) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_extensions\"  id=\"" . $name . "_extensions\" value=\"" . $extensions . "\">\r\n";
		
			// if
			}
	
			// Description of files we are allowed to upload
			if( isset($description) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_description\"  id=\"" . $name . "_description\" value=\"" . $description . "\">\r\n";
		
			// if
			}
			
			// Where the files get uploaded before the handler
			if( isset($directory) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_directory\"  id=\"" . $name . "_directory\" value=\"" . $directory . "\">\r\n";
		
			// if
			}
			
			// Number of files we can upload
			if( isset($number) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_number\"  id=\"" . $name . "_number\" value=\"" . $number . "\">\r\n";
		
			// if
			}
	
			// Individual size limit
			if( isset($limit) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_limit\"  id=\"" . $name . "_limit\" value=\"" . $limit . "\">\r\n";
		
			// if
			}
			
			// Individual size limit
			if( isset($multiple) ){	
				
				$tmp_return .= "<input type=\"hidden\" name=\"" . $name . "_multiple\"  id=\"" . $name . "_multiple\" value=\"" . $multiple . "\">\r\n";
		
			// if
			}
			
		// if
		}
		
		// Remove the junk
		$tmp_return = str_replace("    "," ",$tmp_return);
		$tmp_return = str_replace("   "," ",$tmp_return);
		$tmp_return = str_replace("  "," ",$tmp_return);
		
		// Return it
		return $echo ? print $tmp_return : $tmp_return;
	
	// method
	}
	
	/*
	@method: close( $echo=true )
	@description: Closes a from
	@params:
	@shortcode:  
	@return:
	*/ 
	public function close( $echo=true ){

		// Output the html
		$tmp_return = "</form>\r\n\r\n";
		
		return $echo ? print $tmp_return : $tmp_return;
	
	// method
	}

	/*
	@method: fieldname( $tf, $echo=false)
	@description: Creates an ciphered fieldname for interaction with databases
	@params:
	@shortcode:  
	@return:
	*/ 
	public function fieldname( $tf, $echo=false){
	
		//Output the form field name
		$tmp_return = $this->encrypt( trim( $tf ) );

		return $echo ? print $tmp_return : $tmp_return;
	
	// method
	}
	
	/*
	@method: post( $tf )
	@description: Deciphers a fieldname that's been posted for interfaction with databases
	@params:
	@shortcode:  
	@return:
	*/ 
	public function post( $tf ){
	
		// Here is the post	
		if( isset($_POST[ $this->encrypt( trim($tf) ) ]) ){
	
			$tmp = $_POST[ $this->encrypt( trim($tf) ) ];
			
			// Make sure it's no an array before we clean it up
			if( is_array($tmp) ){
			
				return $tmp;
				
			} else {
			
				return trim($tmp);
				
			// if
			}
	
		} else {
	
			return false;
	
		}
	
	// method
	}
	
	/*
	@method: text( $options=NULL,$echo=TRUE )
	@description: Creates a new text form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function text( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"text"); }

	/*
	@method: file( $options=NULL,$echo=TRUE )
	@description: Creates a new file form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function file( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"file"); }

	/*
	@method: hidden( $options=NULL,$echo=TRUE )
	@description: Creates a new hidden form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function hidden( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"hidden"); }

	/*
	@method: password( $options=NULL,$echo=TRUE )
	@description: Creates a new password form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function password( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"password"); }

	/*
	@method: confirm( $options=NULL,$echo=TRUE )
	@description: Creates a new confirm form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function confirm( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"confirm"); }

	/*
	@method: date( $options=NULL,$echo=TRUE )
	@description: Creates a new date form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function date( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"date"); }

	/*
	@method: time( $options=NULL,$echo=TRUE )
	@description: Creates a new time form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function time( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"time"); }

	/*
	@method: datetime( $options=NULL,$echo=TRUE )
	@description: Creates a new date and time form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function datetime( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"datetime"); }


	/*
	@method: textarea( $options=NULL,$echo=TRUE )
	@description: Creates a new textarea form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function textarea( $options=NULL,$echo=TRUE ) { $this->field( $options,$echo,"textarea"); }

	/*
	@method: file( $options=NULL,$echo=TRUE )
	@description: Creates a new filed
	@params:
	@shortcode:  
	@return:
	*/ 
	public function field( $options,$echo=TRUE,$type ){
	
		global $db,$bento;
	
		$name = isset( $options["name"] ) ? $options["name"] : NULL;
		$encode = isset( $options["encode"] ) ? $options["encode"] : true;
		$value = isset( $options["value"] ) ? $options["value"] : true;
		$required = ( isset( $options["required"] ) && trim($options["required"]) != "" ) ? $options["required"] : NULL;
		$required_a = ( isset( $options["required"] ) && trim($options["required"]) != "" ) ? " " . $this->public->required : NULL;
		$class = isset( $options["class"] ) ? $options["class"] . $required_a : $required_a;
		$id = isset( $options["id"] ) ? $options["id"] : NULL;
		$mask = isset( $options["mask"] ) ? $options["mask"] : NULL; 
		$autocomplete = isset( $options["autocomplete"] ) ? $options["autocomplete"] : NULL;
		$validate = isset( $options["validation"] ) ? "v=\"" . $options["validation"] . "\"" : NULL;	
		$validate = isset( $options["validate"] ) ? "v=\"" . $options["validate"] . "\"" : NULL;	
		$maxlength = isset( $options["maxlength"] ) ? "maxlength=\"" . $options["maxlength"] . "\"" : NULL;
		$size = isset( $options["size"] ) ? "size=\"" . $options["size"] . "\"" : NULL;	
		$readonly = ( isset( $options["readonly"])  && (bool)$options["readonly"] ) ? "readonly=\"" . $options["readonly"] . "\"" : NULL;	
		$html = isset( $options["html"] ) ? $options["html"] : false;
		$tabindex = isset( $options["tabindex"] ) ? " tabindex=\"" . $options["tabindex"] . "\"" : NULL;
		$placeholder = isset( $options["placeholder"] ) ? " placeholder=\"" . $options["placeholder"] . "\"" : NULL;

		// Check if it's a type or not
		if( $type == "password" ){
		
			// Reset the id
			$id = "password";
		
		} else if( $type == "confirm" ){
		
			// Change the type back
			$type = "password";

			// Set the id
			$id = "password_confirm";

			// Now add the password confirmation
			$bento->add_css(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"password"
							)
						);
			
			// Now add the password confirmation
			$bento->add_js(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"password"
							)
						);
						
		} else if( $type == "date" || $type == "time" || $type == "datetime" ){

			// Set the id
			$class .= " " . $type . " ";

			// Change the type back
			$type = "text";

			// Now add the password confirmation
			$bento->add_css(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"date"
							)
						);
			
			// Now add the password confirmation
			$bento->add_js(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"date"
							)
						);
						
		// if
		} else if( $type == "textarea" ){
			
			// Now add the password confirmation
			$bento->add_js(	
							array(
								"plugin"	=>	"form",
								"name"	=>	"textarea"
							)
						);
						
		// if
		}
		
		// Go through the javascript stuff
		if( is_array( $autocomplete ) ){
				
			$autocomplete_value = isset( $autocomplete["value"] ) ? "value: '" . $autocomplete["value"] . "', " : NULL;
			$autocomplete_text = isset( $autocomplete["text"] ) ? "text: '" . $autocomplete["text"] . "', " : NULL;
			$autocomplete_url = isset( $autocomplete["url"] ) ? "url: '" . $autocomplete["url"] . "', " : NULL;
			$autocomplete_function = isset( $autocomplete["function"] ) ? "function: '" . $autocomplete["function"] . "'," : NULL;
			$autocomplete_link = isset( $autocomplete["link"] ) ? "link: '" . $autocomplete["link"] . "'," : NULL;
			$autocomplete_width = isset( $autocomplete["width"] ) ? "width: '" . $autocomplete["width"] . "'" : NULL;			
			$autocomplete_handler = isset( $autocomplete["handler"] ) ? $autocomplete["handler"] : NULL;			
			$autocomplete = " autocomplete=\"off\" autocompleter=\"{" . $autocomplete_value . " " . $autocomplete_text . " " . $autocomplete_url . " " . $autocomplete_function . " " . $autocomplete_link . " " . $autocomplete_width . "}\"";
			
		//if
		} else {
		
			$autocomplete = NULL;
			
		// if
		}	
	
		// Check if there's a mask for this field
		if ( isset($mask) ) {
	
			// add the important stuff
			$mask = "type:'fixed', mask:'" . $mask . "', stripMask: true";
	
			// Output it
			$tmp_mask = "alt=\"{" . $mask . "}\"";
	
		} else {
	
			$tmp_mask = "";
	
		}
	
		// Check if this field is required or now'
		if( isset($required_text) && $required_text != "" ){
	
			$class .= " " . $this->public->required;
	
		}

		// Check if there's a mask on this
		if( !is_null($mask) ){

			$class .= " iMask";

		}
		
		// Check if there's a mask on this
		if( !is_null($autocomplete) ){

			$class .= " autocomplete";

		}
		
		// Check if there's a mask on this
		if( !is_null($validate) ){

			$class .= " " . $this->public->validate . " ";

		}
	
		// Show what should be displayed
		if( $required != NULL && trim($required) != "" ){
	
			$required = " " . $this->public->required . "=\"" . $required . "\"";
	
		}
	
		// Check if we're automatically filling in the value
		if( is_bool($value) && $value  && class_exists("db") ) {
	
			// Get the record
			$tmp_result = $db->record( $name  );
	
		// Fill in the value we want
		} else {
	
			// Check if we're filling in nothing
			if( is_bool($value) && !$value ){
	
				$tmp_result = "";
	
			} else {
	
				$tmp_result = $value;
	
			}
	
		}
	
		// Check if encoding is on
		if( $encode ){
		
			$name = $this->fieldname($name);
		
		// if
		}
	
		if( $id == NULL ){
	
			$id = $name;
	
		}
		
		// Check for htmlentited
		if( $html ){
		
			// Check it
			$tmp_result = htmlspecialchars($tmp_result);
		
		// if
		}
	
		//Output the form field name
		if( $type != "textarea" ){
		
		    $tmp_return = "<input type=\"" . $type . "\" " . $maxlength . " " . $size . " name=\"" . $name . "\" " . $autocomplete . " " . $validate . " " . $placeholder . " class=\"" . $class . "\" " . $required . " " . $tabindex . " " . $tmp_mask . " " . $readonly . " id=\"" . $id  . "\" value=\"" . $tmp_result . "\">\r\n";

		// else
		} else {
		
			//Output the form field name
			$tmp_return = "<textarea name=\"" . $name . "\" " . $maxlength . " 	" . $autocomplete . " class=\"" . $class . "\" " . $required . " " . $tmp_mask . " " . $placeholder . " " . $readonly . " id=\"" . $id  . "\" " . $tabindex . ">" . $tmp_result . "</textarea>\r\n";

		// if
		}

		// check if this is an autocompleter and has a handler
		if( isset($autocomplete_handler) && !is_null($autocomplete_handler) ){

			$this->hidden(
					array(
						"name"	=>	$this->public->handler,
						"encode"	=>	false,
						"value"	=>	$autocomplete_handler,
						"autocomplete"	=>
							array(
								"link"	=>	true /* This links it to the above autocompleter */
								)
						)
					);		
		
		// if
		}
		
		// Remove the junk
		$tmp_return = str_replace("    "," ",$tmp_return);
		$tmp_return = str_replace("   "," ",$tmp_return);
		$tmp_return = str_replace("  "," ",$tmp_return);
	
		// return the select field
		return $echo ? print $tmp_return : $tmp_return;		
	
	// method
	}
	
	/*
	@method: select( $options=NULL,$echo=TRUE )
	@description: Creates a new select form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function select( $options=NULL,$echo=TRUE ) {
	
		global $db,$bento;
	
		$name = isset( $options["name"] ) ? $options["name"] : NULL;
		$recordset = isset( $options["recordset"] ) ? $options["recordset"] : NULL;
		$encode = isset( $options["encode"] ) ? $options["encode"] : true;
		$values = isset( $options["values"] ) && is_array( $options["values"] ) ? $options["values"] : (!is_null( $recordset ) ? $db->recordset( $recordset ) : $db->recordset( $name ));
		$value = isset( $options["value"] ) ? $options["value"] : "id";
		$text = isset( $options["text"] ) ? $options["text"] : "name";
		$selected = isset( $options["selected"] ) ? $options["selected"] : "";
		$required = ( isset( $options["required"]) && trim($options["required"]) != "" ) ? $options["required"] : NULL;
		$required_a = (isset( $options["required"]) &&  trim($options["required"]) != "" ) ? $this->public->required : NULL;
		$class = isset( $options["class"] ) ? trim($options["class"] . " " . $required_a) : $required_a;
		$id = isset( $options["id"] ) ? $options["id"] : NULL;
		$size = isset( $options["size"] ) ? $options["size"] : 1; 
		$size = ( $size > 1 ) ? "size=\"" . $size . "\"" : "";
		$multiple = isset( $options["multiple"] ) ? $options["multiple"] : false;
		$multiple = ( $multiple ) ? "mutiple=\"true\"" : "";
		$default = isset( $options["default"] ) ? $options["default"] : NULL;
		$alt = isset( $options["alt"] ) ? " alt=\"" . $options["alt"] . "\"" : NULL;
		
		$disabled = ( isset( $options["readonly"])  && (bool)$options["readonly"] ) ? "disabled=\"true\"" : NULL;	
		//$disabled = ( isset( $options["disabled"])  && (bool)$options["disabled"] ) ? "disabled=\"true\"" : NULL;	


		// Now add the password confirmation
		$bento->add_js(	
						array(
							"plugin"	=>	"form",
							"name"	=>	"select"
						)
					);
		
		// In the event nothing has been passed in as selected
		$tmp_select = "";
		
		// Go through the javascript stuff
		if( is_array( $default ) ){
		
			$default_text = isset( $default["text"] ) ? $default["text"] : NULL;
			$default_value = isset( $default["value"] ) ? $default["value"] : NULL;
			
		//if
		}
				
		// get the table and name to autovalue if no values entered
		if(  strstr($name,".") ){
		
			// Get the table and field name
			$tf = explode(".",$name);
			
		// if
		}
		
		// Get the table name to check if there's an open recordset
		if( count($values) == 0 && isset($tf[0])){
		
			// Check if there's an open recordset
			if( $db->recordset( $tf[0] ) > 0 ){
			
				// Set the values equal to the open recordset
				$values = $db->recordset( $tf[0] );
			
			// if
			}
			
		// if
		}
	
		//Create a array for selected values
		if( !is_array($selected) && strstr($selected,",") ){

			$selected = explode(",",$selected);

		} else {

			// Check if we already have an open recordset
			if( $selected == ""){

				// check if we have an open recordset
				if( count($db->recordset( $name )) > 0 ){
			
					// Check if we have a single record
					if( count($db->recordset( $name )) == 1 ){
					
						// we have a single value we need to turn into an array we can use
						$selected = array( $db->record( $name ) );
					
					// We have an array of values we can use
					} elseif( count($db->recordset( $name )) > 1 ){
					
						$selected = $db->recordset( $name );
					
					// if
					}
				
				// We've got nothing to work with
				} else {
				
					$selected = array();
					
				// if
				}
			
			// We've passed a value in, let's do something with it
			} else {

				// Check if its an array already
				if( !is_array( $selected )){
	
					$selected = array( $selected );
				
				// if
				}	
				
			// if
			}

		// if
		}

		// Check if we're encoding the name
		if( $encode ){
		
			$fieldname = $this->fieldname($name);
			
		} else {
		
			$fieldname = $name;
		
		}		
	
		// If the size is greater than 1 make it a mutile selection
		if( $multiple != false ){

			$fieldname = $fieldname . "[]";
	
		// if
		}
	
		// Check for id
		if( $id == NULL ){
	
			$id = str_replace("[]","",$fieldname);
	
		//
		}
		
		// Multiple
		$multiple = $multiple ? "multiple" : "";
	
		// Show what should be displayed
		if( $required != NULL && trim($required) != "" ){
	
			$required = " " . $this->public->required . "=\"" . $required . "\"";
	
		}
	
		//Output the form field name
		$tmp_return = "<select name=\"" . $fieldname . "\" id=\"" . $id . "\" class=\"" . $class . "\" " . $required . " " . $multiple . " " . $size . " " . $alt . " " . $disabled . ">";
	
		// Check if we've got an initial value
		if( isset($default_text) ){
		
			if( in_array($default_value, $selected) ){
			
				$tmp_select = "selected=\"selected\"";
				
			} else {
	
				$tmp_select = "";
				
			// if
			}
			
			$tmp_return .= "<option value=\"" . $default_value . "\" " . $tmp_select . ">" . $default_text . "</option>";			
	
		// if
		}		
	
		//Loop through the array to create the fields
		foreach($values as $recordset){
	
			// Check if there is a value in our recordsets to output
			if( isset($recordset[$value]) ){
	
				// Set it
				$tmp_value = "value=\"" . $recordset[$value] . "\"";
	
				//Check if the value is selected
				if( in_array($recordset[$value],$selected ) ){
	
					$tmp_select = "selected";
	
				} else {
	
					$tmp_select = "";
	
				}
	
			//
			} else {
	
				$tmp_value = "";
	
			}
	
			// Loop through the values if coma delimited
			if( strstr($text,",") ){
	
				$tmp_text_array = explode(",",$text);
	
			} else {
	
				$tmp_text_array = array($text);
	
			//if
			}
	
			// Initially set the value
			$tmp_text = "";
	
			// Loop through the values to create the output
			foreach($tmp_text_array as $tmp_field){
	
				// Check for the text output now
				if( isset($recordset[$tmp_field]) ){
	
					$tmp_text .= $recordset[$tmp_field] . " ";
	
				} else {
	
					$tmp_text .= "";
	
				}
	
			// foreach
			}
	
			$tmp_return .= "<option " . $tmp_value . " " . $tmp_select . ">" . $tmp_text . "</option>\r\n";
	
		//foreach
		}
	
		$tmp_return .= "</select>";
	
		// return the select field
		return $echo ? print $tmp_return : $tmp_return;
	
	// method
	}

	/*
	@method: checkbox( $options=NULL,$echo=TRUE )
	@description: Creates a checkbox form field
	@params:
	@shortcode:  
	@return:
	*/ 
	public function checkbox( $options=NULL,$echo=TRUE ) {
	
		global $db;
	
		$name = isset( $options["name"] ) ? $options["name"] : NULL;
		$encode = isset( $options["encode"] ) ? $options["encode"] : true;
		$checked = "";

		// Check the value
		if( $db->record( $name ) == 1 ){
			
			$checked = "checked=\"checked\"";
			
		// if
		}
		
		// Check if we're encoding the name
		if( $encode ){
		
			$fieldname = $this->fieldname($name);
			
		} else {
		
			$fieldname = $name;
		
		}

		// This is what we're returning
		$tmp_return = "<input type=\"hidden\" name=\"" . $fieldname . "\" value=\"0\">";

		// This is what we're returning
		$tmp_return .= "<input type=\"checkbox\" name=\"" . $fieldname . "\" " . $checked . " value=\"1\">";
	
		// return the select field
		return $echo ? print $tmp_return : $tmp_return;
	
	// method
	}
	
	/*
	@method: captcha( $challenge=true )
	@description: Is s third party captcha. For more see recaptchalib.php
	@params:
	@shortcode:  
	@return:
	*/ 
	public function captcha( $challenge=true ){
	
		global $bento;
	
		// response
		if( !$challenge ){
	
			// Include the recaptcha library
			$bento->add_php(
						array(
							"plugin"	=>	"form",
							"name"	=>	$this->private->recaptcha
						)
					);

			$resp = recaptcha_check_answer ( 
										$this->private->private ,
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]
										);
			
			if ( !$resp->is_valid ) {
			
				// What happens when the CAPTCHA was entered incorrectly
				form::response("false","The reCAPTCHA wasn't entered correctly.", array("error"	=> $resp->error, "javascript"	=>	"bento.plugginit.message({'text':bento.form.response.message}); Recaptcha.reload();" ) );
			
			} else {

				return true;

			}

		// challenge (form)
		} else {
		
			require_once( $this->captcha->library );
			
			# the response from reCAPTCHA
			$resp = null;
			# the error code from reCAPTCHA, if any
			$error = null;
			
			# was there a reCAPTCHA response?
			if ( isset($_POST["recaptcha_response_field"]) ) {
					$resp = recaptcha_check_answer ( $this->captcha->private,
													$_SERVER["REMOTE_ADDR"],
													$_POST["recaptcha_challenge_field"],
													$_POST["recaptcha_response_field"]);
			
					if ($resp->is_valid) {
							echo "You got it!";
					} else {
							# set the error code so that we can display it
							$error = $resp->error;
					}
			}
			
			echo recaptcha_get_html( $this->private->public, $error);
		
		}
	
	// method
	}
	
	/*
	@method: response( $response,$message="",$variables="",$type="json" )
	@description: Outputs a json response
	@params:
	@shortcode:  
	@return:
	*/ 
	public function response( $options ){
	
		global $encryption;
	
		// Check this out
		if( !is_array($options) ){ $options = array("response"	=>	$options); }
		
		// Set this up
		$response = isset($options["response"]) ? $options["response"] : false;
		$message = isset($options["message"]) ? $options["message"] : "";
		$variables = isset($options["variables"]) ? $options["variables"] : array();
		$actions = isset($options["actions"]) ? $options["actions"] : array();
		$format = isset($options["format"]) ? $options["format"] : $this->public->format;
		
		// Check it out
		if( !is_array($actions) ){ 
		
			$actions = array($actions); 
			
		// Turn the arrat into a reponse	
		}
		
		// Check if response is json or xml
		if( $format == "json" ){
	
			$tmp_response = json_encode(
										array(
											"response"	=>	(bool)$response, 
											"message"	=>	$message, 
											"variables"	=>	$variables,
											"actions"	=>	$actions
											)
										);
		
		// XML	
		} else if( $format == "xml" ){
		
			// Write out an xml header
			header ("content-type: text/xml");

			// Creates XML string with response and message
			$xml = new SimpleXMLElement("<response >"); 
			$xml->addAttribute('response', $response);
			$xml->addAttribute('message', $message);

			// Generate the variables
			form::xml( $variables, "variables", $xml );

			// Here is the response
			$tmp_response = $xml->asXML();

		// if
		}
		
		// Compress if possible
		//if( extension_loaded('zlib') ){ob_start('ob_gzhandler');}

			// Output the message
			print_r( $tmp_response );
					
		// if
		//if( extension_loaded('zlib') ){ob_end_flush();}

		// No need to go any further		
		die();
	
	// method
	}
		
	/*
	@method: xml($data, $rootNodeName = 'variables', $xml=null)
	@description: Converts variables of a response to xml
	@params:
	@shortcode:  
	@return:
	*/ 
	private function xml($data, $rootNodeName = 'variables', $xml=null){
	
		if( !is_null($data) && is_array($data) ){
	
			// loop through the data passed in.
			foreach( $data as $key => $value ){
			
				// no numeric keys in our xml please!
				if( is_numeric($key) ){
				
					// Make this oject an array
					$key = "array";
				
				// if
				}
				
				// replace anything not alpha numeric
				$key = preg_replace('/[^a-z0-9_]/i', '', $key);
				
				// if there is another array found recrusively call this function
				if ( is_array($value) ){
				
					// $value = preg_replace('/[^a-z0-9_]/i', '', $value);
				
					// Add a new node to the parent
					$node = $xml->addChild($key);
					
					// Move down to the child
					self::xml($value, $rootNodeName, $node);

				// This is a single entry
				} else {

					// add single node.
					$value = htmlentities($value);

					$xml->addChild($key,$value);
					
				// if
				}
			
			// foreach
			}
		
		// if
		}
		
		// pass back as string. or simple xml object if you want!
		return;
	
	// method
	}

	/*
	@method: encrypt( $value )
	@description: Will encrypt the field names
	@params:
	@shortcode:  
	@return:
	*/ 
	public function encrypt( $value ){
		
		global $encryption;
		
		// We're going to use encrpy with this session id for form fields
		return $encryption->encrypt( $value, $_SERVER['REMOTE_ADDR'] );
		
	// method
	}

	/*
	@method: decrypt( $value )
	@description: Will decrypt the field names
	@params:
	@shortcode:  
	@return:
	*/ 
	public function decrypt( $value ){
		
		global $encryption;
		
		// We're going to use encrpy with this session id for form fields
		return $encryption->decrypt( $value, $_SERVER['REMOTE_ADDR'] );
		
	// method
	}

	/*
	@method: submitting()
	@description: Check if submitting or not
	@params:
	@shortcode:  
	@return:
	*/ 
	public function submitting(){
	
		return isset($_GET[ $this->public->querystring ]);

	// method
	}

	/*
	@method: add_event( $action )
	@description: Adds an event to the file system for comet submissions
	@params:
	@shortcode:  
	@return:
	*/
	public function add_event( $session_id=NULL ){
		
		global $file;
		
		// If one is not set
		if( is_null($session_id) ){ $session_id = session_id(); }
	
		// Insert the event to the file system
		$file->write("bento/form/tmp/events/" . $session_id,"");
	
	// method
	}
	
	/*
	@method: delete_event( $action )
	@description: Removes from the database for a session for push events
	@params:
	@shortcode:  
	@return:
	*/
	public function delete_event( $session_id=NULL ){
		
		global $file;
		
		// If one is not set
		if( is_null($session_id) ){ $session_id = session_id(); }
				
		// Insert the event to the file system
		if( $file->exists("bento/form/tmp/events/" . $session_id,"") ){
			
			// Remove the file
			$file->unlink("bento/form/tmp/events/" . $session_id);
		
			// Let it know what's up
			return true;
		
		// There's nothing going on here	
		} else {
		
			return false;	
			
		// if
		}

	// method
	}
	
	/*
	@method: has_event( $action )
	@description: Checks if there is an event for comet submissions
	@params:
	@shortcode:  
	@return:
	*/
	public function has_event( $session_id=NULL ){
		
		global $file;
		
		// If one is not set
		if( is_null($session_id) ){ $session_id = session_id(); }
	
		// Remove the event
		return $this->delete_event( $session_id );
	
	// method
	}
	
	/*
	@method: is_comet()
	@description: Check if it's a push or pull response 
	@params:
	@shortcode:  
	@return:
	*/
	public function is_pull(){
	
		// Check if the form is both a commet form and still set to push
		return $_POST[ $this->public->retrieve ] == "pull";

	// method
	}

	/*
	@method: is_push()
	@description: Check if it's a push or pull response 
	@params:
	@shortcode:  
	@return:
	*/
	public function is_push(){
	
		// Check if the form is both a commet form and still set to push
		return $_POST[ $this->public->retrieve ] == "push";

	// method
	}
	
	/*
	@method: is_cancel()
	@description: Checks if it's a cancellation of a push request 
	@params:
	@shortcode:  
	@return:
	*/
	public function is_cancel(){
	
		// Check if the form is both a commet form and still set to push
		return isset( $_POST[ $this->public->cancel ] );

	// method
	}

// class
}
?>