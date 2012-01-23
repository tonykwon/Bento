<?php			
// This is what we need to continue
$vars = array( 
			"state"	=>	"installed",
			"private"	=>	array("host","database","username","password","webdb")
			);
?>

<?php if( $bento->configure_check($vars) ){ ?>

   	<h2>Database <small>Version <?php echo $db->version;?></small></h2>    

	<p>The database hasn't been installed, the database settings have been changed recently, or the settings you've entered are incorrect. Please enter the following information.</p>
    
	<?php $form->open(
				array(
					"action"	=>	"/",
					"ajax"	=>	false,	
				)
			);?>

        	<?php $form->hidden(
                        array(
                            "name"	=>	"webdb",
                            "encode"	=>	false,
                            "value"	=>	false
                        )
					);?>
		
		<hr>

        <div class="clearfix">
            <label for="xlInput">Host</label>
            <div class="input">
            
        	<?php $form->text(
                        array(
                            "name"	=>	"host",
                            "encode"	=>	false,
                            "value"	=>	$db->private->host,
                            "required"	=>	"Please enter a host."
                        )
                    );?>
                    
            </div>
        </div><!-- /clearfix -->
        
		<hr>
 
        <div class="clearfix">
            <label for="xlInput">Database</label>
            <div class="input">
            
			<?php $form->text(
                            array(
                                "name"	=>	"database",
                                "encode"	=>	false,
                                "value"	=>	$db->private->database,
                                "required"	=>	"Please enter a database."
                            )
                        );?>
                    
            </div>
        </div><!-- /clearfix -->
        
		<hr>

		<div class="clearfix">
            <label for="xlInput">Username</label>
            <div class="input">
            
			<?php $form->text(
                            array(
                                "name"	=>	"username",
                                "encode"	=>	false,
                                "value"	=>	$db->private->username,
                                "required"	=>	"Please enter a database username."
                            )
                        );?>
                    
            </div>
        </div><!-- /clearfix -->
        
		<hr>

		<div class="clearfix">
            <label for="xlInput">Password</label>
            <div class="input">
            
			<?php $form->text(
                            array(
                                "name"	=>	"password",
                                "encode"	=>	false,
                                "value"	=>	$db->private->password,
                                "required"	=>	"Please enter a database password."
                            )
                        );?>
                    
            </div>
        </div><!-- /clearfix -->

		<hr class="double">
		
		<div class="buttons">
		
			<input type="submit" value="Complete" class="button blue">
	
		</div>

	<?php $form->close();?>

<?php } else { 

		// Set this up
		$conf = $bento->configure_posts( $db->config, $vars );?>
		
		<h2>Error!</h2>
					
		<p>We were unable to write to the configuration file. Please copy and paste the following code into the following file name and upload it via ftp and then click continue.</p>

        <hr>

		<textarea><?php echo $conf;?></textarea>

        <hr>

		<strong><?php echo $db->config;?></strong>
            
    	<hr>
        
        <form method="post" action="">
        
			<input type="submit" value="Continue" class="button blue">
	
    	</form>
    
<?php } ?>