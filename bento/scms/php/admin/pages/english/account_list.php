<table class="zebra-striped">
    <thead>
        <tr>
            <th colspan="2">Email</th>
        </tr>
    </thead>
	<tbody>

	<?php foreach( $db->recordset("account") as $account ){
    
        // Filter in the page
        $db->filter("account.id=" . $account["id"] );  ?>
    
            <tr>
                <td>
                
                    <?php echo $account["email"];?>
                
                </td>
                <td class="right buttons">
                
                    <input type="button" class="icon96 button" value="Edit" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"account_edit","id"	=>	$account["id"])));?>">
     
                    <input type="button" class="icon48 button" value="Delete" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"account_delete","id"	=>	$account["id"])));?>">
                
                </td>
              </tr>
    
    <?php } ?>

	</tbody>
</table>

<span class="buttons">

    <!--scms:button:close-->

</span>