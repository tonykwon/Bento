<?php $form->open(
			array(
				"handler"	=>	"handler->permission_add",
				"operation"	=>	"insert",
				"table"	=>	"permission",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.admin.clear(); bento.message.open({text:bento.form.response.message,'class':'success',clear:true});",
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


</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>Permission Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"permission.name",
                        "required"	=>	"Please enter a name.",
						"class"	=>	"clear"
                    )
                );?>    

        <label>Description</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"permission.description",
					    "required"	=>	"Please enter a description.",
						"class"	=>	"clear"
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
  
</div>
        
<span class="buttons">

    <!--scms:button:close-->

    <!--scms:button:submit-->

</span>

<?php $form->close();?>