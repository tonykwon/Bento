<?php
// Class
class form_cron{

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
	public function clean( $time="1day" ){
	
		global $file,$form;
		
		// Setup the directories to empty
		foreach( $file->read_directory("bento/form/tmp/events/") as $f ){
			
			// Check the date
			if( (time()-$file->created("bento/form/tmp/events/" . $f )) > 86400 ){
			
				// Remove the old file
				$file->unlink("bento/form/tmp/events/" . $f );
				
			// if
			}
		
		// foreach	
		}
		
		// Must return true, or the log will show there was an issue
		return true;
		
	// method
	}
	
// classs
} ?>