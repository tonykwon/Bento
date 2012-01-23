<table class="zebra-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Subject</th>
        </tr>
    </thead>
	<tbody>

	<?php foreach( $db->recordset("mail") as $mail ){
    
        // Filter in the page
        $db->filter("mail.id=" . $mail["id"] );  ?>
    
            <tr>
                <td>
                
                    <?php echo $mail["subject"];?>
                
                </td>
                <td class="right buttons">
                
                    <input type="button" class="icon96 button" value="Edit" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"mail_edit","slug"	=>	$mail["slug"])));?>">
     
                    <input type="button" class="icon48 button" value="Delete" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Delete","variables"	=>	array("page"	=>	"mail_delete","slug"	=>	$mail["slug"])));?>">
                
                </td>
			</tr>
    
    <?php } ?>

	</tbody>
</table>

<span class="buttons">

    <!--scms:button:close-->

</span>