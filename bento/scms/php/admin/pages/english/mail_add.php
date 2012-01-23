<?php $form->open(
			array(
				"handler"	=>	"handler->mail_add",
				"operation"	=>	"insert",
				"table"	=>	"mail",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.admin.clear();bento.message.open({text:bento.form.response.message,'class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<ul class="tabs">
    <li class="active">Setup</li>
    <li>From</li>
    <li>Content</li>
    <li>Template</li>
</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"mail.name",
                        "id"	=>	"name",
                        "required"	=>	"Please enter a name.",
						"class"	=>	"clear"
                    )
                );?>    

        <label>Slug</label>

        <?php $form->text(
                array(
                    "name"	=>	"mail.slug",
                    "id"	=>	"slug",
                    "readonly"	=>	true,
					"class"	=>	"clear"
                )
            );?>  
    
        <label>Subject</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"mail.subject",
                        "required"	=>	"Please enter a subject.",
						"id"	=>	"subject",
						"class"	=>	"clear"
                    )
                );?>

	</div>

	<div>

        <label>Name</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"mail.from_name",
						"required"	=>	"Please enter a from name",
						"value"	=>	$db->record("setup.mail_from")
                    )
                );?>
                
        <label>Email</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"mail.from_email",
						"required"	=>	"Please enter a from email",
						"validate"	=>	"email",
						"value"	=>	$db->record("setup.mail_email")
                    )
                );?>
                

    </div>

    <div>

		<?php $form->textarea(
                        array(
                            "name"	=>	"content",
							"id"	=>	"content",
                            "html"	=>	false,
                            "class"	=>	"page",
							"class"	=>	"clear"
                            )
                        );?>

    </div>
    
    <div>
    
		<?php $form->select(
                    array(
                        "name"	=>	"mail.template",
                        "values"	=>	$scms->private->admin_templates["mail"],
                        "selected"	=>	$db->record("mail.template"),
                        "value"	=>	"value",
                        "text"	=>	"value"
                    )
                );?>
    
    </div>   
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>