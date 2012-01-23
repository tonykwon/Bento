<?php $form->open(
			array(
				"id"	=>	"scms_forgot_form",
				"handler"	=>	"handler->forgot",
				"operation"	=>	"select",
				"table"	=>	"account",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.forgot.complete(bento.form.response.message);",
										"onfail"	=>	"bento.message.open({'text':bento.form.response.message,'class':'error'});"
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
		);?>

<div class="buttons">

    <!--scms:button:close-->

    <!--scms:button:continue-->

	<!--scms:button:login-->

</div>

<?php $form->close();?>