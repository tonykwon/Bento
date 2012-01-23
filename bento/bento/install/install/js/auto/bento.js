document.addEvent('domready',function(){
  $$('.deploy').each(function(e){e.addEvent('click',function(){
										  bento.scms.install.deploy( {type:this.alt,state:this.checked} );
										  });	
							 });
  $$('.sm').each(function(e){e.addEvent('click',function(){
										  bento.scms.install.sm( {type:this.alt,state:this.checked} );
										  });	
							 });
  });
