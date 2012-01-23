bento.scms.mobile = {};
// Some touch events

window.addEvent('domready',function() {  ['touchstart','touchmove','touchend','touchcancel','gesturestart','gesturechange','gestureend','orientationchange','onorientationchange','resize'].each(function(ev) {
    window.addEvent(ev,function(e) {
      //alert( ev );
	  //bento.scms.mobile.resize();
    });
  });
});
window.addEvent('domready',function(){
								   window.scrollTo(0,0);
								   });