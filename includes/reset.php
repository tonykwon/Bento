<hr class="space">

<?php $form->open(
			array(
				"handler"	=>	"handler->reset",
				"operation"	=>	"update",
				"table"	=>	"account",
				"criteria"	=>	"id=" . $db->record("account.id"),
				"javascript"	=>	array(
										"onsuccess"	=>	"document.location.href = '" . $scms->remembered() . "'",
										"onfail"	=>	"bento.message.open({'text':bento.form.response.message,'class':'error'});"
						)
			)
		);?> 

	<?php $form->hidden(
                array(
                    "name"	=>	"account.id"
                )
            );?>

	<?php $form->hidden(
                array(
                    "name"	=>	"account.token",
                    "value"	=>	""
                )
            );?>

  <div class="clearfix">
    <label for="xlInput">Password</label>
    <div class="input">
    
        <?php $form->password(
                    array(
                        "name"	=>	"account.password",
                        "required"	=> "Please enter a valid password.",
						"value"	=>	false
                    )
                );?>
                
    </div>
  </div><!-- /clearfix -->
    
  <div class="clearfix">
    <label for="xlInput">Confirm</label>
    <div class="input">
    
        <?php $form->confirm(
                    array(
                        "name"	=>	"account.password",
                        "required"	=> "Please enter a valid password.",
						"value"	=>	false
                    )
                );?>
                
    </div>
  </div><!-- /clearfix -->
              
    <hr class="space">        
    
    <div class="buttons">
    
        <!--scms:button:continue-->
    
    </div>

</fieldset>

<?php $form->close();?>