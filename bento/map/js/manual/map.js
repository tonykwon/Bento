// Setup the map
bento.map.form = {};
bento.map.map = {};
bento.map.bounds = {};
bento.map.loaded = false;
bento.map.show = function( id ){

	// Setup the map
	var latlng = new google.maps.LatLng(bento.map.lat, bento.map.lng);

	// Set up the control options
	bento.map.mapTypeId = google.maps.MapTypeId[ bento.map.mapTypeId ];
	bento.map.mapTypeControlOptions = {style: google.maps.MapTypeControlStyle[ bento.map.mapTypeControlOptions ]};
	bento.map.navigationControlOptions = {style: google.maps.NavigationControlStyle[ bento.map.mapTypeControlOptions ]};
	bento.map.center = latlng;

	// Set it all up
	bento.map.bounds[ id ] = new google.maps.LatLngBounds();
	bento.map.map[ id ] = new google.maps.Map( $( id ), bento.map);
	bento.map.currentInfoWindow = null;  
 
	// Check it
	bento.map.map[ id ].setZoom( bento.map.zoom );
 
 	// Check if it's clickable
	if( bento.map.clickable ){
 
		google.maps.event.addListener(bento.map.map[ id ], 'click', function() { if (currentInfoWindow != null) { currentInfoWindow.close(); } });
	
	// if
	}
	
	// Now let's load some markers
	bento.map.addMarkers( id );
	
	// Now let's load some markers
	bento.map.addCircles( id );

// method
}

// Setup the map
bento.map.form.success = function( variables ){
	
	// Check it
	if( variables.marker && variables.marker.length > 0 ){
		bento.map.clearMarkers( variables.map_id );
		bento.map.marker = variables.marker;
		bento.map.addMarkers( variables.map_id );
	// if
	}
	
	// Check it
	if( variables.circle && variables.circle.length > 0 ){
		bento.map.clearCircles( variables.map_id );
		bento.map.circle = variables.circle;
		bento.map.addCircles( variables.map_id );
	// if
	}
	
	// Check it
	if( variables.rectangle && variables.rectangle.length > 0 ){
		bento.map.clearRectangles( variables.map_id );
		bento.map.rectangle = variables.rectangle;
		bento.map.addRectangles( variables.map_id );
	// if
	}

	// Check it
	if( variables.action ){
		eval( variables.action );
	// if
	}
	
	// Set the zoom if applicable
	if( variables.zoom ){

		// Check it
		bento.map.map[ variables.map_id ].setZoom( bento.form.response.variables.zoom );
	
	// if
	}

// form
}

// Setup the map
bento.map.form.fail = function( response ){
	// Check it
	if( response.variables.action ){
		eval(response.variables.action);
	// if
	}
// form
}

// Check this
bento.map.addMarkers = function(){
	
	if( !bento.map.loaded ){
		setTimeout("bento.map.addMarkers()",500);
		return;	
	}
	
	// Check it
	if( bento.map.marker ){

		// Loop through the markers
		for(var i=0; i<bento.map.marker.length;i++){
			
		
			bento.map.marker[i].latLng = new google.maps.LatLng(bento.map.marker[i].lat, bento.map.marker[i].lng);
			bento.map.marker[i].marker = new google.maps.Marker({
															 position: bento.map.marker[i].latLng,
															 title: bento.map.marker[i].title,
															 icon: bento.map.marker[i].icon,
															 map: bento.map.map[ bento.map.marker[i].map_id ]
															});
			
			// Check if there's an info window
			if( bento.map.marker[i].info ){
			
				// Add the info window
				bento.map.marker[i].marker.i = i;
				
				// This is the info
				bento.map.marker[i].infoWindow = new InfoBox({
																content: bento.map.marker[i].info,
																boxStyle: {'background-color': "#FFF",'opacity': '0.9','border': '1px solid #000000'}
															});
				
				// Add the close listener
				google.maps.event.addListener(bento.map.marker[i].marker, 'click',
														 function() {
															 i = this.i;
															 if( bento.map.currentInfoWindow ){ bento.map.currentInfoWindow.close(); } 
															 bento.map.marker[i].infoWindow.open(bento.map.map[ bento.map.marker[ i ].id ], bento.map.marker[i].marker);
															 bento.map.currentInfoWindow = bento.map.marker[i].infoWindow;
															});

			// if
			}
		
			bento.map.bounds[ bento.map.marker[i].map_id ].extend( bento.map.marker[i].latLng );

			// Fit it in 	
			bento.map.map[ bento.map.marker[i].map_id ].fitBounds( bento.map.bounds[ bento.map.marker[i].map_id ] );

		// for
		}
		
	// if
	}

// method
}

// Clear the markers
bento.map.clearMarkers = function( id ){
	// alert('clearing');
	if( bento.map.marker && bento.map.marker.length ){
		for(var i=0; i<bento.map.marker.length; i++){
			bento.map.clearMarker(i);
		}
		bento.map.bounds[ id ] = new google.maps.LatLngBounds();
	}
	// Clear it up
	bento.map.marker = Array();
// method
}

