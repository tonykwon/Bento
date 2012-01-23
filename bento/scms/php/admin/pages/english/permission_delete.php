<?php $form->open(
			array(
				"operation"	=>	"delete",
				"table"	=>	"permission",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.modal.close({text:'Permission deleted.','class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<?php $form->hidden(
			array(
				"name"	=>	"id",
				"value"	=>	$db->record("permission.id")
			)
		);?>

<ul class="tabs">

    <li class="active">Setup</li>
    <li>Users</li>

</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Permission Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"permission.name",
                        "required"	=>	"Please enter a name.",
						"readonly"	=>	true
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
						"readonly"	=>	true,
						"default"	=>	array(
											"value"	=>	0,
											"text"	=>	"None"
											)
                    )
                );?>

    </div>
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>