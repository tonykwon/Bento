<?php
// session_destroy(); 
/*
 * include class for the geocoder
 */
class map {

	// Do it
	public $public;
	public $private;
		
	/**
	* @function     __construct
	* @description  constructor
	* @param	$sensor : boolean
	*/
	function __construct($sensor = false){
	
		global $bento;
		
		// This will add the map form at the end
		$bento->add_event('all','deconstructed','lookupForm');
		
		// Add the shortcodes
		$bento->add_shortcode("<!--map:map:([^>]+|)-->");
		$bento->add_shortcode("<!--map:address:([^>]+|)-->");	
		$bento->add_shortcode("<!--map:form-->");	
		
		// Include the autoloader
		$bento->add_php(
						array(
							"plugin"	=>	"map",
							"name"	=>	"geocoder"
						)
					);
		
		// Check if we need to create a token
		if( !isset($_SESSION["bento"]["map"]["geolocation"]) ){
		
			// Get the account information
			$buf = "";
			$query = "http://geoip2.maxmind.com/e?l=vyWWCUJCsWeG&i=" . $_SERVER['REMOTE_ADDR'];
			$url = parse_url($query);
			$host = $url["host"];
			$path = $url["path"] . "?" . $url["query"];
			$timeout = 1;
			$fp = fsockopen ($host, 80, $errno, $errstr, $timeout);
			
			if ($fp) {
				fputs ($fp, "GET $path HTTP/1.0\nHost: " . $host . "\n\n");
				while (!feof($fp)) {
					$buf .= fgets($fp, 128);
				}
				$lines = explode("\n", $buf);
				$city = $lines[count($lines)-1];
				fclose($fp);
				$tmp = explode(",",$city);
			} else {
				$tmp[4] = "San Fransisco";
				$tmp[3] = "California";
				$tmp[1] = "United States";
			}
			
			if( $tmp[0] == "" ){
				$tmp[4] = "San Fransisco";
				$tmp[3] = "California";
				$tmp[1] = "United States";	
			}
			
			//print_r( $tmp ); die();
			
			// Use google to help us out a bit			
			$geoCoder = new geocoder();
			$result = $geoCoder->getGeoCoords($tmp[2] . "," . $tmp[1]);

			// Check it out
			if( $result ){
						
				// The reast
				$_SESSION["bento"]["map"]["geolocation"]["lat"] = $tmp[4];
				$_SESSION["bento"]["map"]["geolocation"]["lng"] = $tmp[5];
				$_SESSION["bento"]["map"]["geolocation"]["address"] = isset( $result["address"] ) ? $result["address"] : "";
				$_SESSION["bento"]["map"]["geolocation"]["community"] = isset( $result["community"] ) ? $result["community"] : "";
				$_SESSION["bento"]["map"]["geolocation"]["city"] = isset( $result["city"] ) ? $result["city"] : $tmp[4];
				$_SESSION["bento"]["map"]["geolocation"]["region"] = isset( $result["region"] ) ? $result["region"] : $tmp[3];
				$_SESSION["bento"]["map"]["geolocation"]["country"] = isset( $result["country"] ) ? $result["country"] : $tmp[1];
				$_SESSION["bento"]["map"]["geolocation"]["post"] = isset( $result["post"] ) ? $result["post"] : "";
				$_SESSION["bento"]["map"]["geolocation"]["isp"] = substr($tmp[8],1,-1);
		
			} else {
			
				$_SESSION["bento"]["map"]["geolocation"]["lat"] = $tmp[4];
				$_SESSION["bento"]["map"]["geolocation"]["lng"] = $tmp[5];
				$_SESSION["bento"]["map"]["geolocation"]["address"] = "";
				$_SESSION["bento"]["map"]["geolocation"]["community"] = "";
				$_SESSION["bento"]["map"]["geolocation"]["city"] = $tmp[5];
				$_SESSION["bento"]["map"]["geolocation"]["region"] = $tmp[3];
				$_SESSION["bento"]["map"]["geolocation"]["country"] = $tmp[1];
				$_SESSION["bento"]["map"]["geolocation"]["post"] = "";
				$_SESSION["bento"]["map"]["geolocation"]["isp"] = "";
			
			// if
			}
		
		// if
		}
			
	// method
	}
	
