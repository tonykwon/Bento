// This will load the feed form into the app
bento.scms.app.load = function(){
	// This is what we need to load in to the apge
	var elements = new Array('template','feed_form');
	// Loop through the options
	for(var i=0;i<elements.length;i++){
		// Check if we have this
		if( bento.scms.app[ elements[i] ] ){
			// Add the feed form into the body
			new Element('span').set('html',bento.scms.app[ elements[i] ] ).inject(document.body);
		// if
		}
	// for
	}
	// Now load all the stuff
	bento.form.load();
// method
}
// Let's unload the page
bento.scms.app.open = function(url){
	// Unload it
	document.location.href = url;
// method
}