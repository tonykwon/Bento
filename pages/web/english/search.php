<div class="row">

    <div class="spantwo-thirds">

		<?php if( $db->recordcount("page") > 0 ){?>
        
            <?php // Let's loop through the pages
            foreach( $db->recordset("page") as $page ){
            
                // Filter in the correct page
                $db->filter("page.id=" . $page["id"] ); ?>
                
                <h3><?php $scms->link(
                            array(
                                "slug"	=>	$page["slug"]
                            )
                        );?></h3>
        
                <p><?php $scms->content(
                                array(
                                    "slug"	=>	$page["slug"],
                                    "excerpt"	=>	true
                                )
                            );?></p>
                
                <hr >
                
            <?php	
            // foreach
            } ?>
            
        <?php } else { ?>
        
            <h4>No results found.</h4>
            
        <?php } ?>
		
	</div>
    
</div>