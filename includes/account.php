<?php $form->open(
			array(
				"handler"	=>	"handler->account",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.modal.close({'text':bento.form.response.message,'class':'success'});",
										"onfail"	=>	"bento.message.open({'text':bento.form.response.message,'class':'error'});"
						)
			)
		);?> 

<?php $form->hidden(
			array(
				"name"	=>	"account.date_edit",
				"value"	=>	time()
			)
		);?>

<label>Email Address</label>

<?php $form->text(
			array(
				"name"	=>	"account.email",
				"validate"	=>	"email",
				"required"	=> "Please enter a valid email address."	
			)
		);?>

<label>First Name</label>

<?php $form->text(
			array(
				"name"	=>	"account.email",
				"validate"	=>	"email",
				"required"	=> "Please enter your last name."	
			)
		);?>

<label>Last Name</label>

<?php $form->text(
			array(
				"name"	=>	"account.name_first",
				"validate"	=>	"email",
				"required"	=> "Please enter your first name."	
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

<hr class="space">

<hr class="space">

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

    <!--scms:button:submit-->

</div>

<?php $form->close();?>