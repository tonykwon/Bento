<?php		
// Make sure we have the file	
$sql =  $file->read("/bento/language/install/install/sql/language.sql");

// Set the default language 
$_SESSION["bento"]["scms"]["language"] = "english";

// Let's execute the sql
if( $sql && $db->execute( $sql ) ){

	// redirect the site
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: /?' . $_SERVER['QUERY_STRING'] );	

// if
} ?>

<h2>Error!</h2>
			
<p>We were unable to install the sql file on the server. Try manually and then click continue. The SQL file is located at:</p>

<strong>/bento/language/install/install/sql/language.sql</strong>

<form>
	<input type="submit" value="Continue" class="button save">
</form>