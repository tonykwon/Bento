// Set this up
bento.scms.login = {'email':'','password':''}

// Login
bento.scms.login.check = function(tx, rs) {
  
  // Update
  if( rs.rows.length > 0 ){
	  
	// Check this out
	bento.db.sql({
				db:"bento_scms",
				sql:"UPDATE account set email='" + bento.scms.login.email + "', password = '" + bento.scms.login.password + "' where id=1" 
				});
  
  // Loop through the output
  } else {
	  
	// Check this out
	if( bento.scms.is_mode == 'app' || bento.scms.is_mode == 'mobile' ){ 
  
		// Check this out
		bento.db.sql({
					db:"bento_scms",
					sql:"INSERT INTO account(email, password) VALUES ('" + bento.scms.login.email + "','" + bento.scms.login.password + "')" 
					});
  
  	// if
	}
  
  // if
  }

// method
}

// Just a test function
bento.scms.login.login = function(tx, rs) {
  
	// Update
	if( rs.rows.length > 0 ){ 
  
  		// Store it
		$('email').value = rs.rows.item(0).email;
		$('password').value = rs.rows.item(0).password;
		
		// Submit the login form
		(function(){ bento.form.submit('scms_login_form'); }).delay(500);
	
	// if
	} 
	  
// unction	
}

// Save login information
bento.scms.login.save = function(){
	
	// Check it
	bento.scms.login.email = $('email').value;
	bento.scms.login.password = $('password').value;
	
	// Try it for webkit
	try{
		
		// Select text
		bento.db.sql({
					db:"bento_scms",
					sql:"SELECT * FROM account", 
					function:bento.scms.login.check 
					});
				
	// For nonsupportive browsers
	} catch( err ){}
	
	// There is the modal
	if( bento.scms.is_modal ){
	
		// Close the window
		bento.scms.modal.close(true);
	
	// Reload it (for apps and stuffs)
	} else {
		
		// Reload the window
		document.location.href = "/";
	
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

		// Select text
		bento.db.sql({
					db:"bento_scms",
					sql:"SELECT * FROM account", 
					function:bento.scms.login.login 
					});						

// method
});