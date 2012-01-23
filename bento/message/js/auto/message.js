// Hijack the alerts
/*
alert = function( message ){

	// There you go
	debug( message );
	bento.message.open({'text':message,'clear':true});

// method
}*/

// This creates nice messages using the form messaging system
bento.message.log = new Array();
bento.message.open = function( options ){
	
	// Set this up
	if( !options.id ){ options.id = (parseInt(new Date().getTime(),10)); }

	// Check what type of message we're throwing up
	if( !options.class ){ options.class = 'error'; } 
	if( !options.delay ){ options.delay = 0; } 
	if( !options.timeout ){ options.timeout = 12000; } 
	
	// Clear the messages
	if( options.clear == 'undefined' || options.clear == null || options.clear == true ){ bento.message.clear(); }

	// Otherwise, 
	if( bento.storage.reserve({set:'message','id':options.id}) ){
		
		bento.message.log[ options.id ] = options.timeout;
	
		// Add a new notification
		var notification = new Element('div',{'id': 'bento_message_' + options.id, 'class':'bento_message_notification ' + options.class }).addEvent('click',bento.message.close);
	
		// Set the text
		$('bento_message_html').adopt(notification);
	
		// Open it up
		if( !bento.message.visible ){
		
			setTimeout("bento.message.show();",options.delay);
			
		// if
		}
	
	// if
	}

	// For options and text
	if( !options.html ){
	
		// Add a bit of a space
		options.html = '';
		
		// Check what we're working with here
		if( options.text ){

			// There you go
			options.html = options.text;

		// if
		}
		
	// if
	}
	
	// Add the message
	this.titleText = new Element('h4',{
		'class': 'bento_message_text',
		html: options.html
	}).inject(notification);

// method
}

// This is a friendly alert
bento.message.close = function(){
	
	// Close the message out
	bento.message.hide(); 
	bento.message.clear();
	
// method
}

// This is a friendly alert
bento.message.clear = function(){
	
	$('bento_message_html').set('html','&nbsp;');

// method
}

// This is a friendly alert
bento.message.hide = function(){
	
	// Close the message out
	$('bento_message_html').fade(0); 
	$('bento_message_html').setStyle('display','none'); 
	bento.message.visible = false;

// method
}

// This is a friendly alert
bento.message.show = function(){
	
	// Close the message out
	$('bento_message_html').fade(.99); 
	$('bento_message_html').setStyle('display','block'); 
	bento.message.visible = true;

// method
}

// Check for removing notices
bento.message.periodical = function(){
	
	// Get the messages we've opened already
	var messages = bento.storage.list({set:'message'});
	
	// Loop through the messages
	for(var i=0;i<messages.length;i++){
		
		// Check it
		if( parseInt(messages[ i ].time,10)+parseInt(bento.message.log[ messages[ i ].id ]) < parseInt(new Date().getTime(),10) ){
			
			// Destroy the notification
			if( $( 'bento_message_' + messages[ i ].id ) ){
				$( 'bento_message_' + messages[ i ].id ).destroy();
			// if
			}
			
		// if
		}
	
	// for
	}
	
	// Close it if there's nothing left
	if(  $('bento_message_html') && $('bento_message_html').get('html') == '&nbsp;' ){
		bento.message.hide();
	// if
	}
	
// method
}

// When the page loads up
bento.message.domready = function(){

	// Create the loading div
	var container = new Element('div',{'class':'bento_message_container'}).inject(document.body,'top');
	
	// Load a new element in
	new Element('div',{'id':'bento_message_html','class':'container'}).inject( container ).set('opacity','0').setStyle('display','block');
	
	// Give it a couple seconds before we fire it up
	(function(){
	
		// Check if we're
		for(var i=0;i<bento.message.messages.length;i++){
			
			bento.message.open( bento.message.messages[i] );
			
		// for
		}
		
	}).delay(500);
	
// method
};