// Clear the markers
bento.map.clearMarker = function( i ){
	if( bento.map.marker && bento.map.marker[i] && bento.map.marker[i].marker){
		bento.map.marker[i].marker.setMap(null);
	}
	delete bento.map.marker[i];
// method
}

// Check this
bento.map.addCircles = function(){
	
	// Check it
	if( bento.map.circle ){
	
		// Loop through the markers
		for(var i=0; i<bento.map.circle.length;i++){
			
			bento.map.circle[i].latLng = new google.maps.LatLng(bento.map.circle[i].lat, bento.map.circle[i].lng);
			bento.map.circle[i].marker = new google.maps.Circle({
																center: bento.map.circle[i].latLng, 
																radius: bento.map.circle[i].radius,
																fillColor: bento.map.circle[i].fillColor,
																fillOpacity: bento.map.circle[i].fillOpacity,
																strokeColor: bento.map.circle[i].strokeColor,
																strokeOpacity: bento.map.circle[i].strokeOpacity,
																strokeWeight: bento.map.circle[i].strokeWeight,
																clickable: bento.map.circle[i].clickable,
																radius: bento.map.circle[i].radius,
																map: bento.map.map[ id ]
															  });
			
			// Check if there's an info window
			if( bento.map.circle[i].info ){
			
				// Add the info window
				bento.map.circle[i].infoWindow = new google.maps.InfoWindow({
															content: bento.map.circle[i].info,
															position: bento.map.circle[i].latLng
															});
	
				// This is for closing stuffs			
				google.maps.event.addListener (bento.map.circle[i].marker, 'click', function() {
															 var tmplat1 = bento.map.circle[i].marker.getCenter().lat()+(bento.map.circle[i].marker.getBounds().getNorthEast().lat() - bento.map.circle[i].marker.getCenter().lat())/2;
															 var tmplng1 = bento.map.circle[i].marker.getCenter().lng()+(bento.map.circle[i].marker.getBounds().getNorthEast().lng() - bento.map.circle[i].marker.getCenter().lng())/2;
															 var newpos = new google.maps.LatLng(tmplat1, tmplng1);
															 bento.map.circle[i].infoWindow.open(map);
															 bento.map.circle[i].infoWindow.setPosition(newpos);
															 bento.map.currentInfoWindow = bento.map.circle[i].infoWindow;
														});
	
			// if
			}
			
			
			// Extend the bounds
			//bento.map.bounds.extend(bento.map.circle[i].marker.getBounds().getNorthEast());
			//bento.map.bounds.extend(bento.map.circle[i].marker.getBounds().getSouthWest());
			
		// for
		}
		
		// Fit it in 
		//bento.map.map[ id ].fitBounds(bento.map.bounds);

	// if
	}

// method
}

// Now let's look it up
bento.map.doSearch = function( ){
	if( $('form_map_search') ){
		bento.form.submit('form_map_search');
	}
// if
}

// Now let's look it up
bento.map.doLookup = function( callback ){
	
	// Lookup every 60 minutes
	Cookie.write('lookup', true, {duration: 60});

	// Look it up
	new MooGeo('visitor',{
		onComplete: function(o){
			
			// Update PHP?
			if( $('map_lat').value != o.place.centroid.latitude || $('map_lng').value != o.place.centroid.longitude ){
			
				tmp = true;
			
			// if
			} else {
				
				tmp = false;
			
			// if
			}
			
			// Set the form so we can save it to php
			if( o.place.centroid.latitude ){ $('map_lat').value = o.place.centroid.latitude; }
			if( o.place.centroid.longitude ){ $('map_lng').value = o.place.centroid.longitude; }
			if( o.place.country.content ){ $('map_country').value = o.place.country.content; }
			if( o.place.admin1.content ){ $('map_region').value = o.place.admin1.content; }
			if( o.place.locality1.content ){ $('map_city').value = o.place.locality1.content; }
			if( o.place.locality2.content ){ $('map_community').value = o.place.locality2.content; }

			(new google.maps.Geocoder()).geocode({latLng: new google.maps.LatLng(o.place.centroid.latitude, o.place.centroid.longitude)}, function(resp) {
				if (resp[0]) {
					var tmp = resp[0].address_components;
					$('map_address').value =  tmp[0].long_name + ' ' + tmp[1].long_name;
				}
			});	

			// If there's something else we want to do, do it
			if( callback ){
				eval( callback(o.place) );
			// if
			}
			
			// Update PHP
			if( tmp ){ bento.form.submit('form_map_update'); }
			
		// on complete
		}
	});
  
// method					
}

// Start up the map
bento.map.domready = function(){
	
	// Check if there's a map on-page
	$$("." + bento.map.canvas ).each(function(e){
		// Show the map
		bento.map.show( e.id );
	// if
	});
	
	// Do it
	if( !Cookie.read('lookup') ){
		setTimeout("bento.map.doLookup();",500);
	// if
	}
	
	// Set that we're bonne
	bento.map.loaded = true;

// method
};