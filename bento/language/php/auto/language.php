<?php
/*
@class: language
@description: translates text on the fly
@params:
*/ 
class language{

	/*
	@method: __construct()
	@description: Assign class variables from the database
	@params:
	@shortcode:  
	@return:
	*/ 
	public function __construct(){
	
		global $bento;
		
		// Add the shortcodes
		$bento->add_shortcode("<!--language:translate:([^>]+|)-->");
		
		// Add the google translate library
		$bento->add_php(
						array(
							"plugin"	=>	"language",
							"name"	=>	"GoogleTranslate"
						)
					);

		// Check it
		$GLOBALS["googleTranslate"] = new GoogleTranslate();
		
	// method
	}

	/*
	@method: __configure()
	@description: Set some more variable
	@params:
	@shortcode:  
	@return:
	*/ 
	public function __configure(){
	
		global $db,$bento;
		
		// Check if this is setup yet
		if( !$db->table_exists("language") ){
		
			return false;
		
		// if
		}
		
		// Here you go
		//$this->public->selected = "english";
		
		// Herer you go
		return true;
		
	// method
	}

	/*
	@method: __load()
	@description: Load up the variables
	@params:
	@shortcode:  
	@return:
	*/ 
	public function __load(){
	
		global $db;
	
		// Assign the language
		if( !isset($_SESSION["bento"]["language"]["selected"])){
		
			// Set the language
			$this->set( $this->public->selected );
	
		// if
		}
		
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
		$language = $this;
		
		// Let's look for some buttons		
		preg_match_all("@" . $shortcode . "@",$html,$tmp);
		
		// Check which one it if
		if( stristr($shortcode,"language:translate:") ){

			// Loop through it
			foreach( $tmp[1] as $verbiage ){
			
				// Check it
				if( $verbiage != "" ){
				
					// Check if we're in the original or not
					if( !$this->original() ){
										
						// Translate things
						$db->select(
								array(
									"table"	=>	"translations.original=" . $verbiage . " and language.name=" . $this->selected(),
									"join"	=>	"language" 
									)
								);
					
						// Make sure we have a translation
						if( $db->recordcount("translations") > 0 ){
						
							$html = str_replace("<!--language:translate:" . $verbiage . "-->",$db->record("translations.translation"),$html);
								
						// if
						} else {
						
							// Get the original language
							$db->select("language.name=" . $this->public->selected . " or language.name=" . $this->selected() );
							
							// Filter in the right records
							$db->filter("language.name=" . $this->public->selected );
								
							// Get the original language
							$from = $db->record("language.code");
								
							// Filter it in
							$db->filter("language.name=" . $this->selected()	);
							
							// Get the original language
							$to = $db->record("language.code");
							$i = $db->record("language.id");
							
							// Translate the text		
							$translation = $this->text(
														array(
															"from"	=>	$from,
															"to"	=>	$to,
															"text"	=>	$verbiage
														)
													);
													
							if( $translation ){
							
								// There you go	
								$html = str_replace("<!--language:translate:" . $verbiage . "-->",$translation,$html);
		
								// Insert it into the database
								$db->insert(
											array(
												"table"	=>	"translations",
												"values"	=>	array(
																	"language_id"	=>	$i,
																	"original"	=>	$verbiage,
																	"translation"	=>	$translation
																)
													)
												);
													
							// Didn't get a translation, revert to original
							} else {

								// There is the problem							
								$html = str_replace("<!--language:translate:" . $verbiage . "-->",$verbiage,$html);

							// if
							}
	
						// if
						}

						// Clear it up
						$db->clear("language");
						
					} else {
		
						$html = str_replace("<!--language:translate:" . $verbiage . "-->",$verbiage,$html);
					
					// if
					}
					
				// if
				}
				
				$db->clear("translations");
				
			// foreach
			}
			
		// if
		}
		
		return $html;
	
	// method
	}

	/*
	@method: set( $language )
	@description: Sets the language
	@params:
	@shortcode:  
	@return:
	*/ 
	public function set( $language ){
	
		global $db;
		
		// Get the language and code
		$db->select("language.name=" . $language );
	
		// Check to make sure we're good
		if( $db->recordcount("language") > 0 ){
			
			// Assign the language
			$_SESSION["bento"]["language"]["selected"]["name"] = $db->record("language.name");
			$_SESSION["bento"]["language"]["selected"]["code"] = $db->record("language.code");
			
			// clear it up
			$db->clear("language");
	
			// Return a positive if we need it
			return true;
	
		// if
		} 
		
		// clear it up
		$db->clear("language");
		
		return false;
		
	// method
	}


	/*
	@method: selected( $type="name", $echo=false )
	@description: Returns or echos what language is selected
	@params:
	@shortcode:  
	@return:
	*/ 
	public function selected( $type="name", $echo=false ){

		// Return it
		$tmp_return = $_SESSION["bento"]["language"]["selected"][ $type ];

		// Return it
		return $echo ? print $tmp_return  : $tmp_return;

	// method
	}