	/**
	* @function     __load up the class
	* @description  
	* @param
	*/
	public function __load(){
		
		// Set somet information
		$this->public->geolocation = $_SESSION["bento"]["map"]["geolocation"];
		
		// Set up some defaults
		$this->public->marker = array();	
		
		$this->private->search_id = 0; 
		$this->private->count = 0;
		$this->private->maxLng = -1000000;
		$this->private->minLng = 1000000;
		$this->private->maxLat = -1000000;
		$this->private->minLat = 1000000;
		$this->private->centerLat = 0;
		$this->private->centerLng = 0;
	
		// Set the sensor
		$this->public->apiSensor = false;
			
	// method
	}
	
	public function __shortcode( $shortcode,$html ){

		// Let's look for some buttons		
		preg_match_all("@" . $shortcode. "@",$html,$maps);

		// Check which one it if
		if( stristr($shortcode,"map:address:") ){
		
			// Loop through it
			foreach( $maps[1] as $address ){
			
				// Temp it up
				if( stristr($address,",") ){
					$tmp = explode(",",$address);
				}else{
					$tmp = array($address);
				}
		
				// Setup and address if there is one			
				if( isset( $tmp[0] ) ){ 
				
					$address = array("map_id"	=>	$this->private->count, "address"	=> $tmp[0] );
									
					if( isset( $tmp[1] ) ){ $address["tooltip"] = $tmp[1]; }
					if( isset( $tmp[2] ) ){ $address["info"] = $tmp[2]; }
					if( isset( $tmp[3] ) ){ $address["icon"] = $tmp[3]; }	
					if( isset( $tmp[4] ) ){ $address["clickable"] = $tmp[4]; }
					
					// Check if this isset		
					$this->addMarker( $address );
					
					$html = str_replace("<!--map:address:" . $address . "-->","",$html);
					
				// if
				}
				
			// foreach
			}
		
		} else if( stristr($shortcode,"map:map:") ){
			
			// Loop through it
			foreach( $maps[1] as $map ){
			
				// Temp it up
				$tmp = explode(",",$map);
				
				// These are the variables
				$variables = array("map_id"	=>	$this->private->count );
				
				// These are the variables
				if( isset( $tmp[0] ) ){ $variables["width"] = (int)$tmp[0]; }
				if( isset( $tmp[1] ) ){ $variables["height"] = (int)$tmp[1]; }
				if( isset( $tmp[2] ) ){ $variables["zoom"] = (int)$tmp[2]; }
				
				// Setup and address if there is one			
				if( isset( $tmp[3] ) ){ $address["address"] = $tmp[3]; }
				if( isset( $tmp[4] ) ){ $address["tooltip"] = $tmp[4]; }
				if( isset( $tmp[5] ) ){ $address["info"] = $tmp[5]; }
				if( isset( $tmp[6] ) ){ $address["icon"] = $tmp[6]; }	
				if( isset( $tmp[7] ) ){ $address["clickable"] = $tmp[7]; }	
				
				// Start the output buffer
				ob_start();
			
				// Show the map
				$this->show( $variables );
				
				// Check if this isset		
				if( isset($tmp[3]) ){
				
					$this->addMarker( $address );
									
				// if
				}
			
				// Take the contents from the php files		
				$text = ob_get_contents();
				ob_end_clean();
	
				$html = str_replace("<!--map:map:" . $map . "-->",$text,$html);
			
			// foreach
			}
		
		// if	
		} else if( stristr($shortcode,"map:form") ){
		
			// Start the output buffer
			ob_start();
	
			// Check this up
			$this->form();
		
			// Take the contents from the php files		
			$text = ob_get_contents();
			ob_end_clean();
	
			$html = str_replace("<!--map:form-->",$text,$html);
		
		// if
		}
		
		return $html;
	
	// method
	}
	
	/**
	* @function     setMapType
	* @param        $mapType : string (can be 'ROADMAP', 'SATELLITE', 'HYBRID' or 'TERRAIN')
	* @returns      nothing
	* @description  Sets the type of the map to be displayed, either a (road)map, satellite, hybrid or terrain view; (road)map by default
	*/
	public function setMapType($mapType){
	
		switch ($mapType)
		{
			case 'SATELLITE' :
			case 'HYBRID' :
			case 'TERRAIN' :
				$this->public->mapType = $mapType;
				break;
			default :
				$this->public->mapType = 'ROADMAP';
				break;        
		}
	
	// method
	}
	
