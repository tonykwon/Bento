<?php
// Class
class scms_cron {

	// Load the variables 
	public $public;
	public $private;

	/*
	@method: clean()
	@description: Clean up old form event files
	@params:
	@shortcode:  
	@return:
	*/ 
	public function clean( $time="1minute" ){
	
		global $scms,$db;
		
		// Delete all old files
		//if( $scms->feed_type("push") ){
	
			// Select old events		
			$db->select(
						array(
								"table"	=>	"event.date_insert<" . (time()-3600)
							)
						);
						
			// Loop through the old events
			foreach( $db->recordset("event") as $event ){
				
				// Delete the old event
				$db->delete(
							array(
								"table"	=>	"event",
								"criteria"	=>	array(
													"id"	=>	$event["id"]
													)
												)
											);
				
			// foreach
			}
			
		// if
		//}
		
		// Must return true, or the log will show there was an issue
		return true;
		
	// method
	}

// class
} ?>