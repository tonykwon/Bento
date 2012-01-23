<?php $form->open(
			array(
				"operation"	=>	"update",
				"table"	=>	"setup",
				"criteria"	=>	"id=" . $db->record("setup.id"),
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.message.open({text:'Complete','class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<ul class="tabs">
    <li class="active">Domains</li>
    <li>Feeds</li>
	<li>Mail</li>
	<li>Theme</li>
</ul>
<div class="tab-content">

    <div class="active">
    
    	<?php foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){ ?>
    
    		<label><?php echo ucwords($mode);?></label>
    
			<?php $form->text(
                        array(
                            "name"	=>	"setup.domain_" . $mode,
                            "required"	=>	"Please enter a " . $mode ." domain."
                        )
                    );?>
    
        <?php } ?>

	</div>

	<div>

        <label>Feed Time</label>
        
        <?php $form->text(
                    array(
                        "name"	=>	"setup.feed_time"
                    )
                );?> milliseconds
  
    </div>
  
    <div>

        <label>From Address</label>
        
		<?php $form->text(
                    array(
                        "name"	=>	"setup.mail_email",
                        "validate"	=>	"email",
                        "required"	=>	"Please enter an email address."
                    )
                );?>

        <label>From Name</label>
        
		<?php $form->text(
                    array(
                        "name"	=>	"setup.mail_from",
                        "required"	=>	"Please enter a from name."
                    )
                );?>
    
    </div>

    <div>

        <label>Theme</label>
        
		<?php $form->select(
                    array(
                        "name"	=>	"setup.theme",
                        "required"	=>	"Please select a theme.",
						"values"	=>	$scms->private->themes,
						"text"	=>	"value",
						"value"	=>	"value",
						"selected"	=>	$db->record("setup.theme")
                    )
                );?>
    
    </div>
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>