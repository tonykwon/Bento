<?php $form->open(
			array(
				"handler"	=>	"handler->permission_edit",
				"operation"	=>	"update",
				"table"	=>	"permission",
				"criteria"	=>	"id=" . $db->record("permission.id"),
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.message.open({text:bento.form.response.message,'class':'success',clear:true});",
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
    <li>Users</li>
    <li>Code</li>

</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Permission Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"permission.name",
                        "required"	=>	"Please enter a name."
                    )
                );?>    

        <label>Description</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"permission.description",
					    "required"	=>	"Please enter a description."
                    )
                );?>    


	</div>

    <div>

		<?php $form->select(
                    array(
                        "name"	=>	"accounts",
                        "values"	=>	$db->recordset("account"),
                        "value"	=>	"id",
                        "text"	=>	"email",
						"multiple"	=>	true,
						"size"	=>	10,
						"default"	=>	array(
											"value"	=>	0,
											"text"	=>	"None"
											)
                    )
                );?>

    </div>
    
    <div>
    
<pre>
&lt;? if( $scms->authenticated(<?php echo $db->record("permission.id");?>) ){ ?&gt;
    Do stuff
&lt;? } ?&gt;
</pre>
    
    </div>
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>