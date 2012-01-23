/* class */
var TabSet = new Class({
	options: {
		activeClass: 'active', //css class
		openClass: 'open', //css class
		dropdownClass: 'dropdown', //css class
		cookieName: '',
		cookieOptions: {
			duration: 30, //30 days
			path: '/'
		},
		startIndex: 0 //start with this item if no cookie or active
	},
	Implements: [Options,Events],
	initialize: function(tabs,contents,options) {
		//handle arguments
		this.setOptions(options);
		this.tabs = tabs;
		this.contents = contents;
		// Set up the active before anything
		active = this.options.startIndex;
		// Check if there's an active
		for(var i=0;i<this.tabs.length;i++){
			if( this.tabs[i].hasClass( this.options.activeClass ) ){
				active = i;
				break;	
			}
		}
		//determine the "active" tab in cookie
		if( Cookie.read(this.options.cookieName) ){
			// Set the active tab
			active = Cookie.read(this.options.cookieName);
			// Remove anything inline
			this.tabs.removeClass( this.options.activeClass );
		// if
		}
		// alert( this.tabs.length );
		this.activeTab = this.tabs[active].addClass(this.options.activeClass);
		this.activeContent = this.contents[active];
		// add a click for the rest of the body
		document.id(document.body).addEvent('click', this.closeAll.bind(this));
		document.id(document.body).addEvent('touchmove', this.closeAll.bind(this));
		//process each tab and content
		this.tabs.each(function(tab,i) {
			this.processItem(tab,this.contents[i],i);
		},this);
		//tabs are ready -- load it!
		this.fireEvent('load');
	},
	closeAll:function( el ){
		//remove the active class from the active tab
		this.tabs.removeClass( this.options.openClass );
	},
	processItem:function(tab,content,i) {
			//add a click event to the tab
			tab.addEvent('click',function(e) {
				// On the active tab
				if( tab != this.activeTab ) {
					//stop!
					if(e) e.stop();
					//remove the active class from the active tab
					this.activeTab.removeClass(this.options.activeClass);
					//remove the active class from the active tab
					this.activeTab.removeClass(this.options.openClass);
					//make the clicked tab the active tab
					(this.activeTab = tab).addClass(this.options.activeClass);				
					// Add the dropdown class i/a
					if( tab.hasClass( this.options.dropdownClass ) ){
						(this.activeTab = tab).addClass(this.options.openClass);
					// if	
					} else {
						// Hide the active content
						this.activeContent.removeClass(this.options.activeClass);
						// make the clicked tab the active tab	
						(this.activeContent = content).addClass(this.options.activeClass);
					// if
					} 
					//save the index to cookie
					// if(this.options.cookieName && !tab.hasClass( this.options.dropdownClass ) ){ Cookie.write(this.options.cookieName,i,this.options.cookieOptions); }
				// if
				} else if( tab == this.activeTab && tab.hasClass( this.options.dropdownClass ) ) {
					if( tab.hasClass( this.options.openClass ) ){
						tab.removeClass( this.options.openClass );
					} else {
						tab.addClass( this.options.openClass );
					}
				}
			}.bind(this));
	}
});

// Load this all up, with storage
bento.tabs = {}
bento.tabs.domupdate = function(){
	// Check if we've got some content
	$$('.tabs, .pills').each(function(e){
								// Check ti
								if( bento.storage.reserve({'set':'tabs','id':e}) ){
									// Give it some numbers
									var content = e.getNext('div');
										content.setProperty('id','tab_content_' + e.getProperty('id')).addClass('tab-content');
									// Set up the tabs
									var tabset = new TabSet($$('#' + e.getProperty('id') + ' > li'),$$('#' + content.getProperty('id') + ' > div'),{
										cookieName: e.getProperty('id')
									});
								// if
								}
							});
								
// method
}