<?php
/*
@method: file
@description: Common functions for the filesystem
@params:
@shortcode:  
@return:
*/
class file{

	/*
	@method: exists
	@description: checks to see if a file exists in the root
	@params:
			$file: the file name without the document root
	@shortcode:  
	@return: file contents or false
	*/
	public function exist( $file ){ return $this->exists( $file ); }
	public function exists( $file ){
	
		// Get the root
		$file = str_replace("//","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $file);
	
		//Open and read the file
		return file_exists($file);
	
	// method
	}


	/*
	@method: unlink
	@description: deletes a file if it exist from the root
	@params:
			$file: the file name without the document root
	@shortcode:  
	@return: file contents or false
	*/
	public function unlink( $file ){
	
		// Get the root
		$file = str_replace("//","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $file);
	
		//Open and read the file
		if( file_exists($file) ){
			
			unlink( $file );
		
		// if	
		}
	
	// method
	}

	/*
	@method: edited
	@description: checks the file creation time
	@params:
			$file: the file name without the document root
	@shortcode:  
	@return: file contents or false
	*/
	public function edited( $file ){
	
		// Get the root
		$file = str_replace("//","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $file);
	
		//Open and read the file
		if( file_exists($file) ){
			
			return (int)filemtime( $file );
		
		// if	
		}
		
		return 0;
	
	// method
	}

	/*
	@method: created
	@description: checks the file creation time
	@params:
			$file: the file name without the document root
	@shortcode:  
	@return: file contents or false
	*/
	public function created( $file ){
	
		// Get the root
		$file = str_replace("//","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $file);
	
		//Open and read the file
		if( file_exists($file) ){
			
			return (int)filectime( $file );
		
		// if	
		}
		
		return 0;
	
	// method
	}

	/*
	@method: read
	@description: Reads the text file specified
	@params:
			$file: the file name without the document root
			$echo: return it or echo it (defaults to off)
	@shortcode:  
	@return: file contents or false
	*/
	public function read( $file,$echo=false ){
	
		// Get the root
		$file = str_replace("//","/",$_SERVER['DOCUMENT_ROOT'] . "/" . $file);
	
		//Open and read the file
		if( file_exists($file) ){
		
			// Check if it's a directory or not
			if( !is_dir($file) ){
				
				$file_name = fopen($file, 'r');
				$text = fread($file_name, filesize($file));;
				fclose($file_name);
			
				//Return the text
				$tmp_return = htmlentities($text);
			
			// It's a directory	
			} else {
			
				// Open up the directory
				if ( $handle = opendir( $file ) ) {
					
					// This will return the files
					$tmp = array();
				
					/* This is the correct way to loop over the directory. */
					while (false !== ($file = readdir($handle))) {
						if( $file != "." && $file != ".." ){
							$tmp[] = $file;
						}
					}
				
					// close up the resources
					closedir($handle);
					
					// Return it
					return $tmp;
				
				// This will return nothing
				} else {
				
					// Do it
					return array();
				
				// if
				}
			
			// if
			}
			
		} else {
		
			$tmp_return = false;
		
		}
		
		// Check if returning or writing out
		if( !$echo ){
		
			return $tmp_return;
		
		} else {
		
			echo $tmp_return;
		
		// if
		}
	
	// method
	}
	
	/*
	@method: write( $file,$content )
	@description: Writes to a text file specified
	@params:
	@shortcode:  
	@return:
	*/
	public function write( $file,$content ){

		// print_r(debug_backtrace(false));

		// Get the root
		$file = $_SERVER['DOCUMENT_ROOT'] . "/" . str_replace($_SERVER['DOCUMENT_ROOT'],"",$file);
							
		$file_name = fopen($file, 'w');
		if( !$file_name ){ return $file_name; }
		fwrite($file_name, $content );
		fclose($file_name);
		@chmod($file, 0774);
		
		//Return the text
		return $file_name;

	// method
	}
	
	/*
	@method: create_directory( $directory )
	@description: Creates a directory (For content, Menus, site)
	@params:
	@shortcode:  
	@return:
	*/
	public function create_directory( $directory ){
	
		// Get the root
		$directory = $_SERVER["DOCUMENT_ROOT"] . "/" . str_replace($_SERVER['DOCUMENT_ROOT'],"",$directory);
	
		// Check if we can create a directory
		if ( !file_exists($directory) ) {
	
			// Create the directory
			mkdir( $directory );
			chmod( $directory, 0774);
			return true;
			
		// if
		} else {
		
			return true;
			
		// if
		}
		
	// method
	}
	
	/*
	@method: read_directory( $directory )
	@description: Reads the contents of a directory
	@params:
	@shortcode:  
	@return:
	*/
	public function read_directory( $directory ){
	
		// Get the root
		$directory = $_SERVER["DOCUMENT_ROOT"] . "/" . str_replace($_SERVER['DOCUMENT_ROOT'],"",$directory);

		// Set it up
		$files = array();
	
		// Check if we can create a directory
		if ( file_exists($directory) ) {
	
			// Create the directory
			$handle = opendir( $directory );			
			
			// Loop through the files
			while (false !== ($f2 = readdir($handle))){
			
				// Make sure it's not a directory
				if( $f2 != "." && $f2 != ".." ){
			
					// Get the files
					$files[] = $f2;
			
				// if
				}
				
			// while
			}
			
			return $files;
			
		// if
		} else {
		
			return array();
			
		// if
		}
		
	// method
	}
	
	/*
	@method: rename_directory( $current_directory,$new_directory )
	@description: Renames a directory (For content, Menus, site)
	@params:
	@shortcode:  
	@return:
	*/
	public function rename_directory( $current_directory,$new_directory ){
	
		if ( !file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $new_directory ) ) {
	
			return rename(	$_SERVER["DOCUMENT_ROOT"] . "/" . $current_directory, $_SERVER["DOCUMENT_ROOT"] . "/" . $new_directory );
			
		} else {
		
			return false;
			
		// if
		}
		
	// method
	}
	
	/*
	@method: delete_directory($directory, $empty=false)
	@description: Removes a directory (For content, Menus, site)
	@params:
	@shortcode:  
	@return:
	*/
	public function delete_directory($directory, $empty=false){
	
		// Add the root
		$directory = $_SERVER["DOCUMENT_ROOT"] . "/" . $directory;
	
		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/'){
		
			$directory = substr($directory,0,-1);
		
		// if
		}
	
		// if the path is not valid or is not a directory ...
		if( !file_exists($directory) || !is_dir($directory) ){
		
			// ... we return false and exit the function
			return false;
	
		// ... if the path is not readable
		} elseif (!is_readable($directory)){
		
			// ... we return false and exit the function
			return false;
	
		// ... else if the path is readable
		} else {

			// Unlink the directory
			exec("rm -rf " . $directory);
			
			// return success
			return true;
			
		// if
		}
	
	// method
	}
	
	/*
	@method: empty_directory( $directory )
	@description: Removes a directory (For content, Menus, site)
	@params:
	@shortcode:  
	@return:
	*/
	public function empty_directory( $directory ){
	
		// Add the root
		$directory = $_SERVER["DOCUMENT_ROOT"] . "/" . $directory;
	
		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/'){
		
			$directory = substr($directory,0,-1);
		
		// if
		}
	
		// if the path is not valid or is not a directory ...
		if( !file_exists($directory) || !is_dir($directory) || !$this->is_writeable($directory) ){
		
			// ... we return false and exit the function
			return false;
	
		// ... if the path is not readable
		} else if ( !is_readable($directory) ){
		
			$mydir = opendir($dir);
			while(false !== ($file = readdir($mydir))) {
				if($file != "." && $file != "..") {
					chmod($dir.$file, 0777);
					if(is_dir($dir.$file)) {
						chdir('.');
						$this->empty_directory($dir.$file.'/');
						rmdir($dir.$file) or DIE("couldn't delete $dir$file<br >");
					}
					else
						unlink($dir.$file) or DIE("couldn't delete $dir$file<br >");
				}
			}
			closedir($mydir);
			
			return true;
			
		// if
		}
	
	// method
	}
	
	/*
	@method: writable( $fd )
	@description: This is how we check if things are writable
	@params:
	@shortcode:  
	@return:
	*/
	public function writable( $fd ){
	
		// Add the root
		$fd = $_SERVER['DOCUMENT_ROOT'] . "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", $fd);
	
		// Check if the file exists
		if( file_exists($fd) ){
	
			// If so cehck if it's writable
			if( is_writable( $fd ) ){
			
				return true;
			
			// it's not writable	
			} else {
			
				return false;
				
			}
				
		// The file doesn't exist, so we're looking for a directory
		} else {
		
			return false;
			
		// if
		}
	
	// method
	}
	
