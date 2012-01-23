// This will determin a file type and include it
device.include = function( file ){
	
	// Get the type by the extension
	var type = file.substr(file.lastIndexOf('.') + 1);
	
	// Insert the include
	if( type == "js" ){
		device.js( file )
	} else if( type == "css" ){
		device.css( file );	
	// if
	}
	
// method
}

// This will include a js file
device.js = function( file ){
	// Create the javascript file	
	new Element('script',{'type':'text/javascript','charset':'utf-8','src':file,'class':'device'}).inject(document.body,'bottom');
// method
}

// This will include a css file
device.css = function( file ){
	// Create the javascript file	
	new Element('link',{'href':file,'type':'text/css','rel':'stylesheet','class':'device'}).inject(document.head,'bottom');
// method
}

// Now build the interface
device.build = function( options ){
	
	// Include the variables	
	bento = options.variables;
	
	// Get the types
	var types = new Array("js","css");
	
	// Loop through the js
	for( var j=0;j<types.length;j++ ){
		// Loop through them
		for(var i=0;i<options[ types[ j ] ].length;i++){
			// Include the file
			device.include( options[ types[ j ] ][ i ] );
		// for
		}
	// for
	}

// method
}

// This will load the stuffs
device.load = function( url ){
	
	// Set this upt
	device.url = url;
	
	// Get a list of the html
	new Request.JSON({
				  headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': '*'
				  },
				  data:{
						'app':'data'  
				  },
				  onComplete: function(response) {
				  		device.build(response);
				  },
				  url: device.host + device.url,
				  method: 'GET'
				}).send();

// method
}

// Send out a request for more goodies
document.addEventListener("DOMContentLoaded", device.load( device.url ), false);  
