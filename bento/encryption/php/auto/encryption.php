<?php
/*
@method: encryption()
@description: Encrypts and decrypt data (use with db and forms)
@params:
@shortcode:  
@return:
*/
class encryption{

	// Load the variables 
	public $public;
	public $private;

	/*
	@method: __construct()
	@description: Instantiates the class
	@params:
	@shortcode:  
	@return:
	*/
	public function __construct(){
			
	// method
	}

	/*
	@method: __configure()
	@description: Configures the library
	@params:
	@shortcode:  
	@return:
	*/
	public function __configure(){
			
			// Makre sure we have a key
			if( isset($this->private->key) && $this->private->key != "" ){
			
					return true;
			
			// if
			}
			
	// method
	}

	/*
	@method: encrypt( $value, $key=NULL )
	@description: Will encrypt the field names
	@params:
	@shortcode:  
	@return:
	*/
	public function encrypt( $value, $key=NULL ){
	
			if( is_null($key) ){ $key = $this->private->key ; }
			
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			
			return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_ECB, $iv));

	// method
	}

	/*
	@method: decrypt( $value, $key=NULL )
	@description: Will decrypt the field names
	@params:
	@shortcode:  
	@return:
	*/
	public function decrypt( $value, $key=NULL ){
			
			if( is_null($key) ){ $key = $this->private->key ; }
			
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			
			$value = base64_decode($value);
			
			return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_ECB, $iv);

	// method
	}

// class
}
?>