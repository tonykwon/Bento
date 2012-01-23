// Let's setup this call
bento.form.select = {};

// This will remove items on the select
bento.form.select.remove = function( from ){
		
	  var i = 0;
	  
	  // check if the element exists
	  if( $( from ) ){
	  
		  // Loop through the options
		  $( from ).getChildren().each( function(e){
		  
			// Check if this is the option we want to remove
			if ( e.selected ) {
			
				// Remove it
				e.destroy();
			
			// if
			}
			
			i++;

		  // for
		  });

		// if
		} else {
			
			alert('Error' + from + " doesn't exist.");
		
		}

// method
}

// Comparing items for removal
bento.form.select.compare = function(a,b) {
	/*
	* return >0 if a>b
	*         0 if a=b
	*        <0 if a<b
	*/
	// textual comparison
	return a.text!=b.text ? a.text<b.text ? -1 : 1 : 0;
	// numerical comparison
	//  return a.text - b.text;
// method
}

// This sorts things
bento.form.select.sort = function (list){
  list = $(list);
  var items = list.options.length;
  // create array and make copies of options in list
  var tmpArray = new Array(items);
  for ( i=0; i<items; i++ )
    tmpArray[i] = new
Option(list.options[i].text,list.options[i].value);
  // sort options using given function
  tmpArray.sort(bento.form.select.compare);
  // make copies of sorted options back to list
  for ( i=0; i<items; i++ )
    list.options[i] = new Option(tmpArray[i].text,tmpArray[i].value);

}

// method to add selected option
bento.form.select.add = function( from, to, remove, sort ){
		
		if( $( to ) && $( from ) ){
			
		  // Loop through the options
		  $( from ).getChildren().each( function(e){
		  
				// Check if this is the option we want to remove
				if ( e.selected ) {
				
					var elOptNew = document.createElement('option');
						elOptNew.text = e.text;
						elOptNew.value = e.value;
					
					var pass = true;
					
					// Check if this is in the list yet
					for (i = $( to ).length - 1; i>=0; i--) {
					
					  if( $( to ).options[i].value == elOptNew.value ){
			
						pass = false;
												
					  }
					
					// for
					}
					
					// Check if we can add this 
					if( pass == true ){
					
					  try {
						$( to ).add(elOptNew, null); // standards compliant; doesn't work in IE
					  }
					  catch(ex) {
						$( to ).add(elOptNew); // IE only
					  }
					
					// if
					}
					
					// Check it up
					if( remove ){
					
						e.destroy();

					// if
					}

				// if
				}
				
		 });
		  
		// Check it up
		if( sort ){
		
			bento.form.select.sort( to );

		// if
		}

		} else {
		
			if( !$( to ) ){
				
				alert( "Error " + to + " doesn't exist.");
				
			} else {
				
				alert( "Error " + from + " doesn't exist.");
				
			// if
			}
		
		// if
		}

// method
}

// method to add selected option
bento.form.select.append = function( to, value, text, sort ){
		
	if( $( to ) ){
		
		var elOptNew = document.createElement('option');
			elOptNew.text = text;
			elOptNew.value = value;
		
		try {
			$( to ).add(elOptNew, null); // standards compliant; doesn't work in IE
		}
		catch(ex) {
			$( to ).add(elOptNew); // IE only
		}
	
	// if
	}

// method
}
	
// Selects all the options on submit
bento.form.select.all =  function( from ){
		
		if( $( from ) ){

			// Loop through all the 
			for (var i = 0; i < $( from ).options.length; i++) {
			
				$( from ).options[i].selected = true;
			
			// for	
			}

		// if
		} else {
			
			alert( "Error " + from + " doesn't exist.");
		
		}

// method
}

// Selects all the options on submit
bento.form.select.none =  function( from ){
		
		if( $( from ) ){

			// Loop through all the 
			for (var i = 0; i < $( from ).options.length; i++) {
			
				$( from ).options[i].selected = false;
			
			// for	
			}

		// if
		} else {
			
			alert( "Error " + from + " doesn't exist.");
		
		}

// method
}
	
// Moves options up a select list
bento.form.select.up = function( from ){
		
	   var listField = $( from );
		
	   if ( listField.length == -1) {  // If the list is empty
		  //alert("There are no values which can be moved!");
	   } else {
		  var selected = listField.selectedIndex;
		  if (selected == -1) {
			 //alert("You must select an entry to be moved!");
		  } else {  // Something is selected
			 if ( listField.length == 0 ) {  // If there's only one in the list
				//alert("There is only one entry!\nThe one entry will remain in place.");

			 } else {  // There's more than one in the list, rearrange the list order
				if ( selected == 0 ) {
				   //alert("The first entry in the list cannot be moved up.");
				} else {
				   // Get the text/value of the one directly above the hightlighted entry as
				   // well as the highlighted entry; then flip them
				   var moveText1 = listField[selected-1].text;
				   var moveText2 = listField[selected].text;
				   var moveValue1 = listField[selected-1].value;
				   var moveValue2 = listField[selected].value;
				   listField[selected].text = moveText1;
				   listField[selected].value = moveValue1;
				   listField[selected-1].text = moveText2;
				   listField[selected-1].value = moveValue2;
				   listField.selectedIndex = selected-1; // Select the one that was selected before
				}  // Ends the check for selecting one which can be moved
			 }  // Ends the check for there only being one in the list to begin with
		  }  // Ends the check for there being something selected
	   }  // Ends the check for there being none in the list

// method
}
	
// Moves options up a select list
bento.form.select.down = function( from ){
		
	   var listField = $( from );
		
		  if ( listField.length == -1) {  // If the list is empty
			  //alert("There are no values which can be moved!");
		   } else {
			  var selected = listField.selectedIndex;
			  if (selected == -1) {
				 //alert("You must select an entry to be moved!");
			  } else {  // Something is selected
				 if ( listField.length == 0 ) {  // If there's only one in the list
					//alert("There is only one entry!\nThe one entry will remain in place.");
				 } else {  // There's more than one in the list, rearrange the list order
					if ( selected == listField.length-1 ) {
					   //alert("The last entry in the list cannot be moved down.");
					} else {
					   // Get the text/value of the one directly below the hightlighted entry as
					   // well as the highlighted entry; then flip them
					   var moveText1 = listField[selected+1].text;
					   var moveText2 = listField[selected].text;
					   var moveValue1 = listField[selected+1].value;
					   var moveValue2 = listField[selected].value;
					   listField[selected].text = moveText1;
					   listField[selected].value = moveValue1;
					   listField[selected+1].text = moveText2;
					   listField[selected+1].value = moveValue2;
					   listField.selectedIndex = selected+1; // Select the one that was selected before
					}  // Ends the check for selecting one which can be moved
				 }  // Ends the check for there only being one in the list to begin with
			  }  // Ends the check for there being something selected
		   }  // Ends the check for there being none in the list

// method
}