	/*
	@method: name( $name,$echo=false )
	@description: Converts a name to a usable file
	@params:
	@shortcode:  
	@return:
	*/
	public function name( $name,$echo=false ){
	
		// Make the name usable
		$name = preg_replace('/[^a-zA-Z._\s]/', '', $name);
		$name = str_replace(' ','_',$name);
		$name = strtolower($name); // Convert to lower case

		// Check if echo or return
		return $echo ? print $name : $name;
		
	// method
	}
	
	/*
	@method: upload( $file,$echo=false )
	@description: Converts a name from an uploaded file to a usable name
	@params:
	@shortcode:  
	@return:
	*/
	public function upload( $file,$echo=false ){
	
		// This is to set the name to the (cleaned up) name of the file uploaded
		$fe = explode(".",$file);
		$tmp = str_replace("-"," ",$fe[0]);
		$tmp = str_replace("_"," ",$tmp);
		$tmp = ereg_replace("[/[^a-zA-Z0-9\s]/", "", $tmp);

		// Break the file name into words
		$words = explode(" ", $tmp);
		$result = "";

		// Loop through the words to capitalize
		for ($i=0; $i<count($words); $i++) {
		
			$s = strtolower($words[$i]);
			$s = substr_replace($s, strtoupper(substr($s, 0, 1)), 0, 1);
			$result .= "$s ";
			
		// for
		}
		
		// set the name to the result

		$name = trim($result)  . "." . $fe[1];

		// Check if echo or return
		return $echo ? print $name : $name;
		
	// method
	}
	
