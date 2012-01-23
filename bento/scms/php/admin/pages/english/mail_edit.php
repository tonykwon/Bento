<?php $form->open(
			array(
				"handler"	=>	"handler->mail_edit",
				"operation"	=>	"update",
				"table"	=>	"mail",
				"criteria"	=>	"id=" . $db->record("mail.id"),
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.message.open({text:bento.form.response.message,'class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<ul class="tabs">
    <li class="active">Setup</li>
    <li>From</li>
    <li>Content</li>
    <li>Template</li>
    <li>Code</li>
</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"mail.name",
                        "id"	=>	"name",
                        "required"	=>	"Please enter a name."
                    )
                );?>    

        <label>Slug</label>

        <?php $form->text(
                array(
                    "name"	=>	"mail.slug",
                    "id"	=>	"slug",
                    "readonly"	=>	true
                )
            );?>  
    
        <label>Subject</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"mail.subject",
                        "required"	=>	"Please enter a subject.",
						"validate"	=>	"url"
                    )
                );?>

	</div>

	<div>

        <label>Name</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"mail.from_name",
						"required"	=>	"Please enter a from name"
                    )
                );?>
                
        <label>Email</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"mail.from_email",
						"required"	=>	"Please enter a from email"
                    )
                );?>
                

    </div>

    <div>

		<?php $form->textarea(
                        array(
                            "name"	=>	"content",
                            "html"	=>	false,
                            "class"	=>	"page",	
                            "value"	=>	$file->read( "/mail/" . strtolower( $scms->public->language ) . "/" . $db->record("mail.slug") . ".php" )
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

    <div>
    

<pre>&lt;? $scms->mail(
            array(
                "slug"	=>	"<?php echo $db->record("mail.slug");?>",
                "to"	=>	&lt;email address&gt;,
                "template"	=>	&lt;optional&gt;,
                "from_name"	=>	&lt;optional&gt;,
                "from_email"	=>	&lt;optional&gt;,
                )
            );?&gt;</pre> 
    
    </div>    
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>