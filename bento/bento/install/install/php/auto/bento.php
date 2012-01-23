<?php if( !isset($_POST["license"]) ){ ?>

	<h2>Welcome to +Bento <small>Version <?php echo $bento->version;?></small></h2>    

	<hr >

	<p>+Bento is a developer tool set that includes a php framework for files, forms, and database connectivity, along with css and javascript frameworks.</p>
	
	<p>Bundled in this package is a small, developer CMS that painlessly integrates into the social graph (Facebook and Twitter) and has a full suite of simple tools to allow you to develop robust web applications.</p>
	
	<p>+Bento allows you to deploy your web content to all forms of digital media, inluding mobile web, mobile apps, digital signage, Facebook Canvas, and more.</p> 
	
	<hr >

	<h3>Before You Start</h3>
	
	<p>+Bento must be installed in the root directory. By clicking continue, the entire suite will be installed, and possibly overwrite existing files. It is best to install +Bento into an empty, root directory.</p>

	<p>The package you are installing is:</p>
	
	<hr class="space">
	
	<pre><?php echo $bento->private->version;?> Version <?php echo $bento->version;?></pre>
	
	<hr >

	<h3>Licensing</h3>
	
	<p>You don't require a license, however using a license is free and gives you access to the following, useful services:</p>
	
	<hr class="space">
			
	<ul>
		<li>Cronjob scheduling</li>
		<li>Help forums linked directly to your error logs</li>
		<li>Incremental backups</li>
		<li>Update notifications</li>
	</ul>
	
	<p>
		A license number will be automatically downloaded and your account will be setup.
		If you already have an account, this install will be linked to it. 
	</p>
				
	<hr >
	
	<strong>Would you like to license this install? (Remember it's free!)</strong>
	
	<?php $form->open(
				array(
					"action"	=>	"/",
					"ajax"	=>	false,	
				)
			);?>
			
		<?php $form->hidden(
					array(
						"name"	=>	"state",
						"encode"	=>	false,
						"value"	=>	$bento->state,
						"required"	=>	"installed"
					)
				);?>
		
		<hr class="space">

		<div class="clearfix">
			<label for="xlInput">Yes</label>
			<div class="input">
			
				<input type="radio" name="license" value="1" id="license_yes" checked >
					
			</div>
		</div><!-- /clearfix -->

		<div class="clearfix">
			<label for="xlInput">No</label>
			<div class="input">
			
				<input type="radio" name="license" value="0" id="license_no">
					
			</div>
		</div><!-- /clearfix -->

		<hr >

		<h3>Your Account</h3>
		
		<p>Please provide your email address to assist in the installation. We will not use it to spam you.</p>

		<hr class="space">

		<div class="clearfix">
			<label for="xlInput">Email</label>
			<div class="input">
			
				<?php $form->text(
							array(
								"encode"	=>	false,
								"name"	=>	"email",
								"validate"	=>	"email",
								"required"	=>	"Please enter your email address."
							)
						);?>
					
			</div>
		</div><!-- /clearfix -->

		<hr class="space">

		<div class="buttons">
		
			<input type="submit" value="Continue" class="button blue">
	
		</div>

	<?php $form->close();?>

<?php } else { 

	// Check if we want a licence
	if( $_POST["license"] == "1" ){

		// Get a license
		$license = $bento->licensing(
									true,
									array(
										"plugin"	=>	"+Bento",
										"email"	=>	$_POST["email"]
									)
								);

	// Otherwise
	} else {
	
		$license = "";
	
	// if
	}
	
	// Create the plugins directory
	$file->create_directory("bento/bento/tmp");
	$file->create_directory("bento/bento/tmp/js");
	$file->create_directory("bento/bento/tmp/css");

	// Here is the configuration
	$vars = array(
				"license"	=>	$license,
				"state"	=>	"installed"
			);
			
	// This is for later
	$_SESSION["email"] = $_POST["email"];?>
		
		<h2>Error!</h2>
					
		<p>We were unable to write to the configuration file. Please copy and paste the following code into the following file name and upload it via ftp. Then click continue.</p>

		<hr class="space">

		<textarea><?php echo $bento->configure( $bento->config, $vars );?></textarea>

		<hr class="space">

		<strong><?php echo $bento->config;?></strong>
			
		<hr class="space">
		
		<form method="post" action="">
			
			<input type="submit" value="Continue" class="button blue">
	
		</form>
	
<?php } ?>