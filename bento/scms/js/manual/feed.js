// Check if it's triggered or not
bento.scms.feed.log = 0;

// Check this out
bento.scms.feed.check = function(){
	
	// Check if we have the appropriate response
	try{
	
		// Set the response from 
		response = bento.form.response.variables;
	
	} catch (e){ debug('scms feed response malformed. server side error perhaps?'); return; }
	
	// Check if updating the variables
	if( response && response.time ){
		bento.scms.feed_time = response.time;
	}
	
	// Check if there are new html elements
	if( response && response.html ){
		
		// Loop through the the feed html
		for( var i=0; i<response.html.length; i++ ){
		
			bento.scms.feed.html[ bento.scms.feed.html.length ] = response.html[i];
		
		// for
		}
		
	// if
	}
	
	// Check if we have a response
	if( response ){
		
		// Loop through it
		['data','combined'].each(function(type){
	
			// Check if update the feed
			if( response[type] ){
				
				// Loop through the feeds
				for( var i=0; i<response[type].length; i++ ){
					
					// Actions
					if(	response[type][i].action ){
						
						// Check if there's an action on this feed
						if(	response[type][i].action ){
							
							try{
								eval( response[type][i].action  );
							} catch(err){ debug(err + "(" + response[type][i].action + ")" ); }
							
						// of
						}
					// if
					}
					
					// We don't combine when combined
					if( type != 'combined' ){
						
						// Create some html, if possible
						response[type][i].html = bento.scms.feed.combine( response[type][i] );
						
					// if
					}
				
					// Loading HTML
					if(	response[type][i].html && response[type][i].load ){
						
						try{
							bento.scms.feed.add( response[type][i].container, response[type][i].html, response[type][i].place );
						} catch (err){ debug(err + "(" + response[type][i].action + ")" ); }
						
					// if
					}
					
					// Let's remove some junk
					if( response[type][i].max && response[type][i].max > 0 && response[type][i].place != 'overwrite' ){
						
						try{
							bento.scms.feed.remove( response[type][i].max, response[type][i].container, response[type][i].place );
						} catch (err){ debug(err + "(" + response[type][i].action + ")" ); }
					
					// if
					}
					
				// for
				}
				
			// if
			}
	
		// for each
		});
	
	// if
	}
	
	// Update the screen
	bento.bento.domupdate();
			
	// Link new forms to the feed
	bento.scms.feed.trigger();
	
	// Now set that it is a comet form (in the even we searched)
	if( bento.scms.feed_type == 'push' ){
	
		// Turn this to a pull response
		bento.form.push( bento.scms.feed_form );

	// if
	}
	
// if
}

// Combined the variables and the html into one
bento.scms.feed.combine = function( feed ){
	
	// Make sure the there's html
	var html = false;
	
	// Check if we have some html for this one
	for( var i=0;i<bento.scms.feed.html.length;i++){
		
		// Check if this is the html
		if( bento.scms.feed.html[i].feed == feed.feed ){
			html = bento.scms.feed.html[i].html;
			break;
		// if
		}
		
	// for
	}
	
	// Now that we have html, let's replace it
	if( html ){
		
		// Replace ALL the variables
		for (var variable in feed) {
			// Do this one
			html = html.replace(new RegExp('<!--scms:variable:' + variable + '-->', 'gi'), feed[ variable ]);
		}
		
		return html;
		
	} else {
		
		return false;
		
	// if
	}
	
// method
}

// This will clean up excessive elements ass (for memeory)
bento.scms.feed.remove = function( max, container, place ){

	// Like this
	if( $( container ) && $( container ).getChildren().length > max ){ 	
		
		// If we put at the top we remove from the bottoms
		if( place == "top" ){
			// Something to count
			var i = 1;
			// Go through the children
			$( container ).getChildren().each(function(e){
				// If we're beyond the limit we delete
				if( i>max ){
					e.destroy();
				// if
				}
				// Count
				i++
			// looping through children
			});
		} else if( place == "bottom" ){
			// Something to count
			var i = 1;
			// Go through the children
			$( container ).getChildren().each(function(e){
				// If we're beyond the limit we delete
				if( i>=($( container ).getChildren().length-max) ){
					e.destroy();
				// if
				}
				// Count
				i++
			// looping through children
			});
		// if
		}
	// if
	}
// method
}

