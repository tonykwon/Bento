// This is the ajax form handler
bento.form.file_build = function(el){
	
	// Create the upload status
	upload_status = new Element('div',{'id':'upload-status'}).inject(el,'top');

	// These are the progress bars
	overall_title = new Element('span',{
									'class':'overall-title'
									}).set('text','Overall Progress').inject(upload_status);
	
	// These are the progress bars
	overall_progress = new Element('img',{
									'src':'/bento/form/images/bar.gif',
									'class':'progress overall-progress'
									}).injectAfter(overall_title);

	// These are the progress bars
	current_progress = new Element('img',{
									'src':'/bento/form/images/bar.gif',
									'class':'progress current-progress'
									}).injectAfter(overall_progress);

	// These are the progress bars
	current_title = new Element('span',{
									'class':'current-title'
									}).set('text','Current Progress').injectAfter(current_progress);

	// These are the progress bars
	current_text = new Element('span',{
									'class':'current-text'
									}).set('text','Current Progress').injectAfter(current_title);
	
	// This is the upload list
	upload_list = new Element('div',{'id':'upload-list'}).injectAfter(current_text);

	
// function
}

// This is the ajax form handler
bento.form.file = function(el){
	
		// Build it up
		bento.form.file_build( el );	
	
		// Create a variable to hold everthing
		vars = {};
		opts = {};
		bento.form.responses = new Array();

		// Get the form variables
		el.getElements('input,select,textarea').each( function(f) {
											   
											  // Check if we're working on 
											  if( f.getProperty('type') == "hidden" && f.getProperty('name') == el.id + '_directory' ){
												
												vars["directory"] = f.value;
												opts["directory"] = f.value.toString();
												  
											  // Check if we're working on 
											  } else if( f.getProperty('type') == "hidden" && f.getProperty('name') == 'bento_form_handler' ){
												
												vars["handler"] = f.value;
												opts['bento_form_handler'] = f.value.toString();
												  
											  // Check if we're working on 
											  } else if( f.getProperty('type') == "hidden" && f.getProperty('name') == el.id + '_extensions' ){
												
												vars["extensions"] = f.value;
												opts['extensions'] = f.value.toString();
												  
											  // Check if we're working on 
											  } else if( f.getProperty('type') == "hidden" && f.getProperty('name') == el.id + '_description' ){
												
												vars["description"] = f.value;
											  
											  // Check if we're working on 
											  } else if( f.getProperty('type') == "hidden" && f.getProperty('name') == el.id + '_number' ){
												
												vars["number"] = f.value;
												
												if( vars["number"].toInt() > 1 ){
													
													vars["multiple"] = true;
													
												} else {
													
													vars["multiple"] = false;
													
												// if
												}
												  
											  // Check if we're working on 
											  } else if( f.getProperty('type') == "hidden" && f.getProperty('name') == el.id + '_limit' ){
												
												vars["limit"] = f.value;
												  
											  // if
											  } else if( f.getProperty('type') != "button" && f.getProperty('type') != "submit" ) {
												  
												  if( f.getProperty('name').toString().search(/\[/) == -1 ){
												  
													  try{
													   
														  opts[ f.getProperty('name')  ] = f.value.toString();
																																			  
													  } catch( er ){
														  
														  }
													  
												  // if
												  }

											  //  if
											  }

										  });
		
		// File types
		var file_types = new Object()
			file_types[ vars["description"] + ' (' + vars["extensions"] + ')' ] = vars["extensions"].replace(/,/g,";").toString();
		
		// Create the uploader
		var up = new FancyUpload2($('upload-status'), $('upload-list'), {
															
			// we conbento.log infos, remove that in production!!
			verbose: true,
			
			// This is the path to url
			url: "/" + bento.form.url + "?" + bento.form.querystring + '&' + bento.form.upload,
			
			// This is the path to the uploader
			path: '/bento/form/swf/Swiff.Uploader.swf',
			
			// This is the browse button
			target: 'browse',

			// remove that line to select all files, or edit it, add more items
			typeFilter: {'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'},
			instantStart: true,
			multiple: vars["multiple"],
			
			// This will submit the options for size, directory, handler, etc.
			data: opts,
			
			// Show the upload list
			onSelectSuccess: function( files ){
				
				$('upload-list').setStyle('display','block');					
				
			},
			
			// graceful degradation, onLoad is only called if all went well with Flash
			onLoad: function() {
				$('upload-status').removeClass('hide'); // we show the actual UI
	 
				// We relay the interactions with the overlayed flash to the link
				this.target.addEvents({
					click: function() {
						return false;
					},
					mouseenter: function() {
						this.addClass('hover');
					},
					mouseleave: function() {
						this.removeClass('hover');
						this.blur();
					},
					mousedown: function() {
						this.focus();
					}
				});
	 
				// Interactions for the 2 other buttons
				$('clear').addEvent('click', function() {
					up.remove(); // remove all files
					return false;
				});
	 
				$('upload').addEvent('click', function() {
					up.start(); // start upload
					return false;
				});
			},

			// Edit the following lines, it is your custom event handling
	 
			/**
			 * Is called when files were not added, "files" is an array of invalid File classes.
			 * 
			 * This example creates a list of error elements directly in the file list, which
			 * hide on click.
			 */ 
			onSelectFail: function(files) {
				files.each(function(file) {
					new Element('li', {
						'class': 'validation-error',
						html: file.validationErrorMessage || file.validationError,
						title: MooTools.lang.get('FancyUpload', 'removeTitle'),
						events: {
							click: function() {
								this.destroy();
							}
						}
					}).inject(this.list, 'top');
				}, this);
			},
	 
			/**
			 * This one was directly in FancyUpload2 before, the event makes it
			 * easier for you, to add your own response handling (you probably want
			 * to send something else than JSON or different items).
			 */
			onFileComplete : function( file ) {
				
				alert( file.response.text );

				// Add the decoded response to the response array
				bento.form.responses[ bento.form.responses.length ] = JSON.decode(file.response.text);
				
			},
			
			onComplete: function(file ){
				
				// Unblock the submit buttons
				bento.form.block( false );
				
				// Innocent until proven guilty
				failed = false;
				
				// Check if debugging is on
				if( $( el.id + '_' + bento.form.debug ) ){
				
					// set the message
					alert('Debugging is set to on:\r\n\r\n' + JSON.encode(bento.form.responses) );

				// if
				}
				
				// Loop through them
				for(var i=0;i<bento.form.responses.length;i++){
										
					// Check if we failed
					if( bento.form.responses[i].response == false && $(el.id + '_' + bento.form.onfail) ){

						// Check it out
						bento.form.response = bento.form.responses[0];
																	
						// Eval the javascript in the form
						eval ( $(el.id + '_' + bento.form.javascript_onfail).value );
						
						failed = true;
			
					// if
					}
					
				// for
				}
				
				// Things went well!
				if( !failed ){

					// We succeeded
					if( $( el.id + '_' + bento.form.javascript_onsuccess ) ){
						
						// Check it all out
						bento.form.response = bento.form.responses[0];
			
						// Eval the javascript in the form
						eval ( $(el.id + '_' + bento.form.javascript_onsuccess).value );
						
					// if
					}
					
				// if
				}
				
				// Check if there's an oncomplete function
				if( $(el.id + '_' + bento.form.javascript_oncomplete ) ){
					
					bento.form.response = bento.form.responses[0];
					
					//Evel the javascript instruction to do on failure
					eval ( $(el.id + '_' + bento.form.javascript_oncomplete ).value );
									
				// if
				}
			
			// method
			},
	 
			/**
			 * onFail is called when the Flash movie got bashed by some browser plugin
			 * like Adblock or Flashblock.
			 */
			onFail: function(error) {
				
				switch (error) {
					case 'hidden': // works after enabling the movie and clicking refresh
						//bento.message('Error','To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).');
						break;
					case 'blocked': // This no *full* fail, it works after the user clicks the button
						//bento.message('Error','To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).');
						break;
					/*case 'empty': // Oh oh, wrong path
						alert('A required file was not found, please be patient and we fix this.');
						break;*/
					case 'flash': // no flash 9+ :(
						alert('To enable the embedded uploader, install the latest Adobe Flash plugin.')
				}
			}


		});

// method
}

/**
 * Swiff.Uploader - Flash FileReference Control
 *
 * @version		3.0
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <http://digitarald.de>
 * @author		Valerio Proietti, <http://mad4milk.net>
 * @copyright	Authors
 */

Swiff.Uploader = new Class({

	Extends: Swiff,

	Implements: Events,

	options: {
		path: 'Swiff.Uploader.swf',
		
		target: null,
		zIndex: 9999,
		
		height: 30,
		width: 100,
		callBacks: null,
		params: {
			wMode: 'opaque',
			menu: 'false',
			allowScriptAccess: 'always'
		},

		typeFilter: null,
		multiple: true,
		queued: true,
		verbose: false,

		url: null,
		method: null,
		data: null,
		mergeData: true,
		fieldName: null,

		fileSizeMin: 1,
		fileSizeMax: null, // Official limit is 100 MB for FileReference, but I tested up to 2Gb!
		allowDuplicates: false,
		timeLimit: (Browser.Platform.linux) ? 0 : 30,

		buttonImage: null,
		policyFile: null,
		
		fileListMax: 0,
		fileListSizeMax: 0,

		instantStart: false,
		appendCookieData: false,
		
		fileClass: null
		/*
		onLoad: $empty,
		onFail: $empty,
		onStart: $empty,
		onQueue: $empty,
		onComplete: $empty,
		onBrowse: $empty,
		onDisabledBrowse: $empty,
		onCancel: $empty,
		onSelect: $empty,
		onSelectSuccess: $empty,
		onSelectFail: $empty,
		
		onButtonEnter: $empty,
		onButtonLeave: $empty,
		onButtonDown: $empty,
		onButtonDisable: $empty,
		
		onFileStart: $empty,
		onFileStop: $empty,
		onFileRequeue: $empty,
		onFileOpen: $empty,
		onFileProgress: $empty,
		onFileComplete: $empty,
		onFileRemove: $empty,
		
		onBeforeStart: $empty,
		onBeforeStop: $empty,
		onBeforeRemove: $empty
		*/
	},

	initialize: function(options) {
		// protected events to control the class, added
		// before setting options (which adds own events)
		this.addEvent('load', this.initializeSwiff, true)
			.addEvent('select', this.processFiles, true)
			.addEvent('complete', this.update, true)
			.addEvent('fileRemove', function(file) {
				this.fileList.erase(file);
			}.bind(this), true);

		this.setOptions(options);

		// callbacks are no longer in the options, every callback
		// is fired as event, this is just compat
		if (this.options.callBacks) {
			Hash.each(this.options.callBacks, function(fn, name) {
				this.addEvent(name, fn);
			}, this);
		}

		this.options.callBacks = {
			fireCallback: this.fireCallback.bind(this)
		};

		var path = this.options.path;
		if (!path.contains('?')) path += '?noCache=' + $time(); // cache in IE

		// container options for Swiff class
		this.options.container = this.box = new Element('span', {'class': 'swiff-uploader-box'}).inject($(this.options.container) || document.body);

		// target 
		this.target = $(this.options.target);
		if (this.target) {
			var scroll = window.getScroll();
			this.box.setStyles({
				position: 'absolute',
				visibility: 'visible',
				zIndex: this.options.zIndex,
				overflow: 'hidden',
				height: 1, width: 1,
				top: scroll.y, left: scroll.x
			});
			
			// we force wMode to transparent for the overlay effect
			this.parent(path, {
				params: {
					wMode: 'transparent'
				},
				height: '100%',
				width: '100%'
			});
			
			this.target.addEvent('mouseenter', this.reposition.bind(this, []));
			
			// button interactions, relayed to to the target
			this.addEvents({
				buttonEnter: this.targetRelay.bind(this, ['mouseenter']),
				buttonLeave: this.targetRelay.bind(this, ['mouseleave']),
				buttonDown: this.targetRelay.bind(this, ['mousedown']),
				buttonDisable: this.targetRelay.bind(this, ['disable'])
			});
			
			this.reposition();
			window.addEvent('resize', this.reposition.bind(this, []));
		} else {
			this.parent(path);
		}

		this.inject(this.box);

		this.fileList = [];
		
		this.size = this.uploading = this.bytesLoaded = this.percentLoaded = 0;
		
		if (Browser.Plugins.Flash.version < 9) {
			this.fireEvent('fail', ['flash']);
		} else {
			this.verifyLoad.delay(1000, this);
		}
	},
	
	verifyLoad: function() {
		if (this.loaded) return;
		if (!this.object.parentNode) {
			this.fireEvent('fail', ['disabled']);
		} else if (this.object.style.display == 'none') {
			this.fireEvent('fail', ['hidden']);
		} else if (!this.object.offsetWidth) {
			this.fireEvent('fail', ['empty']);
		}
	},

	fireCallback: function(name, args) {
		// file* callbacks are relayed to the specific file
		if (name.substr(0, 4) == 'file') {
			// updated queue data is the second argument
			if (args.length > 1) this.update(args[1]);
			var data = args[0];
			
			var file = this.findFile(data.id);
			this.fireEvent(name, file || data, 5);
			if (file) {
				var fire = name.replace(/^file([A-Z])/, function($0, $1) {
					return $1.toLowerCase();
				});
				file.update(data).fireEvent(fire, [data], 10);
			}
		} else {
			this.fireEvent(name, args, 5);
		}
	},

	update: function(data) {
		// the data is saved right to the instance 
		$extend(this, data);
		this.fireEvent('queue', [this], 10);
		return this;
	},

	findFile: function(id) {
		for (var i = 0; i < this.fileList.length; i++) {
			if (this.fileList[i].id == id) return this.fileList[i];
		}
		return null;
	},

	initializeSwiff: function() {
		// extracted options for the swf 
		this.remote('initialize', {
			width: this.options.width,
			height: this.options.height,
			typeFilter: this.options.typeFilter,
			multiple: this.options.multiple,
			queued: this.options.queued,
			url: this.options.url,
			method: this.options.method,
			data: this.options.data,
			mergeData: this.options.mergeData,
			fieldName: this.options.fieldName,
			verbose: this.options.verbose,
			fileSizeMin: this.options.fileSizeMin,
			fileSizeMax: this.options.fileSizeMax,
			allowDuplicates: this.options.allowDuplicates,
			timeLimit: this.options.timeLimit,
			buttonImage: this.options.buttonImage,
			policyFile: this.options.policyFile
		});

		this.loaded = true;

		this.appendCookieData();
	},
	
	targetRelay: function(name) {
		if (this.target) this.target.fireEvent(name);
	},

	reposition: function(coords) {
		// update coordinates, manual or automatically
		coords = coords || (this.target && this.target.offsetHeight)
			? this.target.getCoordinates(this.box.getOffsetParent())
			: {top: window.getScrollTop(), left: 0, width: 40, height: 40}
		this.box.setStyles(coords);
		this.fireEvent('reposition', [coords, this.box, this.target]);
	},

	setOptions: function(options) {
		if (options) {
			if (options.url) options.url = Swiff.Uploader.qualifyPath(options.url);
			if (options.buttonImage) options.buttonImage = Swiff.Uploader.qualifyPath(options.buttonImage);
			this.parent(options);
			if (this.loaded) this.remote('setOptions', options);
		}
		return this;
	},

	setEnabled: function(status) {
		this.remote('setEnabled', status);
	},

	start: function() {
		this.fireEvent('beforeStart');
		this.remote('start');
	},

	stop: function() {
		this.fireEvent('beforeStop');
		this.remote('stop');
	},

	remove: function() {
		this.fireEvent('beforeRemove');
		this.remote('remove');
	},

	fileStart: function(file) {
		this.remote('fileStart', file.id);
	},

	fileStop: function(file) {
		this.remote('fileStop', file.id);
	},

	fileRemove: function(file) {
		this.remote('fileRemove', file.id);
	},

	fileRequeue: function(file) {
		this.remote('fileRequeue', file.id);
	},

	appendCookieData: function() {
		var append = this.options.appendCookieData;
		if (!append) return;
		
		var hash = {};
		document.cookie.split(/;\s*/).each(function(cookie) {
			cookie = cookie.split('=');
			if (cookie.length == 2) {
				hash[decodeURIComponent(cookie[0])] = decodeURIComponent(cookie[1]);
			}



		});

		var data = this.options.data || {};
		if ($type(append) == 'string') data[append] = hash;
		else $extend(data, hash);

		this.setOptions({data: data});
	},

	processFiles: function(successraw, failraw, queue) {
		var cls = this.options.fileClass || Swiff.Uploader.File;




		var fail = [], success = [];

		if (successraw) {
			successraw.each(function(data) {
				var ret = new cls(this, data);
				if (!ret.validate()) {
					ret.remove.delay(10, ret);
					fail.push(ret);
				} else {
					this.size += data.size;
					this.fileList.push(ret);
					success.push(ret);
					ret.render();
				}
			}, this);

			this.fireEvent('selectSuccess', [success], 10);
		}

		if (failraw || fail.length) {
			fail.extend((failraw) ? failraw.map(function(data) {
				return new cls(this, data);
			}, this) : []).each(function(file) {
				file.invalidate().render();
			});

			this.fireEvent('selectFail', [fail], 10);
		}

		this.update(queue);

		if (this.options.instantStart && success.length) this.start();
	}

});

$extend(Swiff.Uploader, {

	STATUS_QUEUED: 0,
	STATUS_RUNNING: 1,
	STATUS_ERROR: 2,
	STATUS_COMPLETE: 3,
	STATUS_STOPPED: 4,

	log: function() {
		if (window.console && conbento.info) conbento.info.apply(console, arguments);
	},

	unitLabels: {
		b: [{min: 1, unit: 'B'}, {min: 1024, unit: 'kB'}, {min: 1048576, unit: 'MB'}, {min: 1073741824, unit: 'GB'}],
		s: [{min: 1, unit: 's'}, {min: 60, unit: 'm'}, {min: 3600, unit: 'h'}, {min: 86400, unit: 'd'}]
	},

	formatUnit: function(base, type, join) {
		var labels = Swiff.Uploader.unitLabels[(type == 'bps') ? 'b' : type];
		var append = (type == 'bps') ? '/s' : '';
		var i, l = labels.length, value;


		if (base < 1) return '0 ' + labels[0].unit + append;

		if (type == 's') {
			var units = [];

			for (i = l - 1; i >= 0; i--) {
				value = Math.floor(base / labels[i].min);
				if (value) {
					units.push(value + ' ' + labels[i].unit);
					base -= value * labels[i].min;
					if (!base) break;
				}
			}

			return (join === false) ? units : units.join(join || ', ');
		}

		for (i = l - 1; i >= 0; i--) {
			value = labels[i].min;
			if (base >= value) break;
		}

		return (base / value).toFixed(1) + ' ' + labels[i].unit + append;
	}

});

Swiff.Uploader.qualifyPath = (function() {
	
	var anchor;
	
	return function(path) {
		(anchor || (anchor = new Element('a'))).href = path;
		return anchor.href;
	};

})();

Swiff.Uploader.File = new Class({

	Implements: Events,

	initialize: function(base, data) {
		this.base = base;
		this.update(data);
	},

	update: function(data) {
		return $extend(this, data);
	},

	validate: function() {
		var options = this.base.options;
		
		if (options.fileListMax && this.base.fileList.length >= options.fileListMax) {
			this.validationError = 'fileListMax';
			return false;
		}
		
		if (options.fileListSizeMax && (this.base.size + this.size) > options.fileListSizeMax) {
			this.validationError = 'fileListSizeMax';
			return false;
		}
		
		return true;
	},

	invalidate: function() {
		this.invalid = true;
		this.base.fireEvent('fileInvalid', this, 10);
		return this.fireEvent('invalid', this, 10);
	},

	render: function() {
		return this;
	},

	setOptions: function(options) {
		if (options) {
			if (options.url) options.url = Swiff.Uploader.qualifyPath(options.url);
			this.base.remote('fileSetOptions', this.id, options);
			this.options = $merge(this.options, options);
		}
		return this;
	},

	start: function() {
		this.base.fileStart(this);
		return this;
	},

	stop: function() {
		this.base.fileStop(this);
		return this;
	},

	remove: function() {
		this.base.fileRemove(this);
		return this;
	},

	requeue: function() {
		this.base.fileRequeue(this);
	} 

});

/**
 * FancyUpload - Flash meets Ajax for powerful and elegant uploads.
 * 
 * Updated to latest 3.0 API. Hopefully 100% compat!
 *
 * @version		3.0
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <http://digitarald.de>
 * @copyright	Authors
 */

var FancyUpload2 = new Class({

	Extends: Swiff.Uploader,
	
	options: {
		queued: 1,
		// compat
		limitSize: 0,
		limitFiles: 0,
		validateFile: $lambda(true)
	},

	initialize: function(status, list, options) {
		this.status = $(status);
		this.list = $(list);

		// compat
		options.fileClass = options.fileClass || FancyUpload2.File;
		options.fileSizeMax = options.limitSize || options.fileSizeMax;
		options.fileListMax = options.limitFiles || options.fileListMax;

		this.parent(options);

		this.addEvents({
			'load': this.render,
			'select': this.onSelect,
			'cancel': this.onCancel,
			'start': this.onStart,
			'queue': this.onQueue,
			'complete': this.onComplete
		});
	},

	render: function() {
		this.overallTitle = this.status.getElement('.overall-title');
		this.currentTitle = this.status.getElement('.current-title');
		this.currentText = this.status.getElement('.current-text');

		var progress = this.status.getElement('.overall-progress');
		this.overallProgress = new Fx.ProgressBar(progress, {
			text: new Element('span', {'class': 'progress-text'}).inject(progress, 'after')
		});
		progress = this.status.getElement('.current-progress')
		this.currentProgress = new Fx.ProgressBar(progress, {
			text: new Element('span', {'class': 'progress-text'}).inject(progress, 'after')
		});
				
		this.updateOverall();
	},

	onSelect: function() {
		this.status.removeClass('status-browsing');
	},

	onCancel: function() {
		this.status.removeClass('file-browsing');
	},

	onStart: function() {
		this.status.addClass('file-uploading');
		this.overallProgress.set(0);
	},

	onQueue: function() {
		this.updateOverall();
	},

	onComplete: function() {
		this.status.removeClass('file-uploading');
		if (this.size) {
			this.overallProgress.start(100);
		} else {
			this.overallProgress.set(0);
			this.currentProgress.set(0);
		}
		
	},

	updateOverall: function() {
		this.overallTitle.set('html', MooTools.lang.get('FancyUpload', 'progressOverall').substitute({
			total: Swiff.Uploader.formatUnit(this.size, 'b')
		}));
		if (!this.size) {
			this.currentTitle.set('html', MooTools.lang.get('FancyUpload', 'currentTitle'));
			this.currentText.set('html', '');
		}
	},
	
	/**
	 * compat
	 */
	upload: function() {
		this.start();
	},
	
	removeFile: function() {
		return this.remove();
	}

});

FancyUpload2.File = new Class({
	
	Extends: Swiff.Uploader.File,

	render: function() {
		if (this.invalid) {
			if (this.validationError) {
				var msg = MooTools.lang.get('FancyUpload', 'validationErrors')[this.validationError] || this.validationError;
				this.validationErrorMessage = msg.substitute({
					name: this.name,
					size: Swiff.Uploader.formatUnit(this.size, 'b'),
					fileSizeMin: Swiff.Uploader.formatUnit(this.base.options.fileSizeMin || 0, 'b'),
					fileSizeMax: Swiff.Uploader.formatUnit(this.base.options.fileSizeMax || 0, 'b'),
					fileListMax: this.base.options.fileListMax || 0,
					fileListSizeMax: Swiff.Uploader.formatUnit(this.base.options.fileListSizeMax || 0, 'b')
				});
			}
			this.remove();
			return;
		}
		
		this.addEvents({
			'start': this.onStart,
			'progress': this.onProgress,
			'complete': this.onComplete,
			'error': this.onError,
			'remove': this.onRemove
		});
		
		this.info = new Element('span', {'class': 'file-info'});
		this.element = new Element('li', {'class': 'file'}).adopt(
			new Element('span', {'class': 'file-size', 'html': Swiff.Uploader.formatUnit(this.size, 'b')}),
			new Element('a', {
				'class': 'file-remove',
				href: '#',
				html: MooTools.lang.get('FancyUpload', 'remove'),
				title: MooTools.lang.get('FancyUpload', 'removeTitle'),
				events: {
					click: function() {
						this.remove();
						return false;
					}.bind(this)
				}
			}),
			new Element('span', {'class': 'file-name', 'html': MooTools.lang.get('FancyUpload', 'fileName').substitute(this)}),
			this.info
		).inject(this.base.list);
	},
	
	validate: function() {
		return (this.parent() && this.base.options.validateFile(this));
	},
	
	onStart: function() {
		this.element.addClass('file-uploading');
		this.base.currentProgress.cancel().set(0);
		this.base.currentTitle.set('html', MooTools.lang.get('FancyUpload', 'currentFile').substitute(this));
	},

	onProgress: function() {
		this.base.overallProgress.start(this.base.percentLoaded);
		this.base.currentText.set('html', MooTools.lang.get('FancyUpload', 'currentProgress').substitute({
			rate: (this.progress.rate) ? Swiff.Uploader.formatUnit(this.progress.rate, 'bps') : '- B',
			bytesLoaded: Swiff.Uploader.formatUnit(this.progress.bytesLoaded, 'b'),
			timeRemaining: (this.progress.timeRemaining) ? Swiff.Uploader.formatUnit(this.progress.timeRemaining, 's') : '-'
		}));
		this.base.currentProgress.start(this.progress.percentLoaded);
	},
	
	onComplete: function() {
		this.element.removeClass('file-uploading');
		
		this.base.currentText.set('html', 'Upload completed');
		this.base.currentProgress.start(100);
		
		if (this.response.error) {
			var msg = MooTools.lang.get('FancyUpload', 'errors')[this.response.error] || '{error} #{code}';
			this.errorMessage = msg.substitute($extend({
				name: this.name,
				size: Swiff.Uploader.formatUnit(this.size, 'b')
			}, this.response));
			var args = [this, this.errorMessage, this.response];
			
			this.fireEvent('error', args).base.fireEvent('fileError', args);
		} else {
			this.base.fireEvent('fileSuccess', [this, this.response.text || '']);
		}
	},

	onError: function() {
		this.element.addClass('file-failed');
		var error = MooTools.lang.get('FancyUpload', 'fileError').substitute(this);
		this.info.set('html', '<strong>' + error + ':</strong> ' + this.errorMessage);
	},

	onRemove: function() {
		this.element.getElements('a').setStyle('visibility', 'hidden');
		this.element.fade('out').retrieve('tween').chain(Element.destroy.bind(Element, this.element));
	}
	
});

// Avoiding MooTools.lang dependency
(function() {
	var phrases = {
		'progressOverall': 'Overall Progress ({total})',
		'currentTitle': 'File Progress',
		'currentFile': 'Uploading "{name}"',
		'currentProgress': 'Upload: {bytesLoaded} with {rate}, {timeRemaining} remaining.',
		'fileName': '{name}',
		'remove': 'Remove',
		'removeTitle': 'Click to remove this entry.',
		'fileError': 'Upload failed',
		'validationErrors': {
			'duplicate': 'File <em>{name}</em> is already added, duplicates are not allowed.',
			'sizeLimitMin': 'File <em>{name}</em> (<em>{size}</em>) is too small, the minimal file size is {fileSizeMin}.',
			'sizeLimitMax': 'File <em>{name}</em> (<em>{size}</em>) is too big, the maximal file size is <em>{fileSizeMax}</em>.',
			'fileListMax': 'File <em>{name}</em> could not be added, amount of <em>{fileListMax} files</em> exceeded.',
			'fileListSizeMax': 'File <em>{name}</em> (<em>{size}</em>) is too big, overall filesize of <em>{fileListSizeMax}</em> exceeded.'
		},
		'errors': {
			'httpStatus': 'Server returned HTTP-Status <code>#{code}</code>',
			'securityError': 'Security error occured ({text})',
			'ioError': 'Error caused a send or load operation to fail ({text})'
		}
	};
	
	if (MooTools.lang) {
		MooTools.lang.set('en-US', 'FancyUpload', phrases);
	} else {
		MooTools.lang = {
			get: function(from, key) {
				return phrases[key];
			}
		};
	}
})();

/**
 * Fx.ProgressBar
 *
 * @version		1.1
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <mail [at] digitarald [dot] de>
 * @copyright	Authors
 */

Fx.ProgressBar = new Class({

	Extends: Fx,

	options: {
		text: null,
		url: null,
		transition: Fx.Transitions.Circ.easeOut,
		fit: true,
		link: 'cancel'
	},

	initialize: function(element, options) {
		this.element = $(element);
		this.parent(options);
				
		var url = this.options.url;
		if (url) {
			this.element.setStyles({
				'background-image': 'url(' + url + ')',
				'background-repeat': 'no-repeat'
			});
		}


		
		if (this.options.fit) {
			url = url || this.element.getStyle('background-image').replace(/^url\(["']?|["']?\)$/g, '');
			if (url) {
				var fill = new Image();
				fill.onload = function() {
					this.fill = fill.width;
					fill = fill.onload = null;
					this.set(this.now || 0);
				}.bind(this);
				fill.src = url;
				if (!this.fill && fill.width) fill.onload();
			}
		} else {
			this.set(0);
		}
	},

	start: function(to, total) {
		return this.parent(this.now, (arguments.length == 1) ? to.limit(0, 100) : to / total * 100);
	},

	set: function(to) {
		this.now = to;
		var css = (this.fill)
			? (((this.fill / -2) + (to / 100) * (this.element.width || 1) || 0).round() + 'px')
			: ((100 - to) + '%');
		
		this.element.setStyle('backgroundPosition', css + ' 0px').title = Math.round(to) + '%';
		
		var text = $(this.options.text);
		if (text) text.set('text', Math.round(to) + '%');
		
		return this;
	}

});
