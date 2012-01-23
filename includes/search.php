<?php $form->open(
				array(
					"action"	=>	$scms->url("search"),
					"ajax"	=>	false,
					"method"	=>	"get"
				)
			);?>

<?php $form->text(
				array(
					"encode"	=>	false,
					"name"	=> "search",
					"placeholder"	=>	isset( $_GET["search"] ) ? $_GET["search"] : "Search"
				)
			);?>

<input type="submit" class="button blue inside" value="Search">

<?php $form->close();?>