// This will load html into a feed div
bento.scms.feed.add = function( container, html, place ){
	
	// Like this
	if( $( container ) ){ 		
		
		// Check it
		if( place == "top" || place == "bottom" ){
			
			// Create it
			var html = Elements.from(html).inject( container, place ).setStyle('opacity',0).fade(1);
		
		} else {
			
			// Overwrite it
			$( container ).set('html',html);
			
			// This is cool
			new Fx.Elements( container ).start({
				0: {
					opacity: [0,1]
				}
			});
			
		// if
		}
	
	// if
	} else {
		
		debug('scms feed error ' + container + ' does not exist');	
	
	// if
	}
	
// method
}

// Send the check
bento.scms.feed.fire = function( manual ){
	
	// Set if we're manually submitting the form
	var manual = ( manual != null && manual != 'undefined' && manual != false );

	// Submit the form
	bento.form.submit( bento.scms.feed_form, manual );
	
// if
}	

// Turns a feed form into a search form
bento.scms.feed.variables = function( New ){
	
	// First get the existing variables, and decode them
	existing = JSON.decode( $('scms_feed_variables').value );
	
	// Get the merge
	var merged = Object.merge( existing, New );
	
	// Last, set the new variables
	$('scms_feed_variables').value = JSON.encode( merged );

// if	
}

// Turns a feed form into a search form
bento.scms.feed.search = function(){
	
	// Set that there's a new time (so we can differentiate that it's a search rather than a poll)
	bento.scms.feed.variables({time:new Date().getTime()});
	
	// Now set that it's not a comet form ( if it is )
	if( bento.scms.feed_type == 'push' ){
	
		// Turn this to a pull response
		bento.form.pull( bento.scms.feed_form );

	// if
	}
	
	// Now fire the form
	bento.scms.feed.fire(true);

// if	
}

// Check this out
bento.scms.feed.time = function(){

	// A new date to make sure we should submit it
	var g = new Date();
	
	// Check if we should look for new information
	if( g.getTime() > parseInt(bento.scms.feed.log+bento.scms.feed_time,10) ){
	
		// Get it out
		bento.scms.feed.log = g.getTime();
	
		// We can fire it
		return true;	
		
	// if
	}

	// We can't fire it
	return false;

// if
}

// If the feed is activated
bento.scms.feed.activate = function(){
	
	// Activate bentos peridical
	bento.bento.activate();
	
// if
}

// If the feed is deactivated
bento.scms.feed.deactivate = function(){
	
	// Deactivate bento's periodical
	bento.bento.deactivate(true);
	
// if
}

// Add feeds to the form
bento.scms.feed.trigger = function(){
	
	// Check if we're in the modal here
	if( !bento.scms.is_modal ){
		
		// If we're not on a modal window
		$$("form." + bento.form.ajax ).each(function(el){
			
			// Make sure it's not this form
			if( el.id != bento.scms.feed_form && bento.storage.reserve({set:'scms',id:el}) ){
				
				// Check it out
				el.addEvent('submit',function(){
												bento.form.submit( bento.scms.feed_form );
												});
			// if
			}
		
		//for
		});
		
	// otherwise
	} else if( bento.scms.is_modal && window.parent.document.getElementById( bento.scms.feed_form ) ){
		
		// If we're not on a modal window
		$$("form." + bento.form.ajax ).each(function(el){
			
			// Make sure it's not this form
			if( el.id != bento.scms.feed_form && bento.storage.reserve({set:'scms',id:el}) ){

				el.addEvent('submit',function(){
												// Submit it right away
												bento.scms.feed.fire();
												});

			// if
			}
		//for
		});
		
	// if
	}
	
// method
}