	/**
	* @function     setInfoWindowBehaviour
	* @param        $infoWindowBehaviour : string (can be 'MULTIPLE', 'SINGLE' or 'CLOSE_ON_MAPCLICK')
	* @returns      nothing
	* @description  Sets the behaviour of InfoWindow overlays, either multiple or single windows are displayed
	*/
	public function setInfoWindowBehaviour($infoWindowBehaviour){
	
		switch ($infoWindowBehaviour)
		{
			case 'MULTIPLE' :
			case 'SINGLE' :
			case 'CLOSE_ON_MAPCLICK' :
			case 'SINGLE_CLOSE_ON_MAPCLICK' :
				$this->infoWindowBehaviour = $infoWindowBehaviour;
				break;
			default :
				$this->infoWindowBehaviour = 'MULTIPLE'; // default behaviour of Google Maps V3
				break;        
		}
		
	// method
	}
	
	/**
	* @function     setInfoWindowTrigger
	* @param        $infoWindowTrigger : string : can be 'CLICK' OR 'ONMOUSEOVER'
	* @returns      nothing
	* @description  Determines if InfoWindow is shown with a click or by mouseover
	*/
	public function setInfoWindowTrigger($infoWindowTrigger){
	
		switch ($infoWindowTrigger)
		{
			case 'MOUSEOVER' :
				$this->infoWindowTrigger = $infoWindowTrigger;
				break;
			default :
				$this->infoWindowTrigger = 'CLICK';
				break;        
		}
	
	// public
	}
	
	/**
	* @function     showNavigationControl
	* @param        $control : boolean
	* @param        $style : string (can be 'ANDROID', 'DEFAULT', 'SMALL' or 'ZOOM_PAN')
	* @returns      nothing
	* @description  Tells the v3 API wether to show the navigation control or not
	*/
	public function showNavigationControl($control = true, $style){
	
		$this->showNavigationControl = $control;
		
		switch ( $style )
		{
			case 'ANDROID' :
			case 'SMALL' :
			case 'ZOOM_PAN' :
				$this->navigationControlStyle = $style;
				break;
			default :
				$this->navigationTypeControlStyle = 'DEFAULT';
				break;    
		}
		
	// method
	}
	
	/**
	 * @function     adjustCenterCoords
	 * 
	 * @param        $lng the map longitude : string
	 * @param        $lat the map latitude  : string
	 * @description  adjust map center coordinates by the given lat/lon point
	 */
	private function adjustCenterCoords($lat, $lng){

		if ( (strlen((string)$lat) != 0) && (strlen((string)$lng) != 0) ){
			
			$this->private->maxLat = (float) max($lat, $this->private->maxLat);
			$this->private->minLat = (float) min($lat, $this->private->minLat);
			$this->private->maxLng = (float) max($lng, $this->private->maxLng);
			$this->private->minLng = (float) min($lng, $this->private->minLng);
		
			$this->private->centerLng = (float) ($this->private->minLng + $this->private->maxLng) / 2;
			$this->private->centerLat = (float) ($this->private->minLat + $this->private->maxLat) / 2;
		
		// if
		}
	
	// method
	}
		
	/**
	* @function     addMarkerByAddress
	* @param        $lat : string (latitude)
	*               $lng : string (longitude)
	*               $tooltip : string (tooltip text)
	*               $info : Message to be displayed in an InfoWindow
	*               $iconURL : URL to an icon to be displayed instead of the default icon
	*               (see for example http://code.google.com/p/google-maps-icons/)
	*               $clickable : boolean (true if the marker should be clickable)
	*               @description  Add's a Marker to be displayed on the Google Map using latitude/longitude
	*/
	public function addMarker( $variables,$return=false ){
		
		// Get the 
		global $bento;
		
		// This is where things are going
		$marker["map_id"] = $this->public->canvas . "_" . (( isset( $variables["map_id"] ) ) ? $variables["map_id"] : 0 );	
		
		// Check this out
		if( isset($variables["address"]) ){

			$geoCoder = new geocoder();
			$result = $geoCoder->getGeoCoords($variables["address"]);
		
		// Here is the problem
		} else if( isset($variables["lat"]) && isset($variables["lng"]) ){
		
			$result['status'] = "OK";
			$result['lat'] = $variables['lat'];
			$result['lng'] = $variables['lng'];
			
		// if
		} else {
		
			return false;
		
		}

		// Check the type
		if( isset($result["address"]) ){
		
			$type = "address";
		
		} else if( isset($result["community"]) ){
		
			$type = "community";
		
		} else if( isset($result["city"]) ){
		
			$type = "city";
		
		} else if( isset($result["region"]) ){
		
			$type = "region";
		
		} else if( isset($result["country"]) ){

			$type = "country";
	
		} else {
		
			$type = "none";
			
		}
		
		// Everything went okay
		if ( $result['status'] == "OK" ){

			$marker['lat'] = $result['lat'];
			$marker['lng'] = $result['lng'];
			if( isset( $variables["tooltip"] ) ){ $marker['tooltip'] = $variables["tooltip"]; }
			if( isset( $variables["info"] ) ){ $marker['info'] = $variables["info"]; }
			if( isset( $variables["icon"] ) ){ $marker['icon'] = $variables["icon"]; } else { $marker["icon"] = "/plugins/plugginit/themes/plugginit/images/icons/pin.png"; }
			if( isset( $variables["clickable"] ) ){ $marker['clickable'] = $variables["clickable"]; } else { $marker['clickable'] = false; };        
			
			// Set what kind of type it is
			$marker["type"] = $type;
			$marker["result"] = $result;
			
			// Return the marker
			if( $return ){ return $marker; }
			
			// Add this marker
			$this->public->map->marker[] = $marker;

			// Set the center
			$this->adjustCenterCoords($result['lat'], $result['lng']);
		
		// if
		}
	
	// method
	}
	
