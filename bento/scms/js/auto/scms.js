// These are the queued actions
bento.scms.fire_actions = function(){
	// Here are the actions
	if( bento.scms.actions ){	
		// Loops through
		for(var i=0;i<bento.scms.actions.length;i++){
			try{
				eval( bento.scms.actions[i]  );
			} catch(err){ /*alert(err + "(" + bento.scms.actions[i] + ")" );*/ }
		// for
		}
	// if
	}
// fuction
}

// Add variables to the feed form
bento.scms.add_variable = function( variables ){
	// Get the vars from the field
	tmp = JSON.decode( $('scms_feed_variables').value );
	// Loop through the variables already set	
	for (var key in variables) {
		tmp[ key ] = variables[ key ];
	}
	// Set the variables
	$('scms_feed_variables').value = JSON.encode(tmp);
// method
}

// Check if there's a feed form here
bento.scms.domready = function(){
	
	// Load anything we told not to load
	$$('.' + bento.scms.div.no_load ).each(function(e){
		
		// This is cool
		var morph = new Fx.Morph(e,{ 'duration':'500', link:'cancel' });
		(function(){morph.start({'opacity':1,'display':'block'})}).delay(250);
			
	// foreach
	});
	
	// Check if we have a feed
	try{

		// Handle the loading
		bento.scms.feed.domready();
		
	// if
	} catch( err ){}
	
	// Fire the actions
	bento.scms.fire_actions();
	
// method
};

// Upsate time
bento.scms.periodical = function(){
	
	// Now look for some divs
	$$("." + bento.scms.div.timestamp ).each(function(e){
													  
		// Do some maths
		timestamp = parseInt(e.getProperty('alt'),10);
		time = parseInt(new Date().getTime()/1000,10);
		diff = time - timestamp;
		day_diff = Math.floor(diff / 86400);
		
		// Less than a day
		if (day_diff < 1 ){
			
			if( diff < 86400 ){ text = Math.floor( diff / 3600 ) + " " + bento.scms.timestamp.hours_ago; }
			if( diff < 7200 ){ text = "1 " + bento.scms.timestamp.hour_ago; }
			if( diff < 3600 ){ text = Math.floor( diff / 60 ) + " " + bento.scms.timestamp.minutes_ago; }
			if( diff < 120 ){ text = "1 " + bento.scms.timestamp.minute_ago; }
			if( diff < 60 ){ text = bento.scms.timestamp.seconds_ago; }
			if( diff < 1 ){ text = bento.scms.timestamp.just_now; }
			
		// More than a day ago
		} else {
					
			if( day_diff > 59 ){ text = bento.scms.timestamp.months_ago; }
			if( day_diff < 60 ){ text = Math.ceil( day_diff / 7 ) + " " + bento.scms.timestamp.weeks_ago; }
			if( day_diff < 7 ){ text = day_diff + " " + bento.scms.timestamp.days_ago; }
			if( day_diff == 1){ text = bento.scms.timestamp.yesterday; }
		
		// if
		}

		// Set it up
		e.set('text',text);
		
	// do this
	});
	
	// Check if we have a feed
	try{

		// Handle the loading
		bento.scms.feed.periodical();
		
	// if
	} catch( err ){}
	
// if
}