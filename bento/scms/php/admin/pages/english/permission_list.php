<table class="zebra-striped">
    <thead>
        <tr>
            <th colspan="2">Permission Name</th>
        </tr>
    </thead>
	<tbody>

	<?php foreach( $db->recordset("permission") as $permission ){
    
        // Filter in the page
        $db->filter("permission.id=" . $permission["id"] ); ?>
    
            <tr>
                <td>
                
                    <?php echo $permission["name"];?>
                
                </td>
                <td class="right buttons">
                
                    <input type="button" class="icon96 button" value="Edit" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"permission_edit","id"	=>	$permission["id"])));?>">
     
                    <input type="button" class="icon48 button" value="Delete" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"permission_delete","id"	=>	$permission["id"])));?>">
                
                </td>
              </tr>
    
    <?php } ?>

	</tbody>
</table>

<span class="buttons">

    <!--scms:button:close-->

</span>