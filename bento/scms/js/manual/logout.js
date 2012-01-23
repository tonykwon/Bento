// Set this up
bento.scms.logout = function(){
	
	// Check if we're in the modal or not, if so close the window
	if( bento.scms.is_modal ){
	
		bento.scms.modal.close(true);

	} else {
		
		document.location.href = '/';
		
	// if
	}

// method	
}