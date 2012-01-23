// Stored an item on the dom
bento.storage.container = {};
bento.storage.time = {}

// Store a new option, id
bento.storage.store = function( options ){
	
	// Convert dom elements into ids
	options = bento.storage.clean( options );
		
	// Check it out
	if(
		bento.storage.container[ options.set ] == null ||
		bento.storage.container[ options.set ] == 'undefined'
		){ 
			bento.storage.container[ options.set ] = new Array(); 
			bento.storage.time[ options.set ] = new Array(); 
		}
		
		
	bento.storage.container[ options.set ][ bento.storage.container[ options.set ].length ] = options.id;
	bento.storage.time[ options.set ][ options.id ] = parseInt(new Date().getTime(),10);

// method
}

// Stored an item on the dom
bento.storage.stored = function( options ){

	// Convert dom elements into ids
	options = bento.storage.clean( options );
	
	return (
			bento.storage.container[ options.set ] != null && 
			bento.storage.container[ options.set ] != 'undefined' && 
			bento.storage.container[ options.set ].contains( options.id )
			);
			
// method
}

// Get an ide or create one for a dom element
bento.storage.clean = function( options ){
	
	// Check if it's an element or not
	isElement = true;
	try{
		options.id.get('tag');
	} catch ( er ){
		isElement = false;
	}
	
	// If debugging is on, show it up
	if( options.debug ){
	
		try{
			console.log( 'Debugging ' + options.debug + ' ' + isElement + ' ' + typeof(options.id) );	
		} catch( err ){}
		
	// here it is
	}
	
	// Check to make sure this is a string
	if( isElement && !options.id.getProperty('id') ){
		
		// Make sure there's an id, if 
		options.id.setProperty('id',Date.now() + '_' + Math.floor(Math.random()*1000000));	

		// Assign the id
		options.id = options.id.getProperty('id');	
		
	// if
	} else if( isElement && options.id.getProperty('id')  ){
	
			
		options.id = options.id.getProperty('id');
		
	// if
	}
	
	// Return it
	return options;
	
// method
}

// Stored an item on the dom
bento.storage.reserve = function( options ){

	// Check if this exists
	if( !bento.storage.stored(options) ){
		
		// Store it then
		bento.storage.store( options );
		
		// return that it was true
		return true;
		
	// Check this out
	} else {
		
		// Return false
		return false;
		
	// if
	}
// method
}

// Remove a single stored item 
bento.storage.remove = function( options ){
	// Check to see if it
	if( options.set &&
		bento.storage.container[ options.set ] != null && 
		bento.storage.container[ options.set ] != 'undefined' 
		){
		// Remove it
		delete bento.storage.container[ options.set ];
		// Tell the world
		return true;
	// if
	}
	// Tell the world
	return false;
// method
}

// Remove all the stored items on the dom
bento.storage.empty = function( set ){
	// Goodbye
	bento.storage.container = new Object();
	// Tell it
	return true;
// method
}

// Stored an item on the dom
bento.storage.list = function( options ){
	tmp = new Array();
	// Check to see if it
	if(
		bento.storage.container[ options.set ] != null && 
		bento.storage.container[ options.set ] != 'undefined'
		){
		// Loop through them
		bento.storage.container[ options.set ].each(function(e){
			tmp[ tmp.length ] = {id:e,time:bento.storage.time[ options.set ][ e ]};
		});
	// if
	}
	return tmp;
// method
}