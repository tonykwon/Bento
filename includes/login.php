<?php $form->open(
			array(
				"id"	=>	"scms_login_form",
				"handler"	=>	"handler->login",
				"operation"	=>	"select",
				"table"	=>	"account,account_permission_x",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.login.save();",
										"onfail"	=>	"bento.message.open({'text':bento.form.response.message,'clear':true,'class':'error'});"
						)
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
		);?><br />

<label>Password</label>

<?php $form->password(
			array(
				"id"	=>	"password",
				"name"	=>	"account.password",
				"required"	=> "Please enter a valid password."	
			)
		);?><br />

<div class="buttons">

	<!--scms:button:close-->

    <!--scms:button:continue-->
    
    <!--scms:button:forgot-->

</div>

<?php $form->close();?>