	/**
	* @function     addCircle
	* @param        $lat : string (latitude)
	*               $lng : string (longitude)
	*               $rad : string (radius of circle in meters)
	*               $info : Message to be displayed in an InfoWindow
	*               $options : array (options like stroke color etc. for the circle)
	* @description  Add's an circle to be displayed on the Google Map using latitude/longitude and radius
	*/
	public function addCircle($address, $radius, $info="", $options=array() ){
	
		global $bento;

		// Remove this
		if (!is_string($address)){ return; }
	
		$geoCoder = new geocoder();
		$result = $geoCoder->getGeoCoords($address);
		
		// Everything went okay
		if ( $result['status'] == "OK" ){
		
			$count = count($this->mapCircles);
			$this->mapCircles[$count]['lat']  = $result['lat'];
			$this->mapCircles[$count]['lng']  = $result['lng'];
			$this->mapCircles[$count]['radius']  = $radius;
			$this->mapCircles[$count]['info'] = $info;
			
			/* set options */
			if ( sizeof($options) != 0 ){
			
				$this->mapCircles[$count]['fillColor']     = $options['fillColor'];
				$this->mapCircles[$count]['fillOpacity']   = $options['fillOpacity'];
				$this->mapCircles[$count]['strokeColor']   = $options['strokeColor'];
				$this->mapCircles[$count]['strokeOpacity'] = $options['strokeOpacity'];
				$this->mapCircles[$count]['strokeWeight']  = $options['strokeWeight'];
				
				if ( $options['clickable'] == "" OR $options['clickable'] == false ){
				
					$this->mapCircles[$count]['clickable'] = false;
				
				} else {
				
					$this->mapCircles[$count]['clickable'] = true;
				
				// method
				}
			
			// if
			}
			
			// Set the center
			$this->adjustCenterCoords($result['lat'], $result['lng']);
		
			// Add it
			$this->public->map->circle[] = $this->mapCircles[$count];
		
		// if
		}
	
	// method
	}
	
	/**
	* @function     addRectangle
	* @param        $lat1 : string (latitude sw corner)
	*               $lng1 : string (longitude sw corner)
	*               $lat2 : string (latitude ne corner)
	*               $lng2 : string (longitude ne corner)
	*               $info : Message to be displayed in an InfoWindow
	*               $options : array (options like stroke color etc. for the rectangle)
	* @description  Add's a rectangle to be displayed on the Google Map using latitude/longitude for soutwest and northeast corner
	*/
	public function addRectangle($address1, $address2, $info="", $options=array()){

		$geoCoder = new geocoder();
		$result = array();
		
		if (!is_string($address)){
		
			die("All Addresses must be passed as a string");
		
		// if
		}
		
		// Get the original lat/long
		$result1 = $geoCoder->getGeoCoords($address1);
		$result2 = $geoCoder->getGeoCoords($address2);
		
		// Everything went okay
		if ( $result['status'] == "OK" ){
	
			$count = count($this->mapRectangles);
			$this->mapRectangles[$count]['lat1'] = $result1["lat"];
			$this->mapRectangles[$count]['lng1'] = $result1["lng"];
			$this->mapRectangles[$count]['lat2'] = $result2["lat"];
			$this->mapRectangles[$count]['lng2'] = $result2["lng"];
			$this->mapRectangles[$count]['info'] = $info;
			
			/* set options */
			if ( sizeof($options) != 0 ){
			
				$this->mapRectangles[$count]['fillColor']     = $options['fillColor'];
				$this->mapRectangles[$count]['fillOpacity']   = $options['fillOpacity'];
				$this->mapRectangles[$count]['strokeColor']   = $options['strokeColor'];
				$this->mapRectangles[$count]['strokeOpacity'] = $options['strokeOpacity'];
				$this->mapRectangles[$count]['strokeWeight']  = $options['strokeWeight'];
				
				if ( $options['clickable'] == "" OR $options['clickable'] == false )
				{
					$this->mapRectangles[$count]['clickable'] = false;
				}
				else
				{
					$this->mapRectangles[$count]['clickable'] = true;
				}
			}
			$this->adjustCenterCoords($lat1, $lng1);
			$this->adjustCenterCoords($lat2, $lng2);    
			
		// if
		}
	
	// method
	}
	
