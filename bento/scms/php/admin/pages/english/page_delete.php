<?php $form->open(
			array(
				"handler"	=>	"handler->page_delete",
				"operation"	=>	"delete",
				"table"	=>	"page",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.modal.close({text:'Page deleted.','class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"slug",
				"value"	=>	$db->record("page.slug")
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"page.id",
				"value"	=>	$db->record("page.id")
			)
		);?>

<ul class="tabs">

    <li class="active">Confirm</li>

</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Page Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"page.name",
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