// Setup the password utility
bento.form.password = {};
// This is the strength thingy
bento.form.password.strength = function(password){

	var desc = new Array();
		desc[0] = "Very Weak";
		desc[1] = "Weak";
		desc[2] = "Better";
		desc[3] = "Medium";
		desc[4] = "Strong";
		desc[5] = "Strongest";

	var score = 0;
	
	//if password bigger than 6 give 1 point
	if (password.length > 6) score++;
	
	//if password has both lower and uppercase characters give 1 point	
	if ( ( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ) ) score++;
	
	//if password has at least one number give 1 point
	if (password.match(/\d+/)) score++;
	
	//if password has at least one special caracther give 1 point
	if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) )	score++;
	
	//if password bigger than 12 give another 1 point
	if (password.length > 12) score++;
	
	// Check if passwords don't match
	if( $("password_confirm") && $("password_confirm").value != "" && $("password_confirm").value != $("password").value ){
	
		$("password_description").innerHTML = "Passwords don't match";
		$("password_strength").className = "strength" + 0;
		
	// if
	} else {
		
		$("password_description").innerHTML = desc[score];
		$("password_strength").className = "strength" + score;

	// if
	}
	
// method
}
// This is for the password strength checker
window.addEvent('domready',function(){

	// Insert the password divs
	new Element('div',{'id':'password_description'}).injectAfter('password_confirm');
	new Element('div',{'id':'password_strength','class':'strength0'}).injectAfter('password_description');

	// Add the events
	$('password').addEvent('keyup',function(){
		bento.form.password.strength( this.value );
	});
	$('password').addEvent('change',function(){
		bento.form.password.strength( this.value );
	});
	
	// Add the events
	$('password_confirm').addEvent('keyup',function(){
		bento.form.password.strength( this.value );
	});
	$('password_confirm').addEvent('change',function(){
		bento.form.password.strength( this.value );
	});

});