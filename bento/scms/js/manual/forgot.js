// Set this up
bento.scms.forgot = {}

// Complete the forgot
bento.scms.forgot.complete = function( message ){
	
	// There is the modal
	if( bento.scms.is_modal ){
	
		// Close the window
		bento.scms.modal.close({'text':message,'class':'success'});
	
	// Reload it (for apps and stuffs)
	} else {
		
		// Open the message
		bento.message.open({'text':bento.form.response.message,'class':'success'});
	
	// if
	}
	
// method
}

// Just a test function
bento.scms.forgot.setup = function(tx, rs) {
  
	// Update
	if( rs.rows.length > 0 ){ 
  
  		// Store it
		$('email').value = rs.rows.item(0).email;
		
		// Submit the login form
		(function(){ bento.form.submit('scms_forgot_form'); }).delay(500);
	
	// if
	} 
	  
// unction	
}

// Set this up
window.addEvent('domready',function(){

		// Open the database
		bento.db.open( "bento_scms" );
		
		// Select text
		bento.db.sql({
					db:"bento_scms",
					sql:"SELECT * FROM account", 
					function:bento.scms.forgot.setup 
					});

// method
});