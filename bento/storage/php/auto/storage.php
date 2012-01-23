<?php
/*
@class: storage
@description: stores thing on the client end so they don't have to get loaded again (domready)
@params:
*/
class storage{

	/*
	@method: store()
	@description: Store something in a session
	@params:
	@shortcode:  
	@return: true always
	*/
	public function store( $options ){
		
		// Check for all the stuff we need
		if( !isset($options["plugin"]) ){ return false; }
		if( !isset($options["name"]) ){ return false; }
		if( !isset($options["value"]) ){ return false; }
		
		// Set it up
		$_SESSION["bento"][ $options["plugin"] ][ $options["name"] ] = $options["value"];
		
		// return it
		return true;
		
	// method
	}

	/*
	@method: stored()
	@description: Check if something is stored and return it
	@params:
	@shortcode:  
	@return: value or false if none
	*/	
	public function stored( $options ){

		// Check for all the stuff we need
		if( !isset($options["plugin"]) ){ return false; }
		if( !isset($options["name"]) ){ return false; }
		
		// Set it up
		if( isset( $_SESSION["bento"][ $options["plugin"] ][ $options["name"] ] ) ){
			
			return $_SESSION["bento"][ $options["plugin"] ][ $options["name"] ];
			
		} else {
			
			return false;
			
		// if
		}
		
	// method
	}

	/*
	@method: reserve()
	@description: Check if it's stored, if not store it, otherwise return false
	@params:
	@shortcode:  
	@return: true if not stored, then store / false if previously stored
	*/		
	public function reserve( $options ){
		
		// Check for all the stuff we need
		if( !isset($options["plugin"]) ){ return false; }
		if( !isset($options["name"]) ){ return false; }
		if( !isset($options["value"]) ){ return false; }

		// Set it up
		if( !isset( $_SESSION["bento"][ $options["plugin"] ][ $options["name"] ] ) ){
			
			// Set it up
			$_SESSION["bento"][ $options["plugin"] ][ $options["name"] ] = $options["value"];
			
			// Otherwsie
			return true;
			
		} else {
			
			return false;
			
		// if
		}
		
	// method
	}

	/*
	@method: remove()
	@description: Remove something from a storage container
	@params:
	@shortcode:  
	@return: true, always
	*/
	public function remove(){
		
		// Check for all the stuff we need
		if( !isset($options["plugin"]) ){ return false; }
		if( !isset($options["name"]) ){ return false; }
		
		// Set it up
		if( isset( $_SESSION["bento"][ $options["plugin"] ][ $options["name"] ] ) ){
			
			// Set it up
			unset($_SESSION["bento"][ $options["plugin"] ][ $options["name"] ]);
			
		}	
		
		// Otherwsie
		return true;
		
	// method
	}
	
	/*
	@method: emp()
	@description: Empty a store container
	@params:
	@shortcode:  
	@return: true, always
	*/	
	public function emp(){
		
		// Check for all the stuff we need
		if( !isset($options["plugin"]) ){ return false; }
		if( !isset($options["name"]) ){ return false; }	

		// Set it up
		if( isset( $_SESSION["bento"][ $options["plugin"] ] ) ){
			
			// Set it up
			$_SESSION["bento"][ $options["plugin"] ] = array();
			
		// if
		}
		
		return true;
		
	// method
	}		

	/*
	@method: li()
	@description: List all the variables in a plugin container
	@params:
	@shortcode:  
	@return:
	*/
	public function li(){
		
		// Check for all the stuff we need
		if( !isset($options["plugin"]) ){ return false; }

		// Set it up
		if( isset( $_SESSION["bento"][ $options["plugin"] ] ) ){
			
			// Set it up
			return $_SESSION["bento"][ $options["plugin"] ];
			
		// if
		}
		
		return array();
		
	// method
	}

// class
}    
?>