// Notify the program with any information
bento.scms.feed.notify = function( notify ){
	
	// Check if we're logged out
	if( notify.forward != false ){
		
		// Forward to the new page
		document.location.href = notify.forward;
		
	// if
	}
	
	//Set that we're cecked before
	bento.scms.logged_in = notify.logged_in;
	bento.scms.notifications = notify.notifications;
		
	// Update the notifications
	$('scms_feed_notifications').value = notify.notifications;
	
	// Check it
	if( $( bento.scms.notification_div ) ){
		
		// Update the title
		if( document.title.search(/\((.*?)\)/) > -1 ){
			
			// Check if there are numbers at the top
			document.title = document.title.substr( document.title.search(/\)/)+2 );
		
		// if
		}
			
		// Check it
		if( parseInt(bento.scms.notifications,10) == 0 ){
		
			$( bento.scms.notification_div ).removeClass('some');
			$( bento.scms.notification_div ).addClass('none');
		
		// Check it	
		} else {
	
			// Change the classes
			$( bento.scms.notification_div ).removeClass('none');
			$( bento.scms.notification_div ).addClass('some');
			
			// Update the title
			document.title = '(' + bento.scms.notifications + ')  ' + document.title;
			
		// if
		}
		
		// Update the notifications
		$( bento.scms.notification_div ).getChildren('a').set('text', bento.scms.notifications );
		
	// if
	}
	
	if( $( bento.scms.notification_div ) ){
	
		// THe notifications
		var e = $( bento.scms.notification_div );
	
		// Checl to fade in the stuff
		if( bento.scms.logged_in.scms ){
			// This is cool
			var morph = new Fx.Morph(e,{'duration':'500',link:'cancel'}).start({'opacity':1,'display':'block'});
		} else {
			// This is cool
			var morph = new Fx.Morph(e,{'duration':'500',link:'cancel'}).start({'opacity':0,'display':'none'});
		}
		
		// Hide the auth and none auth divs
		for( divType in bento.scms.div.logged_in ){
			for( authType in {'logged_in':0,'not_logged_in':0} ){
				// alert( bento.scms.div[ authType ][ divType ] + ' ' + divType + ' ' + authType + ' ' +  bento.scms.logged_in[ divType ] );
				$$("." + bento.scms.div[ authType ][ divType ] ).each(function(e){
					cond1 = new Boolean(authType == 'logged_in' && bento.scms.logged_in[ divType ] == true ); 	
					cond2 = new Boolean(authType == 'not_logged_in' && bento.scms.logged_in[ divType ] == false );
					if( cond1 == true || cond2 == true ){
						// This is cool
						var morph = new Fx.Morph(e,{'duration':'500',link:'cancel'}).start({'opacity':1,'display':'block'});
					} else {
						// This is cool
						var morph = new Fx.Morph(e,{'duration':'500',link:'cancel'}).start({'opacity':0,'display':'none'});
					}													   
				// do this
				});			
			// for
			}
		// for
		}

	// if
	}
	
// method
}

// Add this up
bento.scms.feed.domready = function(){
	
	// Tell the system we're searching
	var stub_object = new Element('span',{ 'html':$('scms_feed_variables').getProperty('value') });
	var ret_val = stub_object.get('text');
	delete stub_object;
	
	// Set the variables
	$('scms_feed_variables').value = JSON.encode(JSON.decode(ret_val));
	delete ret_val;
	
	// Update notifications when we click on them
	if( $( bento.scms.notification_div ) ){
		
		// Check this out
		$( bento.scms.notification_div ).addEvent('click',function(){
			
			// Do this
			$('scms_feed_notifications').value = 0;
			
			// Send the new information
			bento.form.submit( bento.scms.notification_form );
			
		});
		
	// if
	}
	
	// Fire this up
	(function(){bento.scms.feed.fire();}).delay(250);

// method	
}

// Fire the feed form on a periodical
bento.scms.feed.periodical = function(){

	// Make sure we haven't submitted more than one and we're doing a pull
	if( bento.scms.feed_type == 'pull' && bento.scms.feed.time() ){

		// Fire the feed
		bento.scms.feed.fire();

	// if
	}

// method
}