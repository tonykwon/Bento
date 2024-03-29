/*
---
description: This is a simple plugin MooTools delivering various geo tasks like geolocation, reverse geocoding and content analysis.

authors:
- Adrian Statescu (http://thinkphp.ro)

license:
- MIT-style license

requires:
 core/1.3: '*'

provides: [MooGeo]
...
*/
MooGeo_map = {};
var MooGeo = new Class({
 
            Implements: [Options, Events],
  
            options: {
              endpoint: 'http://query.yahooapis.com/v1/public/yql?q=',
              format: 'json',
              injectScript: document.head
            },

            initialize: function(){
                 var args = arguments;
                 for(var i=0;i<args.length;i++) {
                         if(typeof args[i] === 'object' && typeof args[i].join === 'undefined') {
                              this.setOptions(args[i]); 
                         } 
                 }
                 if(args[0] === 'ipvisitor') {
                        this.grabIP();
                 };//endif
                 if(args[0] === 'visitor') {
                       this.getVisitor();  
                 }//endif
                 if(typeof args[0] === 'string' && args[0] != 'visitor' && args[0] != 'ipvisitor') {
                     if(args[0]) {
                        if(/^http:\/\/.*/.test(args[0])) {
                           this.getFromURL(args[0]);
                        } else if(/^[\d+\.?]+$/.test(args[0])) {
                          this.getFromIP(args[0]);
                        } else {
                          this.getFromText(args[0]);
                        }
                      }//endif
                  }//endif

                  var lat = args[0], lon = args[1];
                  if(typeof lat.join !== "undefined" && args[0][1]) {                              
                     lat = args[0][0];
                     lon = args[0][1]; 
                  }//endif

                  if(isFinite(lat) && isFinite(lon)){
                     if(lat>-90 && lat<90 && lon>-180 && lon<180){
                          this.getFromLatLon(lat,lon);
                     }//endif
                   }//endif
            },

            getFromIP: function(ip) {
                var yql = "select * from geo.places where woeid in (select place.woeid from flickr.places where (lat,lon) in (select Latitude,Longitude from ip.location where ip='"+ip+"' and key='9fa9c90700b942bbbbbeb19decb33a591140386d2d407d335c46467703002e0b'))";
                this.fireEvent('request',yql);   
                if(window.console) {console.log(yql);}
                this.load(yql,'MooGeo_map.request');
            },

            load: function(yql,cb){                 
                 if(document.id('yqlgeodata')) {
                    var old = document.id('yqlgeodata');
                    old.parentNode.removeChild(old);
                 }
                 var src = this.options.endpoint +
                 encodeURIComponent(yql) + '&format=' + this.options.format + '&callback='+ cb +
                 '&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
                 new Element('script',{id: 'yqlgeodata',src: src}).inject(this.options.injectScript);
                 MooGeo_map['request'] = function(data){this.retrieved(data)}.bind(this);
            },

            retrieved: function(o){
                if(o.query.results) {
                     this.fireEvent('complete',o.query.results).fireEvent('success',o.query.results);
                } else {
                     this.fireEvent('failure',{error: o.query}).fireEvent('error',{error: o.query});
                }  
            },
      
            grabIP: function() {
                this.fireEvent('request');  
                this.jsonp('http://jsonip.appspot.com/?callback=MooGeo_map.request');
            },

            jsonp: function(src) {
                 if(document.id('yqlgeodata')) {
                      var old = document.id('yqlgeodata');
                      old.parentNode.removeChild(old);
                 }
                 new Element('script',{id: 'yqlgeodata',src: src}).inject(this.options.injectScript);
                 MooGeo_map['request'] = function(data){this.ipgrab(data)}.bind(this);
                 MooGeo_map['ipin'] = function(data){this.ipin(data)}.bind(this);
            },

            ipin: function(o) {
                 this.getFromIP(o.ip);
            }, 

            ipgrab: function(o) {
                if(o.ip) {
                   this.fireEvent('complete',{ip: o.ip}).fireEvent('success',{ip: o.ip});
                } else {
                   this.fireEvent('failure',{error: 'Internal Error'}).fireEvent('error',{error: 'Internal Error'});
                }
            },

            getVisitor: function() {
                if(navigator.geolocation) {
                   navigator.geolocation.getCurrentPosition(
                          function(position) {
                            this.getFromLatLon(position.coords.latitude,position.coords.longitude);
                          }.bind(this),
                          function(error) {
                            this.retrieveIP();
                          }.bind(this)
                   );
                } else {
                  this.retrieveIP();
                }
            },

            retrieveIP: function() {
                this.fireEvent('request');
                this.jsonp('http://jsonip.appspot.com/?callback=MooGeo_map.ipin');
            },

            getFromLatLon: function(lat,lon) {
                 var yql = "select * from geo.places where woeid in (select place.woeid from flickr.places where lat='"+lat+"' and lon='"+lon+"')";
                 this.fireEvent('request',yql);
                 if(window.console) {console.log(yql);}
                 this.load(yql,'MooGeo_map.request');  
            },

            getFromText: function(text) {
                 var yql = "select * from geo.places where woeid in (select match.place.woeId from geo.placemaker where documentContent='"+text+"' and documentType='text/plain' and appid='')"; 
                 this.fireEvent('request',yql); 
                 if(window.console) {console.log(yql);}
                 this.load(yql,'MooGeo_map.request'); 
            },

            getFromURL: function(url) {
                 var yql = "select * from geo.places where woeid in (select match.place.woeId from geo.placemaker where documentURL='"+url+"' and documentType='text/html' and appid='')";                                                                    
                 this.fireEvent('request',yql);
                 if(window.console) {console.log(yql);}
                 this.load(yql,'MooGeo_map.request');
            }

});//end class MooGeo
