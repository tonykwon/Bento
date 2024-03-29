// Simple Set Clipboard System
// Author: Joseph Huckaby
// Mootools version: Pedro Visintin

var ZeroClipboard = {
	Implements:[Options, Events],
	options: {
	  version: "1.0.4",
	  clients: {}, // registered upload clients on page, indexed by id
	  moviePath: '/bento/form/js/libraries/ZeroClipboard.swf', // URL to movie
	  nextId: 1 // ID of next movie
	},
	initialize: function(elem,options) {
    this.setOptions(options);  

		this.handlers = {};
		
		// unique ID
		this.id = this.options.nextId++;
		this.movieId = 'ZeroClipboardMovie_' + this.id;
		
		// register client with singleton to receive flash events
		this.register(this.id, this);
		
		// create movie
		if (elem) {
		  this.glue(elem);
	  };
	},
	dispatch: function(id, eventName, args) {
		// receive event from flash movie, send to client		
		var client = this.options.clients[id];
		if (client) {
			client.receiveEvent(eventName, args);
		}
	},
	
	register: function(id, client) {
		// register new client to receive events
		this.options.clients[id] = client;
	}
	
};

ZeroClipboard.Client = new Class({

	id: 0, // unique ID for us
	ready: false, // whether movie is ready to receive events or not
	movie: null, // reference to movie object
	clipText: '', // text to copy to clipboard
	handCursorEnabled: true, // whether to show hand cursor, or default pointer cursor
	cssEffects: true, // enable CSS mouse effects on dom container
	handlers: null, // user event handlers

	initialize: function(elem) {

		// constructor for new simple upload client
		this.handlers = {};
		
		// unique ID
		this.id = ZeroClipboard.options.nextId++;
		this.movieId = 'ZeroClipboardMovie_' + this.id;
		
		// register client with singleton to receive flash events
		ZeroClipboard.register(this.id, this);
		
		// create movie
		if (elem) {
		  this.glue(elem);
	  };
	},
	
	glue: function(elem) {
		// glue to DOM element
		// elem can be ID or actual DOM element object
		this.domElement = elem;
		
		// float just above object, or zIndex 99 if dom element isn't set
		var zIndex = 99;

		if (this.domElement.style.zIndex) {
			zIndex = parseInt(this.domElement.style.zIndex) + 1;
		}
		
		// find X/Y position of domElement
		var box = this.domElement.getSize();
		var pos = this.domElement.getPosition();

		// create floating DIV above element
		this.div = new Element('div', {
		  'title': 'Copy the password to clipboard.'
		});
		var style = this.div.style;
		style.position = 'absolute';
		style.left = '' + pos.x + 'px';
		style.top = '' + pos.y + 'px';
		style.width = '' + box.x + 'px';
		style.height = '' + box.y + 'px';
		style.zIndex = zIndex;
		
		// style.backgroundColor = '#f00'; // debug
		
		var body = document.getElementsByTagName('body')[0];
		body.appendChild(this.div);
		
		this.div.innerHTML = this.getHTML( box.x, box.y );
	},
	
	getHTML: function(width, height) {
		// return HTML for movie
		var html = '';
		var flashvars = 'id=' + this.id + 
			'&width=' + width + 
			'&height=' + height;
			
		if (navigator.userAgent.match(/MSIE/)) {
			// IE gets an OBJECT tag
			var protocol = location.href.match(/^https/i) ? 'https://' : 'http://';
			html += '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="/bento/form/swf/'+protocol+'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'+width+'" height="'+height+'" id="'+this.movieId+'" align="middle"><param name="allowScriptAccess" value="always"><param name="allowFullScreen" value="false"><param name="movie" value="/bento/form/swf/'+ZeroClipboard.options.moviePath+'"><param name="loop" value="false"><param name="menu" value="false"><param name="quality" value="best"><param name="bgcolor" value="#ffffff"><param name="flashvars" value="'+flashvars+'"/><param name="wmode" value="transparent"/></object>';
		}
		else {
			// all other browsers get an EMBED tag
			html += '<embed id="'+this.movieId+'" src="/bento/form/swf/js/'+ZeroClipboard.options.moviePath+'" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="'+width+'" height="'+height+'" name="'+this.movieId+'" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+flashvars+'" wmode="transparent">';
		}
		return html;
	},
	
	hide: function() {
		// temporarily hide floater offscreen
		if (this.div) {
			this.div.style.left = '-2000px';
		}
	},
	
	show: function() {
		// show ourselves after a call to hide()
		this.reposition();
	},
	
	destroy: function() {
		// destroy control and floater
		if (this.domElement && this.div) {
			this.hide();
			this.div.innerHTML = '';
			
			var body = document.getElementsByTagName('body')[0];
			try { body.removeChild( this.div ); } catch(e) {;}
			
			this.domElement = null;
			this.div = null;
		}
	},
	
	reposition: function(elem) {
		// reposition our floating div, optionally to new container
		// warning: container CANNOT change size, only position
		if (elem) {
			this.domElement = elem;
			if (!this.domElement) this.hide();
		}
		
		if (this.domElement && this.div) {
  		var pos = this.domElement.getPosition();
			var style = this.div.style;
			style.left = '' + pos.x + 'px';
			style.top = '' + pos.y + 'px';
		}
	},
	
	setText: function(newText) {
		// set text to be copied to clipboard
		this.clipText = newText;
		if (this.ready) this.movie.setText(newText);
	},
	
	addEventListener: function(eventName, func) {
		// add user event listener for event
		// event types: load, queueStart, fileStart, fileComplete, queueComplete, progress, error, cancel
		eventName = eventName.toString().toLowerCase().replace(/^on/, '');
		if (!this.handlers[eventName]) this.handlers[eventName] = [];
		this.handlers[eventName].push(func);
	},
	
	setHandCursor: function(enabled) {
		// enable hand cursor (true), or default arrow cursor (false)
		this.handCursorEnabled = enabled;
		if (this.ready) this.movie.setHandCursor(enabled);
	},
	
	setCSSEffects: function(enabled) {
		// enable or disable CSS effects on DOM container
		this.cssEffects = !!enabled;
	},
	
	receiveEvent: function(eventName, args) {
		// receive event from flash
		eventName = eventName.toString().toLowerCase().replace(/^on/, '');
				
		// special behavior for certain events
		switch (eventName) {
			case 'load':
				// movie claims it is ready, but in IE this isn't always the case...
				// bug fix: Cannot extend EMBED DOM elements in Firefox, must use traditional function
				this.movie = $(this.movieId);
				if (!this.movie) {
					var self = this;
					setTimeout( function() { self.receiveEvent('load', null); }, 1 );
					return;
				}
				
				// firefox on pc needs a "kick" in order to set these in certain cases
				if (!this.ready && navigator.userAgent.match(/Firefox/) && navigator.userAgent.match(/Windows/)) {
					var self = this;
					setTimeout( function() { self.receiveEvent('load', null); }, 100 );
					this.ready = true;
					return;
				}
				
				this.ready = true;
				this.movie.setText( this.clipText );
				this.movie.setHandCursor( this.handCursorEnabled );
				break;
			
			case 'mouseover':
				if (this.domElement && this.cssEffects) {
					this.domElement.addClass('hover');
					if (this.recoverActive) this.domElement.addClass('active');
				}
				break;
			
			case 'mouseout':
				if (this.domElement && this.cssEffects) {
					this.recoverActive = false;
					if (this.domElement.hasClass('active')) {
						this.domElement.removeClass('active');
						this.recoverActive = true;
					}
					this.domElement.removeClass('hover');
				}
				break;
			
			case 'mousedown':
				if (this.domElement && this.cssEffects) {
					this.domElement.addClass('active');
				}
				break;
			
			case 'mouseup':
				if (this.domElement && this.cssEffects) {
					this.domElement.removeClass('active');
					this.recoverActive = false;
				}
				break;
		} // switch eventName
		
		if (this.handlers[eventName]) {
			for (var idx = 0, len = this.handlers[eventName].length; idx < len; idx++) {
				var func = this.handlers[eventName][idx];
			
				if (typeof(func) == 'function') {
					// actual function reference
					func(this, args);
				}
				else if ((typeof(func) == 'object') && (func.length == 2)) {
					// PHP style object + method, i.e. [myObject, 'myMethod']
					func[0][ func[1] ](this, args);
				}
				else if (typeof(func) == 'string') {
					// name of function
					window[func](this, args);
				}
			} // foreach event handler defined
		} // user defined handler for event
	}
	
});

window.addEvent('domready', function() {
	$$(".clip").each(function(item) {
		//Create a new clipboard client
		var clip = new ZeroClipboard.Client();
		//Glue the clipboard client to the last td in each row
		clip.glue(item);
		//Grab the text from the parent row of the icon
		var txt = item.getProperty('title');
		// Set it to the clipboard						
		clip.setText(txt);
		//Add a complete event to let the user know the text was copied
		clip.addEventListener('complete', function(client, text) {
			bento.form.alert('success','','We\'ve copied this password to your clipboard.',0,20000);
		});

	});
});  