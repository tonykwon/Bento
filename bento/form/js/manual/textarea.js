// This is the textares
bento.form.textarea = {};

// Insert into the textarea
bento.form.textarea.maxlength = function( myField, myValue ){

	var txts = document.getElementsByTagName('textarea')
	for (var i = 0, l = txts.length; i < l; i++) {
		if (/^[0-9]+$/.test(txts[i].getAttribute("maxlength"))) {
			var func = function () {
				var len = parseInt(this.getAttribute("maxlength"), 10);

				if (this.value.length > len) {
					// alert('Maximum length exceeded: ' + len); 
					this.value = this.value.substr(0, len);
					return false;
				}
			}

			txts[i].onkeyup = func;
			txts[i].onblur = func;
		}
	}
		
// method
}

// Insert into the textarea
bento.form.textarea.insert = function( myField, myValue ){
	
	//IE support
	if (document.selection) {
		
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		
	} else if (myField.selectionStart || myField.selectionStart == '0' ) {
		
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
			myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
			
	} else {
		
		myField.value += myValue;
		
	}

// method
}

// When the page loads up
window.addEvent('domready', function(){
	
	// textarea max length
	bento.form.textarea.maxlength();
	
// 
});
	