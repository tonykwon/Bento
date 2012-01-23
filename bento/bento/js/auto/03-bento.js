// Controls periodical
bento.bento.periodical = false;
bento.bento.automatic = true;

// This will determin a file type and include 
bento.bento.include = function( file ){
	
	// Get the type by the extension
	var type = file.substr(file.lastIndexOf('.') + 1);
	
	// Insert the include
	if( type == "js" ){
		bento.bento.js( file )
	} else if( type == "css" ){
		bento.bento.css( file );	
	// if
	}
	
// method
}

// This will include a js file
bento.bento.js = function( file ){
	
	// Create the javascript file	
	// new Element('script',{'type':'text/javascript','src':file,'defer':true}).inject(document.body,'bottom');

// method
}

// This will include a css file
bento.bento.css = function( file ){
	
	// Create the javascript file	
	new Element('link',{'href':file,'type':'text/css','rel':'stylesheet'}).inject(document.head,'bottom');

// method
}

// Run just one domready and onload event + periodical
bento.bento.fire = function( type ){
	
	// Loop through all the objects
	Object.each(bento,function(value,key){
		
		// Return on bento peridical
		if( key == 'bento' && ( type == 'periodical' || type == 'domupdate' ) ){} else {
			
		// Check it
		if( bento[ key ][ type ] ){
			
			// Check it
			if( type != 'periodical' ){
			
				debug( "bento." + key + '.' + type + '();' );
			
			// if
			}
			
			eval( "bento." + key + "." + type + "();" );
		
		// if
		}}
		
	// each
	});
	
// method
}

// Fire the event
bento.bento.events = function( e ){	
	// Try it out
	try{
		eval(e);	
	} catch( error ){
		(function(){
			bento.fire_event( e );
		}).delay(250);
	// try
	}
// if
}

// Look for new elements
bento.bento.domupdate = function(){
	
	// dom ready
	bento.bento.fire('domupdate'); 
	
// method
}

// Check if the display has resumed activity
bento.bento.activate = function( automatic ){
	
	// Update if it's an automatic override
	bento.bento.automatic = ( automatic != null || automatic != 'undefined' );
	
	// Check it
	if( bento.bento.periodical == false || !bento.bento.automatic ){ 
	
		// Check it out
		debug('Activated periodical');
	
		bento.bento.fire('periodical');
		bento.bento.periodical = bento.bento.fire.periodical(250,{},'periodical'); 
		
	// if
	}
	
// method
}

// Check if the display has gone idle (controls periodical)
bento.bento.deactivate = function( automatic ){
	
	// Update if it's an automatic override
	bento.bento.automatic = ( automatic != null || automatic != 'undefined' );
		
	// Make sure it hasn't already be deactivated
	if( bento.bento.periodical != false ){ 
	
		// Log it
		debug('Deactivated periodical');
	
		// Clear the periodical
		$clear(bento.bento.periodical);
		bento.bento.periodical = false;

	// if
	}
	
// method
}

// Look for notifications
var notifier = new Class({	
	
	options: {
		_element:window,
		_events: [[window, 'scroll'], [window, 'resize'], [document, 'mousemove'], [document, 'keydown'], [document, 'focus']],
		_timer: null,
		_idleTime: null
	},
	
	initialize: function(time, options) {
		this.setOptions( options );
		this.time = time;
		this.initObservers();
		this.setTimer();
	},
	
	initObservers: function() {
		this.options._events.each(function(e) {
			e[0].addEvent(e[1], this.onInterrupt.bind(this))
		}.bind(this))
	},
	
	onInterrupt: function() {		
		this.options._element.fireEvent('active', { idleTime: new Date() - this.options._idleTime });
		this.setTimer();
	},
	
	setTimer: function() {
		clearTimeout(this.options._timer);
		this.options._idleTime = new Date();
		var el = this.options._element;
		this.options._timer = setTimeout(function() {
			el.fireEvent('idle');
		}, this.time)
	}
}); notifier.implement(new Options);

// Get the note
new notifier(10000);

// fire it up when the dom is ready
window.addEvent('domready',function(){

	// dom ready
	bento.bento.fire('domupdate');
	bento.bento.fire('domready'); 
	
	// Firing events
	bento.bento.actions.each(function(e){
		bento.bento.events( e );
	});	
		
// domready
});

// When the window loads, all elements loaded up
window.addEvent('load',function(){
	
	// Fire the load even for all classes
	bento.bento.fire('load');
	
	// Fire the periodicals now
	bento.bento.activate(bento.bento.automatic);
	
});

// When the page is unloaded
window.addEvent('unload',function(){
	
	// Fire the load even for all classes
	bento.bento.fire('unload');
	
});

// This will chek for updates to the interface
window.addEvent('idle', function(){ bento.bento.deactivate(bento.bento.automatic); } );
window.addEvent('active', function(){ bento.bento.activate(bento.bento.automatic); } );

// Check it out
function debug( message ){

	// Make sure we can output stuff
	try{
		console.log( message );
	} catch(e) {}
	
// method	
}