	/**
	* @function     calculateDistance
	* @param        $lat1 : string (latitude location 1)
	*               $lng1: string (longitude location 1)
	*               $lat2 : string (latitude location 2)
	*               $lng2: string (longitude location 2)
	*               $unit : km (killometers), m (miles), n (nautical miles), i (inch)
	* @description  calculates distance between two locations in given unit (default kilometers)
	*/
	public function calculateDistance($lat1, $lng1, $lat2, $lng2, $unit="km"){
	
		$radius = 6371; // mean radius of the earth in kilometers
		$lat1 = (float)$lat1;
		$lat2 = (float)$lat2;
		$lng1 = (float)$lng1;
		$lng2 = (float)$lng2;
		
		// calculation of distance in km using Great Circle Distance Formula
		$dist = $radius *
				acos( sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
					  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2) - deg2rad($lng1)) );
		
		switch ( strtolower($unit) )
		{
			case 'm' :     // miles
				$dist = $dist / 1.609;
				break;
			case 'n' :     // nautical miles
				$dist = $dist / 1.852;
				break;
			case 'i' :     // inch
				$dist = $dist * 39370;
				break;
		}
		
		return $dist;
	
	// method
	}
	
	public function show( $variables ){
	
		global $bento;
		
		// Set the variables
		if( isset( $variables["width"] ) ){ $this->public->maxWidth = $variables["width"]; } else { return false; }
		if( isset( $variables["height"] ) ){ $this->public->maxHeight = $variables["height"]; } else { return false; }
		
		// Set the behaviour properties
		if( isset( $variables["zoom"] ) ){ $this->public->zoomLevel = $variables["zoom"]; }
		if( isset( $variables["background"] ) ){ $this->public->backgroundColor = $variables["background"]; }
		if( isset( $variables["draggable"] ) ){ $this->public->mapDraggable = $variables["draggable"]; }
		if( isset( $variables["double_click_zoom"] ) ){ $this->public->double_click_zoom =$variables["double_click_zoom"]; }
		if( isset( $variables["wheel_zoom"] ) ){ $this->public->scroll_wheel_lock =$variables["wheel_zoom"]; }
		if( isset( $variables["default_ui"] ) ){ $this->public->showDefaultUI =$variables["default_ui"]; }

		// Set the control properties
		if( isset( $variables["control"] ) && isset( $variables["control"]["scale"] ) ){ $this->showScaleControl($variables["control"]["scale"]); }
		if( isset( $variables["control"] ) && isset( $variables["control"]["street_view"] ) ){ $this->showStreetViewControl($variables["control"]["street_view"]); }
		if( isset( $variables["control"] ) && isset( $variables["control"]["type"] ) ){ $this->showMapTypeControl(true,$variables["control"]["type"]); }
		if( isset( $variables["control"] ) && isset( $variables["control"]["navigation"] ) ){ $this->showScaleControl($variables["control"]["navigation"]); }
		
		// This is for info windows
		if( isset( $variables["info_window"] ) && isset( $variables["info_window"]["behaiviour"] ) ){ $this->setInfoWindowBehaviour($variables["info_window"]["behaiviour"]); }
		if( isset( $variables["info_window"] ) && isset( $variables["info_window"]["trigger"] ) ){ $this->setInfoWindowTrigger($variables["info_window"]["trigger"]); }

		// Check if we have markers
		//if( isset( $variables["markers"] ) && is_array($variables["markers"]) );
		
		// Show the map
		$this->showMap(true);

	// method
	}
	
	// This will turn on w3c geolocation
	public function lookup(){
	
		global $bento;
	
		// We loaded it, so we need the js
		$this->public->lookup = true;
		
		// Ad the files
		$bento->add_js(
						array(
							"file"	=>	"http://maps.google.com/maps/api/js?sensor=false"
						)
					);

		$bento->add_js(
						array(
							"file"	=>	"http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/src/infobox.js"
						)
					);		

		$bento->add_js(
						array(
							"name"	=>	"MooGeo"
						)
					);		

		$bento->add_js(
						array(
							"name"	=>	"map"
						)
					);

	// public
	}
	
	// This will turn on w3c geolocation
	public function location(){
	
		global $bento,$form;
	
		// We loaded it, so we need the js
		$this->addMarker( array("map_id"	=>	$this->private->count, "address"	=> $this->address() . " " . $this->city() . " " . $this->region() . " " . $this->private->country() . " " . $this->post() ) );

	// public
	}

	/**
	* @function     showMap
	* @description  Displays the Google Map on the page
	*/
	public function showMap($zoomToBounds = true){
	
		global $bento;
		
		// Finish setting stuff up
		$this->public->showDefaultUI ? $_disableDefaultUI = false : $_disableDefaultUI = true;
		$this->public->showMapTypeControl ? $_mapTypeControl = true : $_mapTypeControl = false;
		$this->public->showNavigationControl ? $_navigationControl = true : $_navigationControl = false;
		$this->public->showScaleControl ? $_scaleControl = true : $_scaleControl = false;
		$this->public->showStreetViewControl ? $_streetViewControl = true : $_streetViewControl = false;
		$this->public->mapDraggable ? $_mapDraggable = true : $_mapDraggable = false;
		$this->public->enableScrollwheelZoom ? $_scrollwheelZoom = true : $_scrollwheelZoom = false;
		$this->public->enableDoubleClickZoom ? $_disableDoubleClickZoom = false : $_disableDoubleClickZoom = true;
	
		// We loaded it, so we need the js
		$bento->add_js(
						array(
							"name"	=>	"map"
						)
					);
			
		// Add the js file we need		
		$bento->add_js(
						array(
							"file"	=>	"http://maps.google.com/maps/api/js?sensor=false"
						)
					);
		
		// Assign js variables	
		$this->public->zoom = $this->public->zoomLevel;
		$this->public->lat = $this->private->centerLat;
		$this->public->lng = $this->private->centerLng;
		$this->public->mapTypeId = $this->public->mapType;
		$this->public->disableDefaultUI = $_disableDefaultUI;
		$this->public->mapTypeControl = $_mapTypeControl;
		$this->public->mapTypeControlOptions->style = "google.maps.MapTypeControlStyle." . $this->public->mapTypeControlStyle;
		$this->public->navigationControl = $_navigationControl;
		$this->public->navigationControlOptions->style = "google.maps.NavigationControlStyle." . $this->public->navigationControlStyle;
		$this->public->scaleControl = $_scaleControl;
		$this->public->streetViewControl = $_streetViewControl;
		$this->public->mapDraggable = $_mapDraggable;
		$this->public->scrollwheel = $_scrollwheelZoom;
		$this->public->disableDoubleClickZoom = $_disableDoubleClickZoom;
		
		// Optional background
		if ( $this->public->mapBackgroundColor != "" ) { $this->public->backgroundColor = $this->public->mapBackgroundColor; }
		
		// create div for the map canvas
		echo "<div id=\"" . $this->public->canvas . "_" . $this->private->count ."\" class=\"" . $this->public->canvas . "\" style=\"width: " . $this->public->mapWidth . "px; height: " . $this->public->mapHeight. "px;\"></div>\n";
	
		// Add it up
		$this->private->count++;
	
	// method
	}

	//-------------------------------------------------------------------------------//
	//
	//	20. open() - This will create a form
	//
	//-------------------------------------------------------------------------------//
		public function form( $type="search" ){	
	
		global $form;
		
		// Set this up 
		if( $type == "search" ){
		
			$variables["id"] = ( !isset($variables["id"]) ) ? "form_map_" . $type : $variables["id"] . "_" . $type;
			$variables["handler"] = ( !isset($variables["handler"]) ) ? "map->search" : $variables["handler"];
			$variables["javascript"]["onsuccess"] = ( !isset($variables["javascript"]["onsuccess"]) ) ? "bento.map.form.success( bento.form.response.variables );" : $variables["javascript"]["onsuccess"];	
			$variables["javascript"]["onfail"] = ( !isset($variables["javascript"]["onfail"]) ) ? "bento.map.form.fail( bento.form.response.variables );" : $variables["javascript"]["onfail"];

			// Open the form
			$form->open( $variables );

		} else {
		
			$variables["id"] = ( !isset($variables["id"]) ) ? "form_map_" . $type : $variables["id"] . "_" . $type;
			$variables["handler"] = ( !isset($variables["handler"]) ) ? "map->update" : $variables["handler"];

			// Open the form
			$form->open( $variables );

		// if		
		}

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. close() - This will close a form
	//
	//-------------------------------------------------------------------------------//
	public function close(){
	
		global $form;
		
		$form->close();	

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. form() - Handles looking up new stuffs
	//
	//-------------------------------------------------------------------------------//
	public function lookupForm(){	
	
		global $bento,$form;

		// Make sure that we have something to lookup
		if( $this->public->lookup ){
		
			// Start the output buffer
			ob_start();

			// This will open and close the form for updating via w3c		
			$this->form("update");
			
			$form->hidden( 
						array(
							"id"	=>	"map_lat",
							"name"	=>	"map_lat",
							"value"	=>	$this->lng()
						)
					);
	
			$form->hidden( 
						array(
							"id"	=>	"map_lng",
							"name"	=>	"map_lng",
							"value"	=>	$this->lng()
						)
					);
	
			$form->hidden( 
						array(
							"id"	=>	"map_address",
							"name"	=>	"map_address",
							"value"	=>	$this->address() 
						)
					);
					
			$form->hidden( 
						array(
							"id"	=>	"map_community",
							"name"	=>	"map_community",
							"value"	=>	$this->community() 
						)
					);
					
			$form->hidden( 
						array(
							"id"	=>	"map_city",
							"name"	=>	"map_city",
							"value"	=>	$this->city()
						)
					);
					
			$form->hidden( 
						array(
							"id"	=>	"map_region",
							"name"	=>	"map_region",
							"value"	=>	$this->region() 
						)
					);
					
			$form->hidden( 
						array(
							"id"	=>	"map_country",
							"name"	=>	"map_country",
							"value"	=>	$this->country() 
						)
					);
					
			$form->hidden( 
						array(
							"id"	=>	"map_post",
							"name"	=>	"map_post",
							"value"	=>	$this->post() 
						)
					);

			// Close the form
			$this->close();
			
			// This will open and close the form for updating via w3c		
			$this->form("search");	
			
			// The search field
			$form->hidden( 
						array(
							"id"	=>	"map_id",
							"name"	=>	"map_id",
							"value"	=>	$this->private->search_id 
						)
					);
					
			// The search field
			$form->hidden( 
						array(
							"id"	=>	"map_search",
							"name"	=>	"map_search",
							"value"	=>	$this->address() . " " . $this->city()  . " " . $this->region() . " " . $this->country() 
						)
					);
					
			// Close the form
			$this->close();
	
			// Take the contents from the php files		
			$html = ob_get_contents();
			ob_end_clean();

			// Check for the closing body tag and insert the code
			$bento->html = str_replace("</body>", $html . "</body>",$bento->html);
		
		// if
		}

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. search() - Handles looking up new stuffs
	//
	//-------------------------------------------------------------------------------//
	public function search( $variables ){	

		global $form,$bento;

		// Add the marker
		$this->addMarker(
						array( 
							"map_id"	=>	$form->post("map_id"),
							"address"	=>	$form->post("map_search"), 
							"icon"	=>	"http://google-maps-icons.googlecode.com/files/museum-historical.png",
							"clickable"	=>	false
							)
						);
						
		// Check if we found anything
		if( 
			isset( $this->public->map->marker ) && count( $this->public->map->marker ) > 0
			) {
			
			// Set this up
			$variables = array(
								"map_id"	=>	 $this->public->canvas . "_" . $form->post("map_id"),
								"zoom"	=>	14,
								"type"	=>	$this->public->map->marker[0]["type"],
								"result"	=>	$this->public->map->marker[0]["result"],
								"marker"	=>	$this->public->map->marker,
								"image"	=>	$this->image( array( "width"	=>	120, "height"	=>	120, "zoom"	=>	"13","align"	=>	"right", "address"	=>	$form->post("map_search"), "markers"	=>	array(array("color"	=>	"red",	"address"	=>	$form->post("map_search"), "label"	=>	"A") ) ), false )
							);
			
			// Respond to javascript			
			$form->response(
						array(
							"response"	=>	true,
							"message"	=>	"Address found.",
							"variables"	=>	$variables
							)
						);
		
		// Otherwise nope
		} else {
		
			$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"No address found.",
							"variables"	=>	array(
												"type"	=>	NULL 
												)
											)	
										);
			
		// if
		}
			
	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. update() - Updates the location
	//
	//-------------------------------------------------------------------------------//
	public function update( $variables ){	
	
		global $form;
	
		// Update the address
		$_SESSION["bento"]["map"]["geolocation"]["lat"] = $form->post("map_lat");
		$_SESSION["bento"]["map"]["geolocation"]["lng"] = $form->post("map_lng");
		$_SESSION["bento"]["map"]["geolocation"]["address"] = $form->post("map_address");
		$_SESSION["bento"]["map"]["geolocation"]["community"] = $form->post("map_community");
		$_SESSION["bento"]["map"]["geolocation"]["city"] = $form->post("map_city");
		$_SESSION["bento"]["map"]["geolocation"]["region"] = $form->post("map_region");
		$_SESSION["bento"]["map"]["geolocation"]["country"] = $form->post("map_country");
		$_SESSION["bento"]["map"]["geolocation"]["post"] = $form->post("map_post");

		// Respond to javascript			
		$form->response(
						array(
							"response"	=>	true,
							"message"	=>	"Location updated.",
							"variables"	=>	$_SESSION["bento"]["map"]["geolocation"]
							)
						);

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. lat() - returns the community
	//
	//-------------------------------------------------------------------------------//
	public function lat(){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["lat"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["lat"];

		// if
		} else {
		
			return false;
			
		}

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. address() - returns the community
	//
	//-------------------------------------------------------------------------------//
	public function lng(){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["lng"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["lng"];

		// if
		} else {
		
			return false;
			
		}

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. address() - returns the community
	//
	//-------------------------------------------------------------------------------//
	public function address(){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["address"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["address"];

		// if
		} else {
		
			return false;
			
		}

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. community() - returns the community
	//
	//-------------------------------------------------------------------------------//
	public function community( ){	
	
		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["community"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["community"];

		// if
		} else {
		
			return false;
			
		}

	// method
	}
	//-------------------------------------------------------------------------------//	

	//-------------------------------------------------------------------------------//
	//
	//	20. community() - returns the city
	//
	//-------------------------------------------------------------------------------//
	public function city( ){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["city"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["city"];

		// if
		} else {
		
			return false;
			
		}

	// metjod
	}

	/**
	* @function     regions
	* @description  constructor
	* @param	$sensor : boolean
	*/
	public function region( ){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["region"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["region"];

		// if
		} else {
		
			return false;
			
		}

	// method
	}

	/**
	* @function     returns the country of the users
	* @description  constructor
	* @param	$sensor : boolean
	*/
	public function country( ){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["country"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["country"];

		// if
		} else {
		
			return false;
			
		}
		
	// method
	}

	/**
	* @function     postal codes
	* @description  constructor
	* @param	$sensor : boolean
	*/
	public function post(){	

		// Make sure it's good
		if( isset($_SESSION["bento"]["map"]["geolocation"]["post"]) ){

			return $_SESSION["bento"]["map"]["geolocation"]["post"];

		// if
		} else {
		
			return false;
			
		}
		
	// method
	}

	/**
	* @function     image
	* @description  loads a static map
	* @param	$sensor : boolean
	*/
	public function image( $variables, $echo = true ){	
	
		global $bento;
	
		// Default to the current address
		if( !isset($variables["address"]) ){ $variables["address"] = $this->city() . "," . $this->region() . "," . $this->private->country(); }
		if( !isset($variables["align"]) ){ $variables["align"] = ""; }
		if( !isset($variables["zoom"]) ){ $variables["zoom"] = 8; }

		$bento->add_css(
					array(
						"name"	=>	"map"
					)				
		);

		// Check for markers
		$markers = "";
		
		// Check it
		if( isset($variables["markers"]) && is_array( $variables["markers"] ) ){

			// Loop through the markers
			foreach( $variables["markers"] as $marker ){
								
				 $markers .= "&markers=color:" . $marker["color"] . "|label:" . $marker["label"] . "|" . $marker["address"];
				
			// foreac
			}

		// if
		}

		// There you got
        $tmp = '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $variables["address"] . '&zoom=' . $variables["zoom"] . '&size=' . $variables["width"] . 'x' . $variables["height"] . $markers . '&maptype=roadmap&sensor=false" class="bento_map_image">';

		// here's the output
		return $echo ? print $tmp : $tmp;		

	// method
	}

// class
}
?>