	/*
	@method: code( $echo=false )
	@description: This doesn't do anything yet
	@params:
	@shortcode:  
	@return:
	*/ 
	public function code( $echo=false ){

	// method
	}

	/*
	@method: original()
	@description: Checks if we're in the original language
	@params:
	@shortcode:  
	@return:
	*/ 
	public function original(){

		return $this->public->selected == $_SESSION["bento"]["language"]["selected"]["name"];

	// method
	}

	/*
	@method: translate( $html )
	@description: This will translate anything that needs it
	@params:
	@shortcode:  
	@return:
	*/ 
	public function translate( $html ){
	
		global $db;

		// Find all the replacement text			
		preg_match_all("@<!--language:translate([^>]+|)-->@",$html,$tmp);
		
		// Loop through it
		foreach( $tmp[1] as $verbiage ){
		
			// Make sure we have a translation
			if( isset($language[ $verbiage ]) ){
			
				$html = str_replace("<!--language:translate" . $verbiage . "-->",$language[ $verbiage ],$html);
			
			} else if( isset($language[strtolower($verbiage)]) ){
			
				$html = str_replace("<!--language:translate" . $verbiage . "-->",$language[strtolower($verbiage)],$html);
			
			// if
			} else {

				$html = str_replace("<!--language:translate" . $verbiage . "-->",$verbiage,$html);
			
			// if
			}
		
		// foreach
		}
		
		// Just do it
		return $html;

	// method
	}

	/*
	@method: text( $variables )
	@description: This will translate via google
	@params:
	@shortcode:  
	@return:
	*/ 
	public function text( $variables ){
	
		global $googleTranslate;

		// The to and the from
		$from = ( isset($variables["from"]) ) ? $variables["from"] : false;
		$to = ( isset($variables["to"]) ) ? $variables["to"] : false;
		$text = ( isset($variables["text"]) ) ? $variables["text"] : false;
							
		// Retun if something isn't set
		if( !$from || !$to || !$text ){
		
			return false;

		
		// if
		}
								
		// Translate	
		$googleTranslate->set($from,$to,$text); 
		$translation = $googleTranslate->translate(); 
		
		// Just do it
		return $translation;

	// method
	}

	/*
	@method: html( $variables )
	@description: Translates HTML (as good as possible)
	@params:
	@shortcode:  
	@return:
	*/ 
	public function html( $variables ){

		// The to and the from
		$from = ( isset($variables["from"]) ) ? $variables["from"] : false;
		$to = ( isset($variables["to"]) ) ? $variables["to"] : false;
		$html = ( isset($variables["html"]) ) ? $variables["html"] : false;
							
		// Retun if something isn't set
		if( !$from || !$to || !$html ){
		
			return false;
		
		// if
		}
		
		// Get the text from the html
		foreach( explode("\r",strip_tags($html)) as $text){
		
			// Check it out
			if( trim($text) != "" ){
			
				// Remove the translation
				$translation = $this->text(
											array(
												"from"	=>	$from,
												"to"	=>	$to,
												"text"	=>	trim($text)
											)
										);
										
				// Make sure we got a result
				if( $translation ){
				
					// Replace the old with the new
					$html = str_replace(trim($text),$translation,$html);
				
				// if
				}

			// if
			}
		
		// foreach
		}
		
		return $html;

	// method
	}

	/*
	@method: translate_array( $array,$field )
	@description: Translated a recordset (if applicable)
	@params:
	@shortcode:  
	@return:
	*/ 
	public function translate_array( $array,$field ){
	
		global $db;

		// Make sure there's a recordset
		if( $array[0][ $field ] ){
		
			// Loop through the array
			foreach( $array as $i => $value ){
			
				$array[ $i ][ $field ] = "<!--language:translate" . $array[ $i ][ $field ] . "-->";
			
			// foreach
			}
			
			return $array;
		
		// Check it out
		} else {
		
			return array();
			
		// if
		}
		
	// method
	}
	
	/*
	@method: translate_records( $table,$field )
	@description: Translated a recordset (if applicable)
	@params:
	@shortcode:  
	@return:
	*/ 
	public function translate_records( $table,$field ){
	
		global $db;

		// Make sure there's a recordset
		if( $db->record( $table . "." . $field ) ){
		
			// Get the users
			foreach( $db->private->tables["record"] as $i => $record ){
			
				if( !stristr($db->private->tables["record"][ $i ]["value"][ $field ],"<!--language:translate") ){
			
					$db->private->tables["record"][ $i ]["value"][ $field ] = "<!--language:translate" . $db->private->tables["record"][ $i ]["value"][ $field ] . "-->";
			
				// if
				}
			
			// foreach
			}
			
			return $db->recordset( $table );
		
		// Check it out
		} else {
		
			return array();
			
		// if
		}
		
	// method
	}
	
// class
}
?>