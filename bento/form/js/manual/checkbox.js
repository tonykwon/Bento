window.addEvent('domready',function(){
	
	// Updte it
	$$('input[type=checkbox].bento_checkbox').each(function(e){
		
		e.addEvent('click',function(){
			if(this.checked){ value = 1; } else { value = 0; }
			$('bento_checkbox_' + this.id ).value = value;
		});
		
	// each
	});

// domready
});