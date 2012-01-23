<?php if( !isset($_GET["domain_web"]) ){ ?>
	
    <?php
	// Get a list of languages
	$db->select("language");
    
	$form->open(
				array(
					"method"	=>	"get",
					"ajax"	=>	false,	
				)
			);?>

	<h2>CMS <small>Version <?php echo $scms->version;?></small></h2>
	
	<p>A content management system that give you complete flexibility, deploys to all forms of digital media, and integrates into the social graph out of the box.</p>

	<hr>

	<pre>Most of this is already setup for you, so if you just want to get to it, go to the end and hit continue.</pre>

    <hr >

    <h3>Admin Account</h3>

    <div class="clearfix">
        <label for="xlInput">Email Address</label>
        <div class="input">
        
			<?php $form->text(
                        array(
                            "encode"	=>	false,
                            "name"	=>	"email",
                            "validate"	=>	"email",
							"value"	=>	$_SESSION["email"],
                            "required"	=>	"Please enter an email address."
                        )
                    );?>
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">Password</label>
        <div class="input">
        
			<?php $form->text(
                        array(
                            "encode"	=>	false,
                            "name"	=>	"password",
							"value"	=>	"password",
                            "required"	=>	"Please enter a password."
                        )
                    );?>
                
        </div>
    </div><!-- /clearfix -->

    <hr >

    <h3>Deployment</h3>
    
	<p>
        bento will can deploy to different types of media. It allows you to create individual templates for each of the types of media you wish to render your site on. Please select where you would like to deploy your site. You can enable or disable this feature at any time.
    </p>
   
    <hr class="space">

    <div class="clearfix">
        <label for="xlInput">Mobile (web)</label>
        <div class="input">
        
            <input type="checkbox" alt="mobile" class="deploy">
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">Facebook Canvas</label>
        <div class="input">
        
            <input type="checkbox" alt="facebook" class="deploy">
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">Mobile Applications</label>
        <div class="input">
        
            <input type="checkbox" alt="app" class="deploy">
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">Digital Signage (narrowcast)</label>
        <div class="input">
        
            <input type="checkbox" alt="nc" class="deploy">
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">Kiosk</label>
        <div class="input">
        
            <input type="checkbox" alt="kiosk" class="deploy">
                
        </div>
    </div><!-- /clearfix -->

    <hr class="space">

    <h3>Deployment Domains</h3>
    
    <p>
        In order to differentiate where media is being deployed bento requires a sub-domain for each of the deployment types. In order to user this feature you must have control over your domain and sub-domains. Any other sub domain will be forwarded to the root domain in alignment with best practices for seo. You can enable or disable this feature at any time.
    </p>
        
 
    <div class="clearfix">
        <label for="xlInput">Web</label>
        <div class="input">
        
			<?php $form->text(
                        array(
                            "encode"	=>	false,
                            "name"	=>	"domain_web",
                            "validate"	=>	"http",
							"value"	=>	$_SERVER['HTTP_HOST'],
                            "required"	=>	"Please enter your web address address."
                        )
                    );?>
                
        </div>
    </div><!-- /clearfix -->
  
    <div id="deploy_mobile" class="clearfix hide">
        <label for="xlInput">Mobile</label>
        <div class="input">
        
            <input name="domain_mobile" value="m.<?php echo $_SERVER['HTTP_HOST'];?>">
                
        </div>
    </div><!-- /clearfix -->

    <div id="deploy_facebook" class="clearfix hide">
        <label for="xlInput">Facebook</label>
        <div class="input">
        
            <input name="domain_facebook" value="fb.<?php echo $_SERVER['HTTP_HOST'];?>">
                
        </div>
    </div><!-- /clearfix -->
    
    <div id="deploy_app" class="clearfix hide">
        <label for="xlInput">Mobile App</label>
        <div class="input">
        
            <input name="domain_app" value="app.<?php echo $_SERVER['HTTP_HOST'];?>">
                
        </div>
    </div><!-- /clearfix -->
    
    <div id="deploy_narrowcast" class="clearfix hide">
        <label for="xlInput">Narrowcast</label>
        <div class="input">
        
            <input name="domain_narrowcast" value="nc.<?php echo $_SERVER['HTTP_HOST'];?>">
                
        </div>
    </div><!-- /clearfix -->
    
    <div id="deploy_kiosk" class="clearfix hide">
        <label for="xlInput">Kiosk</label>
        <div class="input">
        
            <input name="domain_kiosk" value="k.<?php echo $_SERVER['HTTP_HOST'];?>">
                
        </div>
    </div><!-- /clearfix -->

    <hr >


    <h3>Mail</h3>
    
	<p>
		Mail is a built in feature. Each new email you create can have it's own from name and address. We will create all new mail with the following information. You should have access to this email address.
    </p>

    <hr class="space">

    <div class="clearfix">
        <label for="xlInput">From Address</label>
        <div class="input">
        
			<?php $form->text(
                        array(
                            "encode"	=>	false,
                            "name"	=>	"from_email",
                            "validate"	=>	"email",
							"value"	=>	"info@" . $_SERVER['HTTP_HOST'],
                            "required"	=>	"Please enter an email address."
                        )
                    );?>
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">From Name</label>
        <div class="input">
        
            <input name="from_name" value="<?php echo $_SERVER['HTTP_HOST'];?>">
                
        </div>
    </div><!-- /clearfix -->

    <hr >


    <h3>Feeds</h3>
    
	<p>bento CMS can load new content into a page without reloading the page itself. These are called feeds. They work to create a dynamic, fast, and responsive web environment. +Bento will look for new content at the interval of <span class="label notice">time</span> in seconds you chose. Bento will stop looking for new content if the user becomes idle. You can tell Bento what <span class="label notice">type</span> of feed you'd like. Pull polls for client data and is easiest on non-specialized server infrastructure. Push content (pushing from server) requires more server knowledge to optimize, but in the end will reduce the load on your server. Leave it alone if you're uncomfortable.</p>

    <div class="clearfix">
        <label for="appendedInput">Feed Time</label>
        <div class="input">
            <div class="input-append">
                <input class="mini" id="feed_time" name="feed_time" value="30" size="16" type="text">
                <label class="add-on">Seconds</label>
            </div>
        </div>
    </div>

    <div class="clearfix">
        <label for="appendedInput">Type</label>
        <div class="input">
            <select name="feed_type">
                <option value="pull">Pull (poll from client)</option>
                <option value="push">Push (push from server)</option>
            </select>
        </div>
    </div>

    <hr >


    <h3>Language</h3>

	<p>
    	Automatic translations are a feature of bento. In order to use this feature we need to know what the default language (your native language) is.
    </p>


    <div class="clearfix">
        <label for="xlInput">Default Language</label>
        <div class="input">
        
            <?php $form->select(
							array(
								"encode"	=>	false,
								"name"	=>	"language",
								"value"	=>	"name",
								"text"	=>	"name",
								"values"	=>	$db->recordset("language"),
								"selected"	=>	"english"
							)
						);?>
                
        </div>
    </div><!-- /clearfix -->

    <hr >


    <h3>Social Media</h3>
    
	<p>
		bento is fully integrated into the social graph. You can use Facebook or Twitter as a login, or post, read, and interact with a users account. You must sign up for an APP ID on either Facebook or Twitter to use this functionality. You can enable it at any time, and if you opt out of social media you can use the native account system built into bento.
    </p>


    <div class="clearfix">
        <label for="xlInput">Facebook</label>
        <div class="input">
        
            <input type="checkbox" alt="facebook" class="sm">
                
        </div>
    </div><!-- /clearfix -->

    <div class="clearfix">
        <label for="xlInput">Twitter</label>
        <div class="input">
        
            <input type="checkbox" alt="twitter" class="sm">
                
        </div>
    </div><!-- /clearfix -->

    <div id="sm_facebook" class="hide">
    
        <hr class="space">
        
        <hr >
        
        <hr class="space">

        <h3>Facebook <small><a href="https://developers.facebook.com/apps" target="blank">Get One</a></small></h3>
        
        <hr class="space">
 
        <div class="clearfix">
            <label for="xlInput">APP ID</label>
            <div class="input">
            
				<input name="facebook_app_id" value="<?php echo $scms->private->facebook->app_id;?>">
                    
            </div>
        </div><!-- /clearfix -->

        <div class="clearfix">
            <label for="xlInput">Key</label>
            <div class="input">
            
				<input name="facebook_key" value="<?php echo $scms->private->facebook->key;?>">
                    
            </div>
        </div><!-- /clearfix -->

        <div class="clearfix">
            <label for="xlInput">Secret</label>
            <div class="input">
            
				<input name="facebook_secret" value="<?php echo $scms->private->facebook->secret;?>">
                    
            </div>
        </div><!-- /clearfix -->

        <div class="clearfix">
            <label for="xlInput">Permissions</label>
            <div class="input">
            
				<textarea name="facebook_perms"><?php echo $scms->private->facebook->perms;?></textarea>
                    
            </div>
        </div><!-- /clearfix -->

    </div>
    
    <div id="sm_twitter" class="hide">
    
        <hr class="space">
        
        <hr >
        
        <hr class="space">

        <h3>Twitter <small><a href="https://dev.twitter.com/apps" target="blank">Get One</a></small></h3>
        
        <hr class="space">

        <div class="clearfix">
            <label for="xlInput">APP ID</label>
            <div class="input">
            
				<input name="twitter_key" value="<?php echo $scms->private->twitter->key;?>">
                    
            </div>
        </div><!-- /clearfix -->

        <div class="clearfix">
            <label for="xlInput">Key</label>
            <div class="input">
            
				<input name="twitter_secret" value="<?php echo $scms->private->twitter->secret;?>">
                    
            </div>
        </div><!-- /clearfix -->

    </div>

    <hr >
    
    <div class="buttons">
    
        <input type="submit" value="Complete" class="button blue">

    </div>
	
	<?php $form->close();?>
	
<?php } else {  

		// Check if the db exists yet
		if( !$db->table_exists("setup") ){
		
			$sql = $file->read("/bento/scms/install/install/sql/scms.sql");
		
			// Let's execute the sql
			if( $db->execute( $sql ) ){
			
				// redirect the site
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: /?' . $_SERVER['QUERY_STRING'] );	
			
			// if
			} ?>
            
            <h2>Error!</h2>
                        
            <p>We were unable to install the sql file on the server. Try manually and then click continue. The SQL file is located at:</p>
    
            <strong>/bento/scms/install/scms.sql</strong>
	
		<?php
		// Here you go!
		} else {
		
			// Get the form class
			global $file;
		
			// Create the plugins directory
			$file->create_directory("plugins");
		
			// Update the cms setup
			$db->update(
					array(
						"table"	=>	"setup",
						"values"	=>	 array(
											"domain_web"	=>	$_GET["domain_web"],
											"domain_mobile"	=>	$_GET["domain_mobile"],
											"domain_app"	=>	$_GET["domain_app"],
											"domain_facebook"	=>	$_GET["domain_facebook"],
											"domain_narrowcast"	=>	$_GET["domain_narrowcast"],
											"domain_kiosk"	=>	$_GET["domain_kiosk"],
											"mail_email"	=> $_GET["from_email"],
											"mail_from"	=> $_GET["from_name"],
											"feed_time"	=>	(int)$_GET["feed_time"],
											"language"	=>	$_GET["language"]
										),
						"crtieria"	=>	"id=1"
						)
					);

			// Create the new user
			$db->update(
						array(
							"table"	=>	"account",
							"values"	=> array(
												"email"	=>	$_GET["email"],
												"password"	=>	$_GET["password"]
											),
							"criteria"	=>	"id=1"
							)	
						);

			// Create the new user
			$db->update(
					array(
						"table"	=>	"mail",
						"values"	=>	 array(
											"from_name"	=>	$_GET["from_name"],
											"from_email"	=>	$_GET["from_email"]
										),
						"criteria"	=>	"id=1 or id=2"
						)
					);
					

			// Now, let's
			/*
			$config = array(
					"state"	=>	"installed",
					"private" => array(
								"facebook"	=>	array(
												"app_id"	=>	$_GET["facebook_app_id"],
												"key"	=>	$_GET["facebook_key"],
												"secret"	=>	$_GET["facebook_secret"],
												"perms"	=>	$_GET["facebook_perms"]
												),
								"twitter"	=>	array(
												"key"	=>	$_GET["twitter_key"],
												"secret"	=>	$_GET["twitter_secret"]
												),
								"feed_variables"	=>	(object)array(),
								"agents"	=>	array(
													"facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)"
													)
								)
						); */
						
		// Check it
		$bento->install("/bento/scms/install/install/zip/scms.zip");
		
		// Get the conf
		$conf = $bento->configure( $scms->config, $config );?>
	
		<h2>Error!</h2>
					
		<p>We were unable to write to the configuration file. Please copy and paste the following code into a file.</p>

		<h3><?php echo $scms->config;?></h3>

		<textarea><?php echo $conf;?></textarea>
    
    <?php } 

} ?>