	/*
	@method: decode ( $data )
	@description: Encodes insert data for languages
	@params:
	@shortcode:  
	@return:
	*/
	public function decode ( $data ){ 
	
		$htmlentity = array(
						chr(226) . chr(128) . chr(152) => "&lsquo;",  // ‘
						chr(226) . chr(128) . chr(153) => "&rsquo;",  // ’
						chr(226) . chr(128) . chr(154) => "&sbquo;",  // ‚
						chr(226) . chr(128) . chr(156) => "&ldquo;",  // “
						chr(226) . chr(128) . chr(157) => "&rdquo;",  // ”
						chr(226) . chr(128) . chr(158) => "&bdquo;",  // „
						chr(226) . chr(128) . chr(160) => "&dagger;",  // †
						chr(226) . chr(128) . chr(161) => "&Dagger;",  // ‡
						chr(226) . chr(128) . chr(176) => "&permil;",  // ‰
						chr(226) . chr(128) . chr(185) => "&lsaquo;",  // ‹
						chr(226) . chr(128) . chr(186) => "&rsaquo;",  // ›
						chr(226) . chr(128) . chr(190) => "&oline;",  // ?
						chr(226) . chr(153) . chr(160) => "&spades;",  // ?
						chr(226) . chr(153) . chr(163) => "&clubs;",  // ?
						chr(226) . chr(153) . chr(165) => "&hearts;",  // ?
						chr(226) . chr(153) . chr(166) => "&diams;",  // ?
						chr(226) . chr(134) . chr(144) => "&larr;",  // ?
						chr(226) . chr(134) . chr(145) => "&uarr;",  // ?
						chr(226) . chr(134) . chr(146) => "&rarr;",  // ?
						chr(226) . chr(134) . chr(147) => "&darr;",  // ?
						chr(226) . chr(132) . chr(162) => "&trade;",  // ™
				
						chr(226) . chr(128) . chr(147) => "&ndash;",  // –
						chr(226) . chr(128) . chr(148) => "&mdash;",  // —
						chr(194) . chr(161) => "&iexcl;",  // ¡
						chr(194) . chr(162) => "&cent;",  // ¢
						chr(194) . chr(163) => "&pound;",  // £
						chr(194) . chr(164) => "&curren;",  // ¤
						chr(194) . chr(165) => "&yen;",  // ¥
						chr(194) . chr(166) => "&brvbar;",  // ¦
						chr(194) . chr(167) => "&sect;",  // §
						chr(194) . chr(168) => "&uml;",  // ¨
						chr(194) . chr(169) => "&copy;",  // ©
						chr(194) . chr(170) => "&ordf;",  // ª
						chr(194) . chr(171) => "&laquo;",  // «
						chr(194) . chr(172) => "&not;",  // ¬
						chr(194) . chr(174) => "&reg;",  // ®
						chr(194) . chr(175) => "&macr;",  // ¯
				
						chr(194) . chr(176) => "&deg;",  // °
						chr(194) . chr(177) => "&plusmn;",  // ±
						chr(194) . chr(178) => "&sup2;",  // ²
						chr(194) . chr(179) => "&sup3;",  // ³
						chr(194) . chr(180) => "&acute;",  // ´
						chr(194) . chr(181) => "&micro;",  // µ
						chr(194) . chr(182) => "&para;",  // ¶
						chr(194) . chr(183) => "&middot;",  // ·
						chr(194) . chr(184) => "&cedil;",  // ¸
						chr(194) . chr(185) => "&sup1;",  // ¹
						chr(194) . chr(186) => "&ordm;",  // º
						chr(194) . chr(187) => "&raquo;",  // »
						chr(194) . chr(188) => "&frac14;",  // ?
						chr(194) . chr(189) => "&frac12;",  // ?
						chr(194) . chr(190) => "&frac34;",  // ?
						chr(194) . chr(191) => "&iquest;",  // ¿
				
						chr(195) . chr(128) => "&Agrave;",  // À
						chr(195) . chr(129) => "&Aacute;",  // Á
						chr(195) . chr(130) => "&Acirc;",  // Â
						chr(195) . chr(131) => "&Atilde;",  // Ã
						chr(195) . chr(132) => "&Auml;",  // Ä
						chr(195) . chr(133) => "&Aring;",  // Å
						chr(195) . chr(134) => "&AElig;",  // Æ
						chr(195) . chr(135) => "&Ccedil;",  // Ç
						chr(195) . chr(136) => "&Egrave;",  // È
						chr(195) . chr(137) => "&Eacute;",  // É
						chr(195) . chr(138) => "&Ecirc;",  // Ê
						chr(195) . chr(139) => "&Euml;",  // Ë
						chr(195) . chr(140) => "&Igrave;",  // Ì
						chr(195) . chr(141) => "&Iacute;",  // Í
						chr(195) . chr(142) => "&Icirc;",  // Î
						chr(195) . chr(143) => "&Iuml;",  // Ï
						chr(195) . chr(144) => "&ETH;",  // Ð
						chr(195) . chr(145) => "&Ntilde;",  // Ñ
						chr(195) . chr(146) => "&Ograve;",  // Ò
						chr(195) . chr(147) => "&Oacute;",  // Ó
						chr(195) . chr(148) => "&Ocirc;",  // Ô
						chr(195) . chr(149) => "&Otilde;",  // Õ
						chr(195) . chr(150) => "&Ouml;",  // Ö
						chr(195) . chr(151) => "&times;",  // ×
						chr(195) . chr(152) => "&Oslash;",  // Ø
						chr(195) . chr(153) => "&Ugrave;",  // Ù
						chr(195) . chr(154) => "&Uacute;",  // Ú
						chr(195) . chr(155) => "&Ucirc;",  // Û
						chr(195) . chr(156) => "&Uuml;",  // Ü
						chr(195) . chr(157) => "&Yacute;",  // Ý
						chr(195) . chr(158) => "&THORN;",  // Þ
						chr(195) . chr(159) => "&szlig;",  // ß
						chr(195) . chr(160) => "&agrave;",  // à
						chr(195) . chr(161) => "&aacute;",  // á
						chr(195) . chr(162) => "&acirc;",  // â
						chr(195) . chr(163) => "&atilde;",  // ã
						chr(195) . chr(164) => "&auml;",  // ä
						chr(195) . chr(165) => "&aring;",  // å
						chr(195) . chr(166) => "&aelig;",  // æ
						chr(195) . chr(167) => "&ccedil;",  // ç
						chr(195) . chr(168) => "&egrave;",  // è
						chr(195) . chr(169) => "&eacute;",  // é
						chr(195) . chr(170) => "&ecirc;",  // ê
						chr(195) . chr(171) => "&euml;",  // ë
						chr(195) . chr(172) => "&igrave;",  // ì
						chr(195) . chr(173) => "&iacute;",  // í
						chr(195) . chr(174) => "&icirc;",  // î
						chr(195) . chr(175) => "&iuml;",  // ï
						chr(195) . chr(176) => "&eth;",  // ð
						chr(195) . chr(177) => "&ntilde;",  // ñ
						chr(195) . chr(178) => "&ograve;",  // ò
						chr(195) . chr(179) => "&oacute;",  // ó
						chr(195) . chr(180) => "&ocirc;",  // ô
						chr(195) . chr(181) => "&otilde;",  // õ
						chr(195) . chr(182) => "&ouml;",  // ö
						chr(195) . chr(183) => "&divide;",  // ÷
						chr(195) . chr(184) => "&oslash;",  // ø
						chr(195) . chr(185) => "&ugrave;",  // ù
						chr(195) . chr(186) => "&uacute;",  // ú
						chr(195) . chr(187) => "&ucirc;",  // û
						chr(195) . chr(188) => "&uuml;",  // ü
						chr(195) . chr(189) => "&yacute;",  // ý
						chr(195) . chr(190) => "&thorn;",  // þ
						chr(195) . chr(191) => "&yuml;",  // ÿ
			);
	
		$data = str_replace(array_keys($htmlentity), array_values($htmlentity), $data);
		
		return $data;
	
	// method
	}

// class
}
?>