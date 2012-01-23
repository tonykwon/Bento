<?php $form->open(
			array(
				"handler"	=>	"handler->register",
				"operation"	=>	"insert",
				"table"	=>	"account",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.register.save(bento.form.response.message);",
										"onfail"	=>	"bento.message.open({'text':bento.form.response.message,'class':'error'});"
						)
			)
		);?> 

<?php $form->hidden(
			array(
				"name"	=>	"account.token",
				"value"	=>	$scms->token()
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"account.date_register",
				"value"	=>	time()
			)
		);?>

<label>Email Address</label>

<?php $form->text(
			array(
				"id"	=>	"email",
				"name"	=>	"account.email",
				"validate"	=>	"email",
				"required"	=> "Please enter a valid email address."	
			)
		);?>

<label>Password</label>

<?php $form->password(
			array(
				"name"	=>	"account.password",
				"required"	=> "Please enter a valid password."	
			)
		);?>

<label>Confirm</label>

<?php $form->confirm(
			array(
				"name"	=>	"confirm",
				"required"	=> "Please confirm your password."	
			)
		);?>

<p>&nbsp;</p>

<label>Timezone</label>

<?php $form->select(
			array(
				"name"	=>	"account.timezone",
				"values"	=>	$scms->timezones(),
				"value"	=>	"value",
				"text"	=>	"text",
				"validate"	=>	"email",
				"default"	=>	array(
									"text"	=>	"",
									"value"	=>	""
						),
				"required"	=> "Please select your timezone."	
			)
		);?>

<div class="buttons">

    <!--scms:button:close-->

    <!--scms:button:register-->

</div>

<?php $form->close();?>