/* class */
var ButtonSet = new Class({
	options: {
		openClass: 'open', //css class
		cookieName: '',
		cookieOptions: {
			duration: 30, //30 days
			path: '/'
		},
		startIndex: 0 //start with this item if no cookie or active
	},
	Implements: [Options,Events],
	initialize: function(el) {
		
		// Set the element
		this.element = el;
		
		// Replace the click
		this.replaceClick();
	
		// add a click for the rest of the body
		document.id(document.body).addEvents({
											'click': this.closeAll.bind(this),
											'touchmove': this.closeAll.bind(this),
											'scroll': this.closeAll.bind(this)
											});
											
		//add a click event to the tab
		el.addEvent('click',function(e) {
			// On the active tab
			if(e) e.stop();
			// Check it it's open already
			if( this.element.hasClass( this.options.openClass ) ){
				
				// Switch the buttongs
				var selected = this.element.getChildren('li').getChildren('input')[0];
				var placeholder = this.element.getChildren('li')[0];
				
				// Get the first item
				selected.inject(e.target,'after');
				selected.removeClass('icon62');
				selected.setProperty('onclick',this.onclick);

				// Add the class
				e.target.addClass('icon62');
				
				// Inject the clicked element into the top
				e.target.inject(placeholder,'top');
				
				// Replace the click
				this.replaceClick();
		
				//remove the active class from the active tab
				this.element.removeClass(this.options.openClass);
		
			} else {
				this.element.addClass(this.options.openClass);
			}
		}.bind(this));
	},
	closeAll:function(){
		
		//remove the active class from the active tab
		$$('ul.dropdown.open').removeClass( this.options.openClass );
	
	},
	replaceClick: function(){
		
		// Get the selected, remove the onclick
		this.onclick = this.element.getChildren('li').getChildren('input')[0].getProperty('onclick');
		
		// Fix the onclick
		this.element.getChildren('li').getChildren('input')[0].setProperty('onclick','javascript:;');
		
	}
});

// Load this all up, with storage
bento.buttons = {}
bento.buttons.domupdate = function(){
	// Check if we've got some content
	$$('ul.buttons.dropdown').each(function(e){
								// Check if we've dealt with this yet
								if( bento.storage.reserve({
															'id':e,
															'set':'buttons'
															}) 
														){
									// Set up the dropdown buttons
									new ButtonSet(e);
								// if
								}
							});
	// Check if we've got some content
	$$('input.button').each(function(e){
							if( e.getProperty('href') ){
								// Check if we've dealt with this yet
								if( bento.storage.reserve({
															'id':e,
															'set':'buttons'
															}) 
														){
									// Set up the dropdown buttons
									e.setProperty('onclick',e.getProperty('href'));
								// if
								}
							}
						});								
// method
}