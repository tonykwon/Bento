<?php
/*
@class: message
@description: Uses a friendly javascript messaging system and hijacks alerts
@params:
*/
class message{

		// Load the variables 
		public $public;

        /*
        @method: __load
        @description: Sets up any messages php is generating
        @params:
        @shortcode:  
        @return:
        */
        public function __load(){
			
			// Here you go
			$this->public->visible = false;
			$this->public->messages = array();
			
		// method
		}

        /*
        @method: tip
        @description: Uses the messaging system, converts inline to javascript popup
        @params:
        @shortcode:  
        @return:
        */
        public function tip( $options, $echo=true ){
        
                // Check it
                if( !is_array($options) ){
                
                        $options = array("text" =>      $options);
                        
                // if
                }
                
                // Check it out
                if( !isset($options["delay"]) ){ $options["delay"] = 0; }
                if( !isset($options["timeout"]) ){ $options["timeout"] = 0; }
                if( !isset($options["clear"]) ){ $options["clear"] = true; }
                        
                // Echo out the tip
                $tmp = "<a href=\"javascript:bento.message.open(" . htmlentities(json_encode($options)). ")\" class=\"bento_message_tip\">?</a>";
        
                // Return it
                return $echo ? print $tmp : $tmp;
        
        // method
        }
		
        /*
        @method: add
        @description: adds a message that loads on startup
        @params:
        @shortcode:  
        @return:
        */
        public function add( $options ){
			
			// Set this up
			if( !is_array($options) ){ $options = array("text"	=>	$options); }
			
			// Set the options
			if( !isset($options["text"]) ){ return false; }
			if( !isset($options["class"]) ){ $options["class"] = "info"; }
			
			// Check this out
			$this->public->messages[] = $options;
			
		// method
		}

// class
} ?>