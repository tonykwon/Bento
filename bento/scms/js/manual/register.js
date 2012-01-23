// Set this up
bento.scms.register = {'email':'','password':''}

// register
bento.scms.register.check = function(tx, rs) {
  
  // Update
  if( rs.rows.length > 0 ){
	  
	// Check this out
	bento.db.sql({
				db:"bento_scms",
				sql:"UPDATE account set email='" + bento.scms.register.email + "', password = '" + bento.scms.register.password + "' where id=1" 
				});
  
  // Loop through the output
  } else {
	  
	// Check this out
	if( bento.scms.is_mode == 'app' || bento.scms.is_mode == 'mobile' ){ 
	  
		// Check this out
		bento.db.sql({
					db:"bento_scms",
					sql:"INSERT INTO account(email, password) VALUES ('" + bento.scms.register.email + "','" + bento.scms.register.password + "')" 
					});
  
  	// if
	}
  
  // if
  }

// method
}

// Save register information
bento.scms.register.save = function( message ){
	
	// Check it
	bento.scms.register.email = $('email').value;
	bento.scms.register.password = $('password').value;
	
	// Try it for webkit
	try{
		
		// Select text
		bento.db.sql({
					db:"bento_scms",
					sql:"SELECT * FROM account", 
					function:bento.scms.register.check 
					});
				
	// For nonsupportive browsers
	} catch( err ){}
	
	// There is the modal
	if( bento.scms.is_modal ){
	
		// Close the window
		bento.scms.modal.close({'text':message,'class':'success'});
	
	// Reload it (for apps and stuffs)
	} else {
		
		// Reload the window
		bento.message.open({'text':message,'class':'success'});
		
		// In 6 seconds forward them to the home page
		(function(){
					document.location.href = "/";
					}).delay(6000);
	
	// if
	}
	
// method
}

// Set this up
window.addEvent('domready',function(){

		// Open the database
		bento.db.open( "bento_scms" );
		
		// Create the table
		bento.db.create({
						db:"bento_scms", 
						sql:"account(id INTEGER PRIMARY KEY ASC, email TEXT, password TEXT)"
						});				

// method
});