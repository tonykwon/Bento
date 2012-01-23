<!DOCTYPE html>
<html xml:lang="<!--scms:language-->" lang="<!--scms:language-->">
<head>
        <title>+ Bento | <!--scms:meta:title--></title>
        <meta name="description" content="<!--scms:meta:description-->">
        <meta name="robots" content="index, follow, noodp">    <meta http-equiv="x-dns-prefetch-control" content="on">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"></head>
    
<body>

<div id="topbar" class="topbar">

    <div class="topbar-inner">

        <div class="container">
               
            <ul class="nav">
            
            	<?php // Setup the admin menu
				foreach( $scms->private->admin_menu as $menu ){ ?>
				
                    <li class="menu ">
                        
                        <?php $scms->link(
                                        array(
                                            "slug"	=>	"admin",
                                            "anchor"	=>	$menu["name"],
                                            "class"	=>	"menu",
                                            "variables"	=>	array(
                                                                "page"	=>	$menu["page"]
                                            )
                                        )
                                    );
							
							// Check it out		
							if( isset($menu["menu"]) && is_array($menu["menu"]) ){ ?>
							
                                <ul class="menu-dropdown">
                                
                                	<?php foreach( $menu["menu"] as $menu ){
									
										if( !isset($menu["hidden"]) ){ ?>
                                
                                            <li>
                                            
                                                <?php $scms->link(
                                                                array(
                                                                    "slug"	=>	"admin",
                                                                    "anchor"	=>	$menu["name"],
                                                                    "variables"	=>	array(
                                                                                        "page"	=>	$menu["page"]
                                                                    )
                                                                )
                                                            );?>
                                            
                                            </li>
                                            
                                       <?php } 
									   
									} ?>
			                                        
                                </ul>
							
							<?php // if
							}?>
                                    
					</li>
                    
				<?php // foreach
				} ?>
               
            </ul>
    
        </div>
    
    </div>
  
</div>

<div class="content">

    <div class="span16">
    
    	<?php echo $scms->private->admin_breadcrumb;?>

        <!--scms:page-->
        
    </div>
                
</div>
    
</body>
</html>