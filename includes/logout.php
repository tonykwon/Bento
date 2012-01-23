<?php $form->open(
			array(
				"handler"	=>	"handler->logout",
				"javascript"	=>	array(
										"onsuccess"	=>	"bento.scms.logout();"
										)
			)
		);?> 

<h4>Are you sure you want to logout?</h4>

<div class="buttons">

    <!--scms:button:no-->

    <!--scms:button:yes-->

</div>

<?php $form->close();?>

