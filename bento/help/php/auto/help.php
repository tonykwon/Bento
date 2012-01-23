<?php
/*
@class: help
@description: extracts help from comments on classes and methods
@params:
*/
class help{

	/*
	@method: __load()
	@description: Will load help if asked for
	@params:
	@shortcode:
	@return:
	*/	
	public function __load(){

		// Check it
		if( isset($_GET["bento_help"]) ){
		
			// Break up the request
			$tmp = explode(",",$_GET[ $this->private->querystring ]);
			
			// Get the help
			$this->topic( array("class"	=>	$tmp[0],	"method"	=>	$tmp[1] ) );
			
			die();
		
		// if
		}
	
	// method
	}

	/*
	@method: topic()
	@description: Returns help (comments) for a class method
	@params:
			$class (string): The class
			$method (string): The method (function)
	@shortcode:
	@return:
	*/	
	public function topic( $options ){
	
		// Make sure everything is setup
		if( !isset($options["class"]) || !isset($options["method"]) ){ return false; }
		
		// Set it up
		$c = $options["class"];
		$m = $options["method"];
	
		// Include the class
		global $file,$$c;
		
		// Check it out
		if( is_null($m) && class_exists($c) ){
		
			echo "<pre>";
			echo implode("<br >",get_class_methods($c));
			echo "</pre>";
		
		// if
		} else if( !is_null($m) && class_exists($c) && method_exists($$c,$m) ){
		
			// Now let's read it
			$contents = file_get_contents( $$c->file );
		
			// Let's look for some buttons		
			preg_match_all("@/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/@",$contents,$tmp);
		
			// Check if we have both the toc and the header comment
			foreach( $tmp[0] as $method ){
			
				// Check if this is the class
				if( stristr($method,$m) ){
					
					$h = $method; 
					break;
					
				// if
				}
				
			// foreach
			}
			
			echo "<pre>";
			
// Now we're going to clean it up
echo htmlentities(str_replace("\t","",$h)); 
					
			echo "</pre>";
			
		// if
		}
		
		return false;
	
	// method
	}

	/*
	@method: list()
	@description: Creates a listing of help for a class, and makes it clickable
	@params:
			$class (string): The class
	@shortcode:
	@return:
	*/	
	public function listing( $c, $m=NULL ){

		// Check it out
		if( is_null($m) && class_exists($c) ){
		
			echo "<pre>";
			
			// Loop through the methods
			foreach( get_class_methods($c) as $m ){
		
				echo "<a href='?" . $this->private->querystring . "=" . $c . "," . $m . "'>" . $m . "</a>\r\n";
		
			// foreach
			}	

			// Check it
			echo "</pre>";
		
		// if
		}

	// method
	}

// class
}
//-------------------------------------------------------------------------------//	
?>