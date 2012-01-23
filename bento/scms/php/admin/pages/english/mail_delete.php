<?php $form->open(
			array(
				"handler"	=>	"handler->mail_delete",
				"operation"	=>	"delete",
				"table"	=>	"mail",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.modal.close({text:bento.form.response.message,'class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"mail.slug"
			)
		);?> 

<ul class="tabs">
    <li class="active">Confirm</li>
</ul>
<div class="tab-content">
    
    <div class="active">
    
        <?php $form->text(
                    array(
                        "name"	=>	"mail.name",
                        "id"	=>	"name",
                        "readonly"	=>	true
                    )
                );?> 

	</div> 
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>