<?php $form->open(
			array(
				"handler"	=>	"handler->page_edit",
				"operation"	=>	"update",
				"table"	=>	"page",
				"criteria"	=>	"id=" . $db->record("page.id"),
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.message.open({text:'Complete','class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"id",
				"value"	=>	$db->record("page.id")
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"slug",
				"value"	=>	$db->record("page.slug")
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
    
    <li>Code</li>

</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Page Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"page.name",
                        "id"	=>	"name",
                        "required"	=>	"Please enter a page name."
                    )
                );?>    

        <label>Slug</label>

        <?php $form->text(
                array(
                    "name"	=>	"slug",
                    "id"	=>	"slug",
                    "readonly"	=>	true,
                    "value"	=>	$db->record("page.slug")
                )
            );?>  
    
        <label>URL</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"page.url",
                    	"id"	=>	"url",
                        "required"	=>	"Please enter a page url.",
						"validate"	=>	"url"
                    )
                );?>
    
        <label>Anchor</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"page.anchor",
                    	"id"	=>	"anchor",
                        "required"	=>	"Please enter a page anchor."
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
                    "name"	=>	"page.modal_width"
                )
            );?>  
            
        <label>Modal Height</label>

        <?php $form->text(
                array(
                    "name"	=>	"page.modal_height"
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
                        "name"	=>	"page.title"
                    )
                );?>
                
        <label>Page Description</label>
        
        <?php $form->textarea(
                    array(
                        "name"	=>	"page.description"
                    )
                );?>
  
    </div>

    <div>

		<?php $db->order("permission.name","asc");?>

		<?php $form->select(
                    array(
                        "name"	=>	"permissions",
                        "values"	=>	$db->recordset("permission"),
                        "selected"	=>	$db->recordset("page_permission_x.permission_id"),
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
                            "text"	=>	"value"
                        )
                    );?>
        
            <label>Mobile</label>
            
            <?php $form->select(
                        array(
                            "name"	=>	"page.template_mobile",
                            "values"	=>	$scms->private->admin_templates["mobile"],
                            "selected"	=>	$db->record("page.template_mobile"),
                            "value"	=>	"value",
                            "text"	=>	"value"
                        )
                    );?>
                        
            <label>App</label>
        
            <?php $form->select(
                        array(
                            "name"	=>	"page.template_app",
                            "values"	=>	$scms->private->admin_templates["app"],
                            "selected"	=>	$db->record("page.template_app"),
                            "value"	=>	"value",
                            "text"	=>	"value"
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
                        "text"	=>	"value"
                    )
                );?>
    
        <label>Narrowcast</label>
    
        <?php $form->select(
                    array(
                        "name"	=>	"page.template_narrowcast",
                        "values"	=>	$scms->private->admin_templates["narrowcast"],
                        "selected"	=>	$db->record("page.template_narrowcast"),
                        "value"	=>	"value",
                        "text"	=>	"value"
                    )
                );?>
    
        <label>Kiosk</label>
        
        <?php $form->select(
                    array(
                        "name"	=>	"page.template_kiosk",
                        "values"	=>	$scms->private->admin_templates["kiosk"],
                        "selected"	=>	$db->record("page.template_kiosk"),
                        "value"	=>	"value",
                        "text"	=>	"value"
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
                                "value"	=>	$file->read( $scms->private->admin_content[ $mode ] )
                                )
                            );?>
        
        </div>
        
    <?php // if
    } ?>
  
    <div>
    
<label>Content</label>
    
<pre>
&lt;? $scms->page();?&gt;
</pre>

<hr class="space">

<pre>
&lt;--scms:page--&gt;
</pre>

<label>URLs, Links, &amp; Buttons</label>

<pre>
&lt;--scms:link:<?php echo $db->record("page.slug");?>--&gt;
</pre>

<pre>
&lt;--scms:url:<?php echo $db->record("page.slug");?>--&gt;
</pre>

<pre>
&lt;--scms:button:<?php echo $db->record("page.slug");?>--&gt;
</pre>
    
    </div>
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>