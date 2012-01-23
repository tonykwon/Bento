// This is where we'll hold the forms
bento.form.forms = {};

// This is the ajax form handler
bento.form.text = function(e){
				
		// Prevent the submit event
		e = new Event(e).stop();
		
		// Reset that there are no problems
		bento.form.exit = false;
			
		// Check for password
		if( $('password') && $('password_confirm') && $('password').value != $('password_confirm').value ){
		
			// Here is the password mismatch
			alert( bento.form.password_mismatch );
			
			// Exit
			bento.form.exit = true;
			
		// if
		}
		
		// Check it
		this.getElements('input[type=hidden].' + bento.form.javascript_onsubmit ).each(function(e){
									try{
										debug("firing onload " + e.value );	
										eval(e.value);
									} catch(err){
										debug("can't run form onload " + err );	
									}
								});
		
		// This is if form is required
		this.getElements('input[type=text],input[type=hidden],input[type=radio],input[type=checkbox],select,textarea').each( function(el) {
			
			// If things need to be cancelled
			if( bento.form.exit ){ bento.form.block( false ); return; }

			// Check if the value is present
			if( el.hasClass( bento.form.required ) && el.get( bento.form.required ) && el.get( bento.form.required ) != '' && el.value == "" ){
				
				// Cancel the submission
				bento.form.exit = true;
				
			// if
			}
							
			// Check if it's a valid value
			if( el.hasClass( bento.form.validate ) ){			

				// Check if we have a matching ruleset
				if( bento.form.rules[ el.get('v') ] ){
				
					rule = bento.form.rules[ el.get('v') ].replace('\\\\','\\');
				
					// Do some checking
					if( el.value.search( eval( rule ) ) == -1 ){
						
						// Cancel the submission
						bento.form.exit = true;
					
					// RegEx Checking
					}
					
				// if
				}
				
			// if
			}
				
			// Check if we need to exit
			if( bento.form.exit ){
				
				// If it has some text, say it
				if( el.get( bento.form.required ) != false ){

					//bento.message.open({'text':el.get('required')});
					alert(el.get( bento.form.required ));
					
				} else {
					
					alert('A required field is not valid (id:' + el.get('id') + ')');
				
				// if
				}
				
				// Set the focus for this account
				try{
					el.focus();
				} catch ( err ){}
				
				// Blocks the submit buttons so we don't double submit
				bento.form.block( false );
				
			// if
			}
			
		// each
		});
			
		// Skip this function
		if( !bento.form.exit ){

			// This empties the log div and shows the spinning indicator
			var form = this;

			// Check this out
			if( this.hasClass( bento.form.ajax ) ){
				
				// Set the timeout
				timeout = this.hasClass( bento.form.ajax ) ? bento.form.timeout.ajax : bento.form.timeout.comet;
				
				// Set the options
				options = {
							url: this.getProperty('action'),
							timeout: bento.form.is_timeout( form ),
							data: form,
							onProgress: function(event, xhr){
								
								// Create the loaded total
								var loaded = event.loaded, total = event.total;
						 
						 		// Check it out
								debug('Progress for ' + form.id + ' - ' + parseInt(loaded / total * 100, 10) );
								
							},
							onRequest: function(){
								
								// Output a message
								debug('Submitting ' + form.id );
								
								// Check if we're blocking everything or not (comet)
								if( bento.form.is_pull(form) ){
		
									// Blocks the submit buttons so we don't double submit
									bento.form.block( true );
									
								// form
								}
								
							},
							onCancel: function(){
								
								// Output a message
								debug('Canceled submission  ' + form.id );
								
							},
							onFailure: function( xhr ){
								
								// Make sure we can output stuff
								debug('Failed submission (failure) ' + form.id );

								// Blocks the submit buttons so we don't double submit
								bento.form.block( false );
								
							},
							onException: function( headerName, value ){
								
								// Make sure we can output stuff
								debug('Failed submission (exeption) ' + form.id );

								// Blocks the submit buttons so we don't double submit
								bento.form.block( false );
								
							},
							onTimeout: function(){
								
								// Make sure we can output stuff
								debug('Failed submission (timeout) ' + form.id );

								// Blocks the submit buttons so we don't double submit
								bento.form.block( false );
								
								// If this form
								if( bento.form.is_push(form) ){
									
									// Send the file
									bento.form.submit( form.id );
								
								// if	
								}
								
							},
							onSuccess: function( json, text ){
									
								// Set this up
								bento.form.response = json;
								
								// Make sure we can output stuff
								debug('Successful submission ' + form.id );
							
								// add additional javascript functions if need be
								if( json.response && $( form.id + '_' + bento.form.javascript_onsuccess ) ){
			
									try{
			
										// Eval the javascript in the form
										eval( $( form.id + '_' + bento.form.javascript_onsuccess ).value );
							
									} catch ( e ){
										
										debug('Error executing oncomplete ' + e);
										
									}
			
								// if
								}
																
								// add additional forward function if need be
								if( json.response && $( form.id + '_' + bento.form.redirect_onsuccess ) && $( form.id + '_' + bento.form.redirect_onsuccess ).value ){
									
									document.location.href = $( form.id + '_' + bento.form.redirect_onsuccess ).value;
										
								}
								
								// Last we do the actions
								if( bento.form.response && bento.form.response.actions ){
									
									// Loop through it
									for(var i=0;i<bento.form.response.actions.length;i++){	
									
										try{
									
											eval(bento.form.response.actions[i]);
									
										} catch ( e ){
											
											debug('Error executing action ' + e);
											
										}
																			
									// for
									}
								
								// if
								}	
								
								// add additional javascript functions if need be
								if( !json.response && $( form.id + '_' + bento.form.javascript_onfail ) ){
									
									try{
									
										//Evel the javascript instruction to do on failure
										eval( $( form.id + '_' + bento.form.javascript_onfail ).value );
									
									} catch ( e ){
										
										debug('Error executing oncomplete ' + e);
										
									}
									
								// if	
								}
								
								// add additional forward function if need be
								if( !json.response && $( form.id + '_' + bento.form.redirect_onfail ) && $( form.id + '_' + bento.form.redirect_onfail ).value  ){
									
									// Unblock the submit buttons
									bento.form.block( true );
										
									document.location.href = $( form.id + '_' + bento.form.redirect_onfail ).value;
								
								// if	
								}

								// Blocks the submit buttons so we don't double submit
								bento.form.block( false );
								
								// If this form
								if( bento.form.is_push(form) ){
									
									// Send the file
									bento.form.submit( form.id );
								
								// if	
								}
								
							},
							onError: function( text, error ){
								
								// Make sure we can output stuff
								debug('Failed submission (error) ' + form.id );

								// add additional javascript functions if need be
								if( $( form.id + '_' + bento.form.javascript_onfail ) ){
									
									try{
									
										//Evel the javascript instruction to do on failure
										eval( $( form.id + '_' + bento.form.javascript_onfail ).value );
									
									} catch ( e ){
										
										debug('Error executing oncomplete ' + e);
										
									}
									
								// if	
								}
								
								// add additional forward function if need be
								if( $( form.id + '_' + bento.form.redirect_onfail ) && $( form.id + '_' + bento.form.redirect_onfail ).value  ){
									
									// Unblock the submit buttons
									bento.form.block( true );
										
									document.location.href = $( form.id + '_' + bento.form.redirect_onfail ).value;
								
								// if	
								}

								// Blocks the submit buttons so we don't double submit
								bento.form.block( false );
								
								// If this form
								if( bento.form.is_push(form) ){
									
									// Send the file
									bento.form.submit( form.id );
								
								// if	
								}
								
							},
							onComplete: function( json ){
									
								// Set this up
								bento.form.response = json;
								
								// Make sure we can output stuff
								debug('Complete submission ' + form.id );
	
								// Check if there's an oncomplete function
								if( $( form.id + '_' + bento.form.javascript_oncomplete ) ){
									
									try{
										
										//Evel the javascript instruction to do on failure
										eval ( $( form.id + '_' + bento.form.javascript_oncomplete ).value );
									
									} catch ( e ){
										
										debug('Error executing oncomplete ' + e);
										
									}
									
								// if
								}
								
								// add additional forward function if need be
								if( $( form.id + '_' + bento.form.redirect_oncomplete ) && $( form.id + '_' + bento.form.redirect_oncomplete ).value ){
									
									// lock the submit buttons
									bento.form.block( true );
									
									document.location.href = $( form.id + '_' + bento.form.redirect_oncomplete ).value;
									
								// if	
								} else {
		
									// Blocks the submit buttons so we don't double submit
									bento.form.block( true );
									
								// form
								}
								
							// oncomplete
							}
			
						 };
				
				// Check it
				if( !bento.form.forms[ this.id ] || ( bento.form.forms[ this.id ] && !bento.form.forms[ this.id ].isRunning() ) ){
				
					// Add this for queue
					bento.form.forms[ this.id ] = new Request.JSON( options ).send();
				
				// if
				} else {
				
					// Tell the world there's a problem
					debug('wont submit form (' + this.id + '), already running');
				
				// Check it	
				}
				
			// It's not 
			} else {
				
				// Just submit it
				this.submit();
				
			}

		//if
		}

// method
}

// Check this up
bento.form.focus = function(){
	
	// Check if there is an autoselect
	tmp = $$('input');
	
	// For
	for(var i=0;i<tmp.length;i++){
		
		try{
			if(tmp[i].getProperty('type') == 'text' ){
			  // tmp[i].focus();
			  break;
			// if
			}
		} catch(err){}
		
	// for
	}	
	
// method
}

// This will submit a bento form
bento.form.submit = function( form, manual ){
	
	// Set if we're manually submitting the form
	var manual = ( manual != null && manual != 'undefined' && manual != false );
	
	// Submit this file
	if( $(form) ){
		
		// Check if this is a manual (override) submission
		if( manual && bento.form.forms[ form ] && bento.form.forms[ form ].isRunning() ){
			
			// Cancel the form before resubmitting
			bento.form.forms[ form ].cancel();
			
		// if
		}
		
		// Send it if we can
		$(form).fireEvent('submit',{
			type: 'submit',
			target: $(form)
		});	
		
	// if
	}
	
// method
}

// Checks if this is a pull form
bento.form.is_pull = function( form ){
	
	// Make sure the element exists 
	if( $( bento.form.retrieve + '_' + form.getProperty('id') ) ){
		
		// Set it to pull, rather than push
		return $( bento.form.retrieve + '_' + form.getProperty('id') ).value == 'pull';
		
	// if
	}

// method	
}

// Checks if this is a push form
bento.form.is_push = function( form ){
	
	// Make sure the element exists 
	if( $( bento.form.retrieve + '_' + form.getProperty('id') ) ){
		
		// Set it to pull, rather than push
		return $( bento.form.retrieve + '_' + form.getProperty('id') ).value == 'push';
		
	// if
	}

// method	
}

// Turns a push form to a pull form
bento.form.is_timeout = function( form ){
	
	// Set it to pull, rather than push
	return $( bento.form.timeout + '_' + form.getProperty('id') ).value;

// method	
}

// Turns a push form to a pull form
bento.form.pull = function( id ){
	
	// Make sure the element exists 
	if( $( bento.form.retrieve + '_' + id ) ){
		
		// Set it to pull, rather than push
		$( bento.form.retrieve + '_' + id ).value = 'pull';
		$( bento.form.timeout + '_' + id ).value = (bento.form.timeouts.pull*1000);
		
	// if
	}

// method	
}

// Turns a pull form to a push form
bento.form.push = function( id ){
	
	// Make sure the element exists 
	if( $( bento.form.retrieve + '_' + id ) ){
		
		// Set it to pull, rather than push
		$( bento.form.retrieve + '_' + id ).value = 'push';
		$( bento.form.timeout + '_' + id ).value = (bento.form.timeouts.push*1000);
		
	// if
	}

// method	
}

// Blocks the submit buttons so we don't double click
bento.form.block = function( state ){

	// This is for the loading
	if( state ){ 
	
		$$( "." + bento.form.loading ).each(function(e){e.setStyle('display','inline-block');});
		
	// if
	} else if( !state ) {
		
		$$( "." + bento.form.loading ).each(function(e){e.setStyle('display','none');});
		
	// if
	}

	// first we're going to disable the submit button
	$$('input').each( function(e){
	
		// Check if this input type is a submit button
		if( e.getProperty('type') == 'submit' || e.getProperty('type') == 'button' ){
			
			// Disable the submit button
			e.setProperty('disabled',state);
			
		// if
		}
							   
	});

// method
}

// When the page loads up
bento.form.domready = function(){
	
	// Loop through the input
	$$("input[type=text].focus,input[type=password].focus,input[type=select].focus,input[type=textarea].focus").each(function(el){

		// set the focus		
		el.focus();
	
	// check 
	});
	
// method
}

// When the page loads up
bento.form.domupdate = function(){

	// Check this out
	$$("form." + bento.form.ajax ).each(function(el){
	
		// Check to make sure we din't already load this form
		if( bento.storage.reserve( {'set':'form','id':el.getProperty('id')}) ){
	
			// Now handing it		
			debug('now handling ' + el.getProperty('id') + ' form with ajax');
				
			// check if this is an image uploader or not
			if( el.get('enctype') == null || el.get('enctype') == "application/x-www-form-urlencoded" ){
		
				// Bind it with the form handler
				el.addEvent('submit', bento.form.text.bindWithEvent( el )	);
		
			// Check is this is submission or now
			} else {
		
				// Submit the file
				bento.form.file( el );
		
			// if
			}
			
			// Check it
			el.getChildren('input[type=hidden].' + bento.form.javascript_onload ).each(function(e){
										try{
											debug("firing onload " + e.value );	
											eval(e.value);
										} catch(err){
											debug("can't run form onload " + err );	
										}
									});
			
		// if
		}

	// each
	});
	
	// Halt enters
	$$('form').each(function(e){
		
		var inputs = e.getElements('input[type=text",input[type=password],select');
		$each(inputs,function(el,i) {
			
			// Check to make sure we din't already load this form
			if( bento.storage.reserve( {'set':'form','id':el.getProperty('id')}) ){
				
				el.addEvent('keypress',function(e) {
					if(e.key == 'enter') { 
						e.stop(); 
						try{
							if(inputs[i+1]) { inputs[i+1].focus(); }
						} catch ( err ){}
						//last one?
						if(i == inputs.length-2) { bento.form.submit(e); }
					}
				});
			
			// if
			}
		
		// if
		});
		
	});
	
	// There you go
	if( bento.form.load_class && !$('bento_form_container') ){
	
		// Create the loading div
		var container = new Element('div',{'id':'bento_form_container','class':'bento_form_container'}).inject(document.body,'top');
		
		// Load a new element in
		new Element('div',{'class':bento.form.load_class + ' ' + bento.form.load_image}).inject( container );
		
	// if
	}
		
// method
}

// When the page loads up
bento.form.unload = function(){
	
	// Load up the loading
	bento.form.block( true );
 
 // method
}