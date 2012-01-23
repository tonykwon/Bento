<?php
/**************************************************************************************
 * This script is developed by Arturs Sosins aka ar2rsawseen, http://code-snippets.co.cc
 * Fee free to distribute and modify code, but keep reference to its creator
 *
 * This class generate QR [Quick Response] codes with proper metadata for mobile  phones
 * using google chart api http://chart.apis.google.com
 * Here are sources with free QR code reading software for mobile phones:
 * http://reader.kaywa.com/
 * http://www.quickmark.com.tw/En/basic/download.asp
 * http://code.google.com/p/zxing/
 *
 * For more information, examples and online documentation visit: 
 * http://code-snippets.co.cc/PHP-classes/QR-code-generator-class
 ***************************************************************************************/
class qrcode
{
	private $data;
	
	/**
	* @function     __construct
	* @description  constructor
	* @param	$sensor : boolean
	*/
	function __construct($sensor = false){
	
		global $bento;
		
		// Add the shortcodes
		$bento->add_shortcode("<!--qrcode:url-->");
		$bento->add_shortcode("<!--qrcode:address:([^>]+|)-->");
		$bento->add_shortcode("<!--qrcode:contact:([^>]+|)-->");
			
	// method
	}
	
	public function __shortcode( $shortcode,$html ){

		// Let's look for some buttons		
		preg_match_all("@" . $shortcode. "@",$html,$maps);

		// Check which one it if
		if( stristr($shortcode,"qrcode:url") ){
		
			// Start the output buffer
			ob_start();
			
			// qr codes
			$this->link(); 
			echo "<img src='" . $this->get_link() . "' border='0'/>"; 
		
			// Take the contents from the php files		
			$text = ob_get_contents();
			ob_end_clean();
			
			$html = str_replace("<!--qrcode:link-->",$text,$html);
		
		// if
		}
		
		// Check which one it if
		if( stristr($shortcode,"qrcode:address:") ){
		
			// Loop through it
			foreach( $maps[1] as $address ){

				// Get the geocoder				
				$geoCoder = new geocoder();
				$result = $geoCoder->getGeoCoords($address);
		
				// Setup and address if there is one			
				if( $result["status"] == "OK" ){ 
				
					// Start the output buffer
					ob_start();
					
					// qr codes
					$this->geo( $result["lat"], $result["lng"], 0 ); 
					echo "<img src='" . $this->get_link() . "' border='0'/>"; 
				
					// Take the contents from the php files		
					$text = ob_get_contents();
					ob_end_clean();
		
					$html = str_replace("<!--qrcode:address:" . $address . "-->",$text,$html);
					
				// if
				}
				
			// foreach
			}
		
		}
		
		// Check which one it if
		if( stristr($shortcode,"qrcode:contact:") ){
		
			// Loop through it
			foreach( $maps[1] as $contact ){

				// Temp it up
				$tmp = explode(",",$contact);
				
				// These are the variables
				$variables = array();
				
				// These are the variables
				if( isset( $tmp[0] ) ){ $variables["name"] = $tmp[0]; }
				if( isset( $tmp[1] ) ){ $variables["address"] = $tmp[1]; }
				if( isset( $tmp[2] ) ){ $variables["phone"] = $tmp[2]; }
				if( isset( $tmp[2] ) ){ $variables["email"] = $tmp[2]; }
		
				// Start the output buffer
				ob_start();
				
				// qr codes
				$this->contact_info( $tmp[0], $tmp[1], $tmp[2], $tmp[3] ); 
				echo "<img src='" . $this->get_link() . "' border='0'/>"; 
			
				// Take the contents from the php files		
				$text = ob_get_contents();
				ob_end_clean();
	
				$html = str_replace("<!--qrcode:contact:" . $contact . "-->",$text,$html);
				
			// foreach
			}
		
		}
		
		return $html;
	
	// method
	}
	
	//creating code with link mtadata
	public function link($url = NULL ){
	
		global $scms;
		
		// Check this out	
		if( is_null($url) ){ $url = $scms->http() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ; }
	
		// echo $url; die();
	
		if (preg_match('/^http:\/\//', $url) || preg_match('/^https:\/\//', $url)) 
		{
			$this->data = $url;
		}
		else
		{
			$this->data = "http://".$url;
		}
	}
	
	//creating code with bookmark metadata
	public function bookmark($title, $url){
		$this->data = "MEBKM:TITLE:".$title.";URL:".$url.";;";
	}
	
	//creating text qr code
	public function text($text){
		$this->data = $text;
	}
	
	//creatng code with sms metadata
	public function sms($phone, $text){
		$this->data = "SMSTO:".$phone.":".$text;
	}
	
	//creating code with phone 
	public function phone_number($phone){
		$this->data = "TEL:".$phone;
	}
	
	//creating code with mecard metadata
	public function contact_info($name, $address, $phone, $email){
		$this->data = "MECARD:N:".$name.";ADR:".$address.";TEL:".$phone.";EMAIL:".$email.";;";
	}
	
	//creating code wth email metadata
	public function email($email, $subject, $message){
		$this->data = "MATMSG:TO:".$email.";SUB:".$subject.";BODY:".$message.";;";
	}
	
	//creating code with geo location metadata
	public function geo($lat, $lon, $height){
		$this->data = "GEO:".$lat.",".$lon.",".$height;
	}
	
	//creating code with wifi configuration metadata
	public function wifi($type, $ssid, $pass){
		$this->data = "WIFI:T:".$type.";S".$ssid.";".$pass.";;";
	}
	
	//creating code with i-appli activating meta data
	public function iappli($adf, $cmd, $param){
		$param_str = "";
		foreach($param as $val)
		{
			$param_str .= "PARAM:".$val["name"].",".$val["value"].";";
		}
		$this->data = "LAPL:ADFURL:".$adf.";CMD:".$cmd.";".$param_str.";";
	}
	
	//creating code with gif or jpg image, or smf or MFi of ToruCa files as content
	public function content($type, $size, $content){
		$this->data = "CNTS:TYPE:".$type.";LNG:".$size.";BODY:".$content.";;";
	}
	
	//getting image
	public function get_image($size = 150, $EC_level = 'L', $margin = '0'){
		$ch = curl_init();
		$this->data = urlencode($this->data); 
		curl_setopt($ch, CURLOPT_URL, 'http://chart.apis.google.com/chart');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'chs='.$size.'x'.$size.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$this->data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	//getting link for image
	public function get_link($size = 150, $EC_level = 'L', $margin = '0'){
		$this->data = urlencode($this->data); 
		return 'http://chart.apis.google.com/chart?chs='.$size.'x'.$size.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$this->data;
	}
	
	//forsing image download
	public function download_image($file){
		
		header('Content-Description: File Transfer');
		header('Content-Type: image/png');
		header('Content-Disposition: attachment; filename=QRcode.png');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		echo $file;
	}
}
?>