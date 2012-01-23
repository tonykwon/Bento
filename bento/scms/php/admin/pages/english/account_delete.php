<?php $form->open(
			array(
				"operation"	=>	"delete",
				"table"	=>	"account",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.modal.close({text:'User deleted','class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"account.id"
			)
		);?> 

<ul class="tabs">

    <li class="active">Confirm</li>
    
</ul>
<div class="tab-content">
    
    <div class="active">
    
        <?php $form->text(
                    array(
                        "name"	=>	"account.email",
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