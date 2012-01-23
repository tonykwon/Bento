// This will open the agreement boxes
bento.scms.modal.open = function (vars ){
	
	// Close this window
	bento.scms.modal.close(false);
	
	// Open another modal
	window.parent.bento.scms.modal.open( vars );

// method
}
// This will close the modal window
bento.scms.modal.close = function ( options ) {
	
	// Let's get rid of the buttons
	$$('.button').each(function(e){
								e.setProperty('disabled',true);
								});
	
	// Check what we're doing here
	if( options && typeof(options) == 'object' ){
		
		// Check it
		if( options.text ){
			
			// Check it
			if( !options.class ){ options.class = 'success'; }
			if( !options.clear ){ options.clear = true; }
			
			// Close it up
			window.parent.bento.message.open({'text':options.text,'class':options.class,'clear':options.clear});
		
		// if
		}
		
	// Check it we're reloading
	} else if ( options && typeof(options) == 'boolean' ){
	
		options = {reload:true};
	
	// We're not reloading
	} else {
	
		options = {reload:false};
		
	// if
	}
	
	// Check if we're reloading the page or not
    if ( options.reload ) {
		
		// Reloat
        window.parent.location.reload();
		
    } else {
		
		// Close the modal window
        window.parent.bento.scms.modal.close();
		
		// If there is a feed, 
		try{
			
			// Fire it
			window.parent.bento.scms.feed.fire(true);
		
		// nothing to do
		} catch( err ){}
	
	// if
	}
	
// method
}
// Here is a problem
bento.scms.go = function (url) {
	
    window.parent.document.location.href = url;

// method
}

// Do some fany moving to get the form field buttons lined up
window.addEvent('domready',function(){
	
	// Help us move things around
	size = window.getSize()
	buttons = false;
	
	// Check if we've got some content
	$$('div.buttons,span.buttons').each(function(e){
								 	buttons = true;
									e.setStyle('top',size.y-55);
								 });	
	
	// Check it
	if( buttons ){
		
		// Check if we're in the admin section
		if( bento.scms.modal.page == 'Admin' ){
			
			admin = 40;
			
		} else {
			
			admin = 0;
			
		}
	
		// Check if we've got some content
		$$('.content').each(function(e){
										e.setStyle('display','block');
										e.setStyle('height',size.y-95-admin);
									 });
		
		
	
		// Fix up the alerts
		$$('#bento_message_container,#bento_message_html').each(function(e){
																		 e.setStyle('width',size.x-20);
																		 });

	// if
	} else {

		// Check if we've got some content
		$$('.content').each(function(e){
										e.setStyle('display','block');
										e.setStyle('height',size.y-40);
									 });
		
	// if
	}

// domready
});