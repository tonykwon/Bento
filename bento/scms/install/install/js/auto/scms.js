// This will turn on and off options
bento.scms.install.deploy = function( deploy ){
	if( deploy.state ){
		$( "deploy_" + deploy.type ).setStyle('display','block');	
	} else {
		$( "deploy_" + deploy.type ).setStyle('display','none');	
	// if
	}
// if
}
bento.scms.install.sm = function( sm ){
	
	if( sm.state ){
		$( "sm_" + sm.type ).setStyle('display','block');	
	} else {
		$( "sm_" + sm.type ).setStyle('display','none');	
	// if
	}
// if
}
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
