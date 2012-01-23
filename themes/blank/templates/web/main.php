<!DOCTYPE html>
<html xml:lang="<!--scms:language-->" lang="<!--scms:language-->">
<head>
    <title>+Bento | <!--scms:meta:title--></title>
    <meta name="description" content="<!--scms:meta:description-->">
    <meta name="robots" content="index, follow, noodp">
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
</head>

<body>

<div id="topbar" class="topbar">

  <div class="topbar-inner">

    <div class="container">
    
    	<div class="row">

            <div class="span4">
        
                <h3>
                    <a href="/">+Bento</a>
                </h3>
              
            </div>
            
            <div class="span12">
              
                <!--scms:include:search-->
               
                <ul class="nav secondary-nav">
                    <li class="menu ">
                    	<!--scms:link:admin-->
                    </li>
                    <li class="menu ">
                        <a href="#" class="menu">Languages</a>
                        <ul class="menu-dropdown">
                        	<li><!--scms:switch:english--></li>
                            <li><!--scms:switch:french--></li>
                            <li><!--scms:switch:spanish--></li>
                        </ul>
                    </li>
                    <li class="menu">
                    
                    	<?php if( $scms->logged_in() ){ ?>
                    
                            <a href="<!--scms:url:account-->" class="menu">Account</a>
                            <ul class="menu-dropdown">
                            	<li><!--scms:link:account--></li>
                                <li><!--scms:link:logout--></li>
                            </ul> 
                            
                        <?php } else { ?>
 
                            <a href="<!--scms:url:login-->" class="menu">Login or Register</a>
                            <ul class="menu-dropdown">
                                <li><!--scms:link:login--></li>
                                <li class="divider"></li>
                                <li><!--scms:link:forgot--></li>
                                <li class="divider"></li>
                                <li><!--scms:link:register--></li>
                            </ul>                     
                        
                        <?php } ?>
                            
                    </li>
                
                	<!--scms:include:notifications-->
                
                </ul>
                
            </div>

		</div>

    </div>
    
  </div>
  
</div>

<div class="content main">
 
    <div class="container">

		<div class="row">

			<div class="span16">

				<!--scms:breadcrumb-->
                
            </div>

			<div class="spanone-third">

				<!--custom:shortcut-->
                
            </div>
            
        </div>
    
	</div> 
    
    <div class="container">
    
    	<!--scms:page-->
    
	</div>  

</div>

<div class="shadow footer">
    
    <div class="container">

		<hr>
    
    	<div class="row">
   
            <div class="span16">
                
                <a rel="license" href="http://creativecommons.org/licenses/by/3.0/"><img alt="Creative Commons Licence" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/88x31.png"></a>
                
                <p>&nbsp;</p>
                
                <small><span href="http://purl.org/dc/dcmitype/Text">+Bento</span> by <a href="http://www.builtonbento.com">Jesse James Richard</a> version <?php echo $bento->version;?> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>. Based on a work at <a href="http://www.builtonbento.com/downloads/" rel="copyright">http://www.builtonbento.com/downloads/</a>. Permissions beyond the scope of this license may be available at <a href="http://www.builtonbento.com/licensing/" rel="license">http://www.builtonbento.com/licensing/</a>.</small>
        
            </div>

		</div>

	</div>
    
</div>

<p>&nbsp;</p>

</body>
</html>