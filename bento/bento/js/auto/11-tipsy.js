/**
 * mooTipsy
 * A Mootools port of the popular jQuery plugin Tipsy.
 *
 * @copyright Copyright (c) 2011, Tim Wickstrom
 * @link http://www.timwickstrom.com Tim Wickstrom Blog
 * @version 1.0
 *
 *
 * @Changelog
 * Version 1.0
 *      - fixed numerous issues with focus/blur/mouse event conflicts
 *      - fixed arrow position issue with IE browsers
 *      - fixed auto correct positioning
 *      - fixed position storage to increase speed of script as well as prevent memory leaks
 *      - Added new methods to add a tipsy
 *      - Added delay out (sticky)
 *
 * Version 0.1
 *      - Initial Release
 */
 
var mooTipsy = new Class({
    Implements: [Options, Events],
    options: {
        'fade': .85,
        'gravity': 'w',
        'title': 'title',
        'autoCorrect': true,
        'speed': 250,
        'sticky': 150,
        'destroyTitle': true,
        'offset': {
            b: 12,
            e: 2
        },
        'styles': {
            'background': '#000000',
            'color': '#ffffff',
            'padding': '5px',
            'font-size': '10px',
            'max-width': '200px',
            '-moz-border-radius': '4px',
            '-webkit-border-radius': '4px',
            'border-radius': '4px'
        },
        'arrow': {
            color: '#000000',
            size: 6
        }
    },
    initialize: function(elements,options){
        this.resizeDelayTimer = false;
        this.activeTips = [];
        this.tips = elements;
        this.setOptions(options);
        this.activateTips(this.tips);
    },
    setTipsData: function(tip){
        var rel = tip.getProperty('rel');
        this.activeTips.include(tip);
        tip.store('tipsy.info', {
            'title': tip.getProperty('title'),
            'gravity': ((rel=='n'||rel=='s'||rel=='e'||rel=='w')?rel:this.options.gravity),
            'pos': false,
            'dim': false,
            'box': false,
            'temp': false,
            'hidden': true,
            'state': '',
            'timer': false,
            'titleTip': false
        });
        if(this.options.destroyTitle) {
            tip.removeProperty('title');
        }
    },
    positionTip: function(tip) {
        var pos = tip.getPosition();
        var dim = tip.getSize();
        tip.retrieve('tipsy.info').titleTip.setStyles({
            'z-index': '1000000',
            'display': 'block',
            'visibility': 'visible',
            'padding': 0
        });
        var box = tip.retrieve('tipsy.info').titleTip.getSize();
        tip.retrieve('tipsy.info').titleTip.setStyles({
            'visibility': 'hidden',
            'display': 'none',
            'z-index': '-10'
        });
        this.updateTipData(tip, {
            pos: pos,
            dim: dim,
            box: box
        });
    },
    rePositionTips: function() {
        this.activeTips.each(function(tip){
            this.positionTip(tip);
        }.bind(this));
    },
    updateTipData: function(tip,obj) {
        tip.store('tipsy.info',Object.merge(tip.retrieve('tipsy.info'),obj));
    },
    activateTips: function(tips){
        tips.each(function(tip){
            this.addTip(tip);
        }.bind(this));
        window.addEvent('resize', function(){
            $clear(this.resizeDelayTimer);
            this.resizeDelayTimer = (function(){
                this.rePositionTips();
            }.bind(this)).delay(250);
        }.bind(this));
    },
    addTip: function(tip){
        if(!tip.retrieve('tipsy.info')) {
            this.setTipsData(tip);
            var titleTip = new Element('div',{
                'class': 'tipsy',
                'styles': {
                    'position': 'absolute',
                    'visibility': 'hidden',
                    'display': 'none',
                    'opacity': 0,
                    'z-index': '-10',
                    'text-align':'center'
                }
            }).adopt(
                new Element('div',{
                    'class': 'tipsy-inner',
                    'styles': this.options.styles,
                    'html': tip.retrieve('tipsy.info').title
                })
            ).set('morph',{
                'duration': this.options.speed,
                'link': 'cancel',
                'onComplete': function(){
                    if(tip.retrieve('tipsy.info').hidden == 'hiding') {
                        titleTip.setStyles({
                            'visibility': 'hidden',
                            'display': 'none',
                            'z-index': '-10'
                        });
                        if(titleTip.getElement('.tipsy-arrow')!=undefined) {
                            titleTip.getElement('.tipsy-arrow').destroy();
                        }
                        this.updateTipData(tip, {
                            hidden: true,
                            state: ''
                        });
                    }
                }.bind(this)
            }).inject(document.body);
            this.updateTipData(tip, {
                titleTip: $(titleTip)
            });
            this.positionTip(tip);
            tip.addEvents({
                'mouseenter': function(e){
                    if(tip.retrieve('tipsy.info').hidden){
                        this.updateTipData(tip, {
                            state: 'mouseenter'
                        });
                        this.showTipsy(tip);
                    }
                }.bind(this),
                'mouseleave': function(e){
                    if(tip.retrieve('tipsy.info').state!='focus'&&tip.retrieve('tipsy.info').state!='blur'){
                        this.updateTipData(tip, {
                            state: 'mouseleave'
                        });
                        this.hideTipsy(tip);
                    }
                }.bind(this),
                'focus': function(e){
                    if(tip.retrieve('tipsy.info').hidden){
                        this.updateTipData(tip, {
                            state: 'focus'
                        });
                        this.toggleTipsy(tip);
                    }
                }.bind(this),
                'blur': function(e){
                    this.updateTipData(tip, {
                        state: 'blur'
                    });
                    this.toggleTipsy(tip);
                }.bind(this)
            });
        }
    },
    positionArrow: function(tip){
        var arrow = new Element('div',{
            'class': 'tipsy-arrow',
            'styles': {
                'position':'absolute',
                'width': '0',
                'height': '0',
                'font-size': '0px',
                'line-height': '0px',
                'border-color': 'transparent',
                'border-style': 'solid',
                'border-width': this.options.arrow.size+'px'
            }
        });
        switch (tip.retrieve('tipsy.info').temp?tip.retrieve('tipsy.info').temp:tip.retrieve('tipsy.info').gravity) {
            case 'n':
                arrow.setStyles({
                    'left': '50%',
                    'top': 0,
                    'margin-left': '-'+this.options.arrow.size+'px',
                    'border-top':'none',
                    'border-bottom-color': this.options.arrow.color
                });
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    padding: this.options.arrow.size + 'px 0 0 0'
                });
            break;
            case 's':
                arrow.setStyles({
                    'left': '50%',
                    'bottom': 0,
                    'margin-left': '-'+this.options.arrow.size+'px',
                    'border-bottom':'none',
                    'border-top-color': this.options.arrow.color
                });
                tip.retrieve('tipsy.info').titleTip.setStyles({
                padding: '0 0 ' + this.options.arrow.size + 'px 0'
            });
            break;
            case 'e':
                arrow.setStyles({
                    'right': '0',
                    'top': '50%',
                    'margin-top': '-'+this.options.arrow.size+'px',
                    'border-right':'none',
                    'border-left-color': this.options.arrow.color
                });
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    padding: '0 ' + this.options.arrow.size + 'px 0 0'
                });
            break;
            case 'w':
                arrow.setStyles({
                    'left': '0',
                    'top': '50%',
                    'margin-top': '-'+this.options.arrow.size+'px',
                    'border-left':'none',
                    'border-right-color': this.options.arrow.color
                });
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    padding: '0 0 0 ' + this.options.arrow.size + 'px'
                });
            break;
        }
        arrow.inject(tip.retrieve('tipsy.info').titleTip);
    },
    setGravity: function(tip) {
        var pos = tip.retrieve('tipsy.info').pos;
        var dim = tip.retrieve('tipsy.info').dim;
        var box = tip.retrieve('tipsy.info').box;
        switch (tip.retrieve('tipsy.info').temp?tip.retrieve('tipsy.info').temp:tip.retrieve('tipsy.info').gravity) {
            case 'n':
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    'top': pos.y + dim.y + this.options.offset.b + this.options.arrow.size,
                    'left': (pos.x + (dim.x/2)) - (box.x/2)
                });
                return {
                    'opacity': (this.options.fade!=false?this.options.fade:1),
                    'top': pos.y + dim.y + this.options.offset.e + this.options.arrow.size
                }
            break;
            case 's':
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    'top': pos.y - box.y - this.options.offset.b - (this.options.arrow.size*2),
                    'left': pos.x + (dim.x/2) - (box.x/2)
                });
                return {
                    'opacity': (this.options.fade!=false?this.options.fade:1),
                    'top': pos.y - box.y - this.options.offset.e - (this.options.arrow.size*2)
                }
            break;
            case 'e':
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    'top': pos.y + (dim.y/2) - (box.y/2),
                    'left': pos.x - box.x - this.options.offset.b - (this.options.arrow.size*2)
                });
                return {
                    'opacity': (this.options.fade!=false?this.options.fade:1),
                    'left': pos.x - box.x - this.options.offset.e - (this.options.arrow.size*2)
                }
            break;
            case 'w':
                tip.retrieve('tipsy.info').titleTip.setStyles({
                    'top': pos.y + (dim.y/2) - (box.y/2),
                    'left': pos.x + dim.x + this.options.offset.b + this.options.arrow.size
                });
                return {
                    'opacity': (this.options.fade!=false?this.options.fade:1),
                    'left': pos.x + dim.x + this.options.offset.e + this.options.arrow.size
                }
            break;
        }
    },
    toggleTipsy: function(tip){
        if(tip.retrieve('tipsy.info').hidden){
            this.showTipsy(tip);
        }else if(tip.retrieve('tipsy.info').state != 'focus'){
            this.hideTipsy(tip);
        }
    },
    hideTipsy: function(tip){
        this.updateTipData(tip, {
            hidden: 'hiding',
            state: ''
        });
        this.updateTipData(tip, {
            timer: (function(){
                if(!this.options.destroyTitle) {
                    tip.setProperty('title',tip.retrieve('tipsy.info').title);
                }
                var pos = tip.retrieve('tipsy.info').pos;
                var dim = tip.retrieve('tipsy.info').dim;
                var box = tip.retrieve('tipsy.info').box;
                switch (tip.retrieve('tipsy.info').temp?tip.retrieve('tipsy.info').temp:tip.retrieve('tipsy.info').gravity) {
                    case 'n':
                        tip.retrieve('tipsy.info').titleTip.morph({
                            'top': pos.y + dim.y + this.options.offset.b + this.options.arrow.size,
                            'opacity':(this.options.fade!=false?0:1)
                        });
                    break;
                    case 's':
                        tip.retrieve('tipsy.info').titleTip.morph({
                            'top': pos.y - box.y - this.options.offset.b - (this.options.arrow.size*2),
                            'opacity':(this.options.fade!=false?0:1)
                        });
                    break;
                    case 'e':
                        tip.retrieve('tipsy.info').titleTip.morph({
                            'left': pos.x - box.x - this.options.offset.b - (this.options.arrow.size*2),
                            'opacity':(this.options.fade!=false?0:1)
                        });
                    break;
                    case 'w':
                        tip.retrieve('tipsy.info').titleTip.morph({
                            'left': pos.x + dim.x + this.options.offset.b + this.options.arrow.size,
                            'opacity':(this.options.fade!=false?0:1)
                        });
                    break;
                }
                this.updateTipData(tip, {
                    temp: false
                });
            }.bind(this)).delay(this.options.sticky)
        });
    },
    showTipsy: function(tip){
        if(tip.retrieve('tipsy.info').hidden == 'hiding'){
            $clear(tip.retrieve('tipsy.info').timer);
        }
        if(tip.retrieve('tipsy.info').title){
            var pos = tip.retrieve('tipsy.info').pos;
            var dim = tip.retrieve('tipsy.info').dim;
            var box = tip.retrieve('tipsy.info').box;
            if(tip.getProperty('title')!=null&&tip.getProperty('title')!=tip.retrieve('tipsy.info').title&&tip.retrieve('tipsy.info').hidden != 'hiding'&&this.options.destroyTitle) {
                this.updateTipData(tip, {
                    title: tip.getProperty('title')
                });
                tip.retrieve('tipsy.info').titleTip.getElement('.tipsy-inner').set('html',tip.retrieve('tipsy.info').title);
            }
            if(!this.options.destroyTitle) {
                tip.removeProperty('title');
            }
            if(this.options.autoCorrect) {
                var winSize = $(window).getSize();
                var docScroll = $(document.body).getScroll();
                switch (tip.retrieve('tipsy.info').gravity) {
                    case 'n':
                        if((pos.y + dim.y + box.y + this.options.offset.b) > (winSize.y+docScroll.y)){
                            this.updateTipData(tip, {
                                temp: 's'
                            });
                        }
                    break;
                    case 's':
                        if((docScroll.y+box.y+this.options.offset.b)>(pos.y - this.options.offset.b)){
                            this.updateTipData(tip, {
                                temp: 'n'
                            });
                        }
                    break;
                    case 'e':
                        if(((docScroll.x+box.x+this.options.offset.e) + (this.options.arrow.size*2)) >= (pos.x - this.options.offset.b)){
                            this.updateTipData(tip, {
                                temp: 'w'
                            });
                        }
                    break;
                    case 'w':
                        if((pos.x + dim.x + box.x + this.options.offset.b + (this.options.arrow.size*2)) >= (winSize.x + docScroll.x)){
                            this.updateTipData(tip, {
                                temp: 'e'
                            });
                        }
                    break;
                }
            }else{
                this.updateTipData(tip, {
                    temp: tip.retrieve('tipsy.info').gravity
                });
            }
            this.positionArrow(tip);
            var morphTo = this.setGravity(tip);
            tip.retrieve('tipsy.info').titleTip.setStyles({
                'z-index': '1000000',
                'opacity': (tip.retrieve('tipsy.info').hidden == 'hiding'?tip.getStyle('opacity')!=false?this.options.fade:1:0),
                'display': 'block',
                'visibility': 'visible'
            }).morph(morphTo);
            this.updateTipData(tip, {
                hidden: false
            });
        }
    }
});

// Load it up
bento.tipsy = {}
bento.tipsy.domupdate = function(){
	
	// Loop through them all
	$(document.body).getElements('a.tipsy').each(function(el,e){
		
		// Check this out
		if( !el.get('id'))
		{
		  el.setProperty('id',Date.now() + '_' + Math.floor(Math.random()*1000000));
		}
		
		// If we haven't done it yet, do it
		if( bento.storage.reserve({set:'tipsy','id':el.id}) ){
			new mooTipsy([$(el.id)]);	
		// if
		}
	
	// each
	});
	
// method
}