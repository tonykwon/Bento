<?php $form->open(
			array(
				"handler"	=>	"handler->page_add",
				"operation"	=>	"insert",
				"table"	=>	"page",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.admin.clear();bento.message.open({'text':bento.form.response.message,'class':'success'});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<ul class="tabs">

    <li class="active">Setup</li>
    <li>Add.</li>
    <li>SEO</li>
    <li>Permissions</li>
    <li>Templates</li>

	<?php // this is the type of content we're editing
    foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){ ?>

		<li><?php echo ucwords($mode);?></li>

	<?php // if
	} ?>

</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Page Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"page.name",
                        "id"	=>	"name",
                        "required"	=>	"Please enter a page name.",
						"class"	=>	"clear"
                    )
                );?>    

        <label>Slug</label>

        <?php $form->text(
                array(
                    "name"	=>	"page.slug",
                    "id"	=>	"slug",
                    "readonly"	=>	true,
					"class"	=>	"clear"
                )
            );?>  
    
        <label>URL</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"page.url",
						"id"	=>	"url",
                        "required"	=>	"Please enter a page url.",
						"validate"	=>	"url",
						"class"	=>	"clear"
                    )
                );?>
    
        <label>Anchor</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"page.anchor",
						"id"	=>	"anchor",
                        "required"	=>	"Please enter a page anchor.",
						"class"	=>	"clear"
                    )
                );?>

	</div>

    <div>
    
        <label>Modal</label>
        
		<?php $form->select(
                    array(
                        "name"	=>	"page.modal",
                        "values"	=>	array(
											array("value"=>0,"text"=>"No"),
											array("value"=>1,"text"=>"Yes")
											),
                        "value"	=>	"value",
                        "text"	=>	"text"
                    )
                );?>
        
        <label>Modal Width</label>

        <?php $form->text(
                array(
                    "name"	=>	"page.modal_width",
					"value"	=>	"500"
                )
            );?>  
            
        <label>Modal Height</label>

        <?php $form->text(
                array(
                    "name"	=>	"page.modal_height",
					"value"	=>	"500"
                )
            );?>  
            
        <hr >
            
		<label>Feed</label>

		<?php $form->select(
                    array(
                        "name"	=>	"page.feed",
                        "values"	=>	array(
											array("value"=>0,"text"=>"No"),
											array("value"=>1,"text"=>"Yes")
											),
                        "value"	=>	"value",
                        "text"	=>	"text"
                    )
                );?>

		<label>Requires SSL</label>

		<?php $form->select(
                    array(
                        "name"	=>	"page.secure",
                        "values"	=>	array(
											array("value"=>0,"text"=>"No"),
											array("value"=>1,"text"=>"Yes")
											),
                        "value"	=>	"value",
                        "text"	=>	"text"
                    )
                );?>

		<label>Hide in Sitemap</label>

		<?php $form->select(
                    array(
                        "name"	=>	"page.hidden",
                        "values"	=>	array(
											array("value"=>0,"text"=>"No"),
											array("value"=>1,"text"=>"Yes")
											),
                        "value"	=>	"value",
                        "text"	=>	"text"
                    )
                );?>


	</div>

	<div>

        <label>Page Title</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"page.title",
						"class"	=>	"clear"
                    )
                );?>
                
        <label>Page Description</label>
        
        <?php $form->textarea(
                    array(
                        "name"	=>	"page.description",
						"class"	=>	"clear"
                    )
                );?>
                

    </div>

    <div>

		<?php $form->select(
                    array(
                        "name"	=>	"permissions",
                        "values"	=>	$db->recordset("permission"),
                        "selected"	=>	$db->record("page_permission_x.permission_id"),
                        "value"	=>	"id",
                        "text"	=>	"name",
						"multiple"	=>	true,
						"size"	=>	10,
						"default"	=>	array(
											"value"	=>	0,
											"text"	=>	"None"
											)
                    )
                );?>

    </div>
  
    <div class="row">
    
        <span class="span8">
        
            <label>Web</label>
        
            <?php $form->select(
                        array(
                            "name"	=>	"page.template_web",
                            "values"	=>	$scms->private->admin_templates["web"],
                            "selected"	=>	$db->record("page.template_web"),
                            "value"	=>	"value",
                            "text"	=>	"value",
						"selected"	=>	"main"
                        )
                    );?>
        
            <label>Mobile</label>
            
            <?php $form->select(
                        array(
                            "name"	=>	"page.template_mobile",
                            "values"	=>	$scms->private->admin_templates["mobile"],
                            "selected"	=>	$db->record("page.template_mobile"),
                            "value"	=>	"value",
                            "text"	=>	"value",
						"selected"	=>	"main"
                        )
                    );?>
                        
            <label>App</label>
        
            <?php $form->select(
                        array(
                            "name"	=>	"page.template_app",
                            "values"	=>	$scms->private->admin_templates["app"],
                            "selected"	=>	$db->record("page.template_app"),
                            "value"	=>	"value",
                            "text"	=>	"value",
						"selected"	=>	"main"
                        )
                    );?>
    
    </span>
    
	<span class="span8">
    
        <label>Facebook</label>
        
        <?php $form->select(
                    array(
                        "name"	=>	"page.template_facebook",
                        "values"	=>	$scms->private->admin_templates["facebook"],
                        "selected"	=>	$db->record("page.template_facebook"),
                        "value"	=>	"value",
                        "text"	=>	"value",
						"selected"	=>	"main"
                    )
                );?>
    
        <label>Narrowcast</label>
    
        <?php $form->select(
                    array(
                        "name"	=>	"page.template_narrowcast",
                        "values"	=>	$scms->private->admin_templates["narrowcast"],
                        "selected"	=>	$db->record("page.template_narrowcast"),
                        "value"	=>	"value",
                        "text"	=>	"value",
						"selected"	=>	"main"
                    )
                );?>
    
        <label>Kiosk</label>
        
        <?php $form->select(
                    array(
                        "name"	=>	"page.template_kiosk",
                        "values"	=>	$scms->private->admin_templates["kiosk"],
                        "selected"	=>	$db->record("page.template_kiosk"),
                        "value"	=>	"value",
                        "text"	=>	"value",
						"selected"	=>	"main"
                    )
                );?>

		</span>

    </div>
  
	<?php // this is the type of content we're editing
    foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){ ?>
    
    
        <div>
        
            <label><?php echo ucwords($mode);?> Content</label>
        
            <?php $form->textarea(
                            array(
                                "name"	=>	$mode . "_page",
                                "html"	=>	false,
								"class"	=>	"page",
								"id"	=>	$mode,
								"class"	=>	"clear"
                                )
                            );?>
        
        </div>
        
    <?php // if
    } ?>
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>