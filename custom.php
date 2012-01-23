<?php
/*
@class: custom
@description: A place to put custom functions if you need them
@params:
@shortcode:  
@return:
*/
class custom {

	// Load the variables 
	public $public;
	public $private;
	
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
		$bento->add_shortcode("<!--custom:shortcut-->");
		
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
		$custom = $this;
		
		// Let's look for some buttons		
		preg_match_all("@" . $shortcode . "@",$html,$tmp);
				
		// Check which one it if
		if( stristr($shortcode,"custom:shortcut") && isset($tmp[0][0]) ){

			// Start the output buffer
			ob_start();
		
			// Get the feed
			$this->shortcut();
		
			// Take the contents from the php files		
			$breadcrumb = ob_get_contents();
			ob_end_clean();
			
			// Replace it
			$html = str_replace("<!--custom:shortcut-->",$breadcrumb,$html);
		
		// if	
		}
		
		// Return the html
		return $html;
	
	// method
	}

	/*
	@class: shortcut
	@description: example of custom shortcode
	@params:
	@shortcode:  
	@return:
	*/
	public function shortcut(){
		
		// You could put some stuff here if you wanted to 
	
	// method
	} 

// class
}
?>