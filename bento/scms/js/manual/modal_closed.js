bento.scms.modal.class = new Class({
	
	Implements: [Options,Events],
	
	options: {
		width: 'auto',
		height: 'auto',
		draggable: false,
		title: '',
		buttons: [],
		fadeDelay: 400,
		fadeDuration: 400,
		keys: { 
			esc: function() { this.close(); } 
		},
		zIndex: 900000000005,
		pad: 100,
		overlayAll: false,
		constrain: false,
		resetOnScroll: true,
		baseClass: 'scms_modal'
	},	
	
	initialize: function(options) {
		this.setOptions(options);
		this.state = false;
		this.resizeOnOpen = true;
		this.ie6 = typeof document.body.style.maxHeight == "undefined";
		this.draw();
		this.bindClose = this.close.bind(this);
	},
	
	draw: function() {
		
		//create main box
		this.box = new Element('div',{
			'class': this.options.baseClass,
			styles: {
				'z-index': this.options.zIndex,
				opacity: 0
			},
			tween: {
				duration: this.options.fadeDuration,
				onComplete: function() {
					if(this.box.getStyle('opacity') == 0) {
						this.box.setStyles({ top: -9000, left: -9000 });
					}
				}.bind(this)
			}
		}).inject(document.body,'bottom');

		// Add the content box
		this.contentBox = new Element('div',{
			'class': 'scms_modal_content',
			styles: {
				width: this.options.width
			}
		});
		this.box.appendChild(this.contentBox);
		
		//draw title
		if(this.options.title) {
			this.title = new Element('div',{
				'class': 'scms_modal_title'
			}).inject(this.contentBox);
			this.titleClose = new Element('a',{
				'class': 'scms_modal_title_close',
				text: 'x',
				href: 'javascript:;',
			}).inject(this.title).addEvent('click', bento.scms.modal.close );
			this.titleText = new Element('h4',{
				'class': 'scms_modal_title_text',
				html: this.options.title
			}).inject(this.title);
		}
		
		//draw message box
		this.messageBox = new Element('div',{
			'class': 'scms_modal_message',
			html: this.options.content || '',
			styles: {
				width: this.options.width,
				height: this.options.height
			}
		}).inject(this.contentBox);
		
		//draw overlay
		this.overlay = new Element('div',{
			html: '&nbsp;',
			styles: {
				opacity: 0
			},
			'class': 'scms_modal_overlay',
			tween: {
				link: 'chain',
				duration: this.options.fadeDuration,
				onComplete: function() {
					//if(this.overlay.getStyle('opacity') == 0) this.box.focus();
				}.bind(this)
			}
		}).inject(this.contentBox);
		if(!this.options.overlayAll) {
			this.overlay.setStyle('top',(this.title ? this.title.getSize().y - 1: 0));
		}
		
		//focus node
		this.focusNode = this.box;
		
		return this;
	},
	
	// Open and close box
	close: function(fast) {
		if(this.isOpen) {
			this.box[fast ? 'setStyles' : 'tween']('opacity',0);
			this.fireEvent('close');
			this._detachEvents();
			this.isOpen = false;
		}
		// Get rid of it
		return this;
	},
	
	open: function(fast) {
		if(!this.isOpen) {
			this.box[fast ? 'setStyles' : 'tween']('opacity',1);
			if(this.resizeOnOpen) this._resize();
			this.fireEvent('open');
			this._attachEvents();
			(function() {
				this._setFocus();
			}).bind(this).delay(this.options.fadeDuration + 10);
			this.isOpen = true;
		}
		if(this.options.url) this.load();
		return this;
	},
	
	_setFocus: function() {
		this.focusNode.setAttribute('tabIndex',0);
		this.focusNode.focus();
	},
	
	// Show and hide overlay
	fade: function(fade,delay) {
		this._ie6Size();
		(function() {
			this.overlay.setStyle('opacity',fade || 1);
		}.bind(this)).delay(delay || 0);
		this.fireEvent('fade');
		return this;
	},
	unfade: function(delay) {
		(function() {
			this.overlay.fade(0);
		}.bind(this)).delay(delay || this.options.fadeDelay);
		this.fireEvent('unfade');
		return this;
	},
	_ie6Size: function() {
		if(this.ie6) {
			var size = this.contentBox.getSize();
			var titleHeight = (this.options.overlayAll || !this.title) ? 0 : this.title.getSize().y;
			this.overlay.setStyles({
				height: size.y - titleHeight,
				width: size.x
			});
		}
	},
	
	// Loads content
	load: function(url,title) {
		this.fade();
		if(!this.iframe) {
			this.messageBox.set('html','');
			this.iframe = new IFrame({
				scrolling: 'no',					 
				styles: {
					width: '100%',
					height: '100%'
				},
				events: {
					load: function() {
						this.unfade();
						this.fireEvent('complete');
					}.bind(this)
				},
				frameborder: 0
			}).inject(this.messageBox);
			this.messageBox.setStyles({ padding:0, overflow:'hidden' });
		}
		if(title) this.title.set('html',title);
		this.iframe.src = url || this.options.url;
		this.fireEvent('request');
		return this;
	},
	
	// Attaches events when opened
	_attachEvents: function() {
		this.keyEvent = function(e){
			if(this.options.keys[e.key]) this.options.keys[e.key].call(this);
		}.bind(this);
		this.focusNode.addEvent('keyup',this.keyEvent);
		
		this.resizeEvent = this.options.constrain ? function(e) { 
			this._resize(); 
		}.bind(this) : function() { 
			this._position(); 
		}.bind(this);
		window.addEvent('resize',this.resizeEvent);
		
		if(this.options.resetOnScroll) {
			this.scrollEvent = function() {
				this._position();
			}.bind(this);
			window.addEvent('scroll',this.scrollEvent);
		}
		
		return this;
	},
	
	// Detaches events upon close
	_detachEvents: function() {
		this.focusNode.removeEvent('keyup',this.keyEvent);
		window.removeEvent('resize',this.resizeEvent);
		if(this.scrollEvent) window.removeEvent('scroll',this.scrollEvent);
		return this;
	},
	
	// Repositions the box
	_position: function() {
		var windowSize = window.getSize(), 
			scrollSize = window.getScroll(), 
			boxSize = this.box.getSize();
		this.box.setStyles({
			left: scrollSize.x + ((windowSize.x - boxSize.x) / 2),
			top: scrollSize.y + ((windowSize.y - boxSize.y)  * .25)
		});
		this._ie6Size();
		return this;
	},
	
	// Resizes the box, then positions it
	_resize: function() {
		var height = this.options.height;
		if(height == 'auto') {
			//get the height of the content box
			var max = window.getSize().y - this.options.pad;
			if(this.contentBox.getSize().y > max) height = max;
		}
		this.messageBox.setStyle('height',height);
		this._position();
	},
	
	// Expose message box
	toElement: function () {
		return this.messageBox;
	},
	
	// Expose entire modal box
	getBox: function() {
		return this.box;
	},
	
	// Cleanup
	destroy: function() {
		this._detachEvents();
		this.box.dispose();
	}
});
// This will open the agreement boxes
bento.scms.modal.open = function( options ){
	// Here are the options
	options = JSON.decode(options);
	// Close any messages
	bento.message.close();
	// modal, title, width, height, form, validate, vars, button_submit, button_close, button_custom
    var size = window.document.getSize();
    if ( (options.height+100) >= size.y ){
        options.width = options.width + 100;
        options.height = size.y - 100;
    }
	if ( (options.width-50) >= size.x ) {
		options.width = size.x - 100;	
	}
	// Check this out
	if( bento.scms.modal.page[options.url] && bento.scms.modal.page[options.url].isOpen ){
		return;	
	// if
	}
	// Close anything that might be open
	bento.scms.modal.close();
	// There you go
	bento.scms.modal.isOpen = options.url;
	bento.scms.modal.page[ options.url ] = new bento.scms.modal.class({
		height: options.height,
		width: options.width,
		url: '/' + options.url + '/?' + options.variables,
		title: options.title
	}).open();	
	
// method
}
// Close any open modals
bento.scms.modal.close = function(){
	// Check it
	modal = bento.scms.modal.isOpen;
	// Check this out
	if( bento.scms.modal.page[modal] && bento.scms.modal.page[modal].isOpen ){
		bento.scms.modal.page[modal].close();
	// if
	}
// method
}