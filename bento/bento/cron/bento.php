<?php
// Class
class bento_cron{

	// Load the variables 
	public $public;
	public $private;

	/*
	@method: clean()
	@description: Clean up old form tmp files and logs
	@params:
	@shortcode:  
	@return:
	*/ 
	public function clean( $time="1day" ){
	
		global $bento;
		
		// Setup the directories to empty
		$directories = array(
							$_SERVER['DOCUMENT_ROOT'] . "/bento/bento/cache/css/",
							$_SERVER['DOCUMENT_ROOT'] . "/bento/bento/cache/js/",
							$_SERVER['DOCUMENT_ROOT'] . "/bento/bento/log/error/",
							$_SERVER['DOCUMENT_ROOT'] . "/bento/bento/log/cron/"
							);
		
		// Loop through them for removal
		foreach( $directories as $directory ){
		
			// if the path is not valid or is not a directory ...
			if( !file_exists($directory) || !is_dir($directory) || !is_writable($directory) ){

				// ... we return false and exit the function
				continue;
		
			// ... if the path is not readable
			} else {
			
				$mydir = opendir($directory);
				while(false !== ($file = readdir( $mydir ))) {
					if( !is_dir($file) ) {
						unlink($directory.$file);
					}
				}
				closedir($mydir);
								
			// if
			}

		// foreach
		}
		
		// Loop through them for removal of log files older than log_retention
		foreach( $bento->private->log as $directory ){
		
			// Check it
			$directory = $_SERVER['DOCUMENT_ROOT'] . $directory;
		
			// if the path is not valid or is not a directory ...
			if( !file_exists($directory) || !is_dir($directory) || !is_writable($directory) ){

				// ... we return false and exit the function
				continue;
		
			// ... if the path is not readable
			} else {
			
				$mydir = opendir($directory);
				while(false !== ($file = readdir( $mydir ))) {
					if( !is_dir($file) ) {
						
						// Remove it if it's old
						if( time()-(int)(basename($directory . $file,".php")*86400) > $bento->private->log_retention ){
						
							unlink( $directory . $file );
						
						// if
						}
						
						//unlink($directory.$file);
					}
				}
				closedir($mydir);
								
			// if
			}

		// foreach
		}
		
		// Must return true, or the log will show there was an issue
		return true;
		
	// method
	}

	/*
	@method: report()
	@description: reports errors to the central bento server
	@params:
	@shortcode:  
	@return:
	*/ 
	public function report( $time="1minute" ){

		// Return true for the logs sake
		return true;

	// method
	}
	
// classs
} ?>