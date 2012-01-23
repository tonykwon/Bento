<table class="zebra-striped">
    <thead>
        <tr>
            <th colspan="2">Page Name</th>
        </tr>
    </thead>
<tbody>

<?php foreach( $db->recordset("page") as $page ){

	// Filter in the page
	$db->filter("page.id=" . $page["id"] );
	
	if( !$scms->is_hidden( $page["slug"] ) ){ ?>

        <tr>
            <td>
            
                <?php echo $page["name"];?>
            
            </td>
            <td class="right buttons">
            
                <input type="button" class="icon96 button" value="Edit" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"page_edit","slug"	=>	$page["slug"])));?>">
 
                <input type="button" class="icon48 button" value="Delete" onclick="<?php echo $scms->url(array("slug"	=>	"admin","anchor"	=>	"Edit","variables"	=>	array("page"	=>	"page_delete","slug"	=>	$page["slug"])));?>">
            
            </td>
          </tr>

	<?php }

} ?>

</tbody>
</table>

<span class="buttons">

    <!--scms:button:close-->

</span>