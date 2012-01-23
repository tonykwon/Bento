<?php		
// Make sure we have the file	
$config = array(
		"state"	=>	"installed",
		"private" => array(
						"key"	=> time()
						)
					);

// Now configure it
$bento->configure( $encryption->config, $config );
?>