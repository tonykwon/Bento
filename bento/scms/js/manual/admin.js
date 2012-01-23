// Setup the admin
bento.scms.admin = {};
// Remove the junk
bento.scms.admin.clear = function(){
	// Clear the text and texareas
	$$('input[type=text],input[type=password],textarea').each(function(e){
		// Clear it up
		if( e.hasClass('clear') ){
			e.value = '';
		// if
		}
	});
	// Reset the select boxes
	$$('select').each(function(e){
		e.selectedIndex = 0;
	});
// if
}
// Remove the junk
bento.scms.admin.slug = function(){ 
	// Clear this up
	var value = new String( $('name').value ); 
		value = value.replace(/ /g ,'_');
		value = value.replace(/[^a-zA-Z_]+/g,'');
		value = value.toLowerCase();
		// Return it
		$('slug').value = value;
		// Add it to the url
		if( $('url') ){
			$('url').value = value.replace(/_/g,'-');
		// if
		}
		// Add it to the anchor
		if( $('anchor') ){
			$('anchor').value = $('name').value;
		// if
		}
		// Add it to the subject
		if( $('subject') ){
			$('subject').value = $('name').value;
		// if
		}
		// Add it to the subject
		if( $('web') ){
			$('web').value = $('name').value;
		// if
		}
		// Add it to the subject
		if( $('content') ){
			$('content').value = $('name').value;
		// if
		}		
// method
}
// This is going to build slugs for us
window.addEvent('domready',function(){
										// Check if we have a slug field
										if( $('name') && $('slug') ){
											// Check it
									   		$('name').addEvent('keypress',function(e){
																				   new Observer('name',bento.scms.admin.slug);
																				   // method
																				   });
										// if
								   		}
								   });