<?php $form->open(
			array(
				"handler"	=>	"handler->account_add",
				"operation"	=>	"insert",
				"table"	=>	"account",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.admin.clear();bento.message.open({text:bento.form.response.message,'class':'success',clear:true});",
										"onfail"	=>	"bento.message.open({text:bento.form.response.message,'class':'error',clear:true});"
										)
			)
		);?>

<ul class="tabs">

    <li class="active">Setup</li>
    <li>Permissions</li>

</ul>
<div class="tab-content">
    
    <div class="active">
    
        <label>First Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"account.name_first"
                    )
                );?>    

        <label>Last Name</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"account.name_last"
                    )
                );?>     

        <label>Email Address</label>
    
        <?php $form->text(
                    array(
                        "name"	=>	"account.email"
                    )
                );?>   

	</div>

    <div>

		<?php $form->select(
                    array(
                        "name"	=>	"permissions",
                        "values"	=>	$db->recordset("permission"),
                        "value"	=>	"id",
                        "text"	=>	"name",
						"multiple"	=>	true,
						"size"	=>	10,
						"selected"	=>	$db->recordset("account_permission_x.permission_id"),
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