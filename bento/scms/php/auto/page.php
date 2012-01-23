<?php
// Make sure the file exists
if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/page.php" ) ){ 
	require_once $_SERVER['DOCUMENT_ROOT'] . "/page.php";
// if
}

// This is for some core functionality
class scms_page_core {

	// This will return a lost password
	public function search(){
	
		global $db,$scms,$form;
		
		// Make sure there's something to work with here
		if( isset($_GET["search"]) ){
			
			// Loop through it
			foreach( $scms->private->content as $table => $fields ){
			
				if( !isset($_GET["table"]) || (isset($_GET["table"]) && $_GET["table"] == $table) ){
				
					// Get the this out
					$sql = str_replace(" or"," like '%" . $_GET["search"] . "%' or", $table . "." . implode(" or " . $table . ".",$fields)) . " like '%" . $_GET["search"] . "%'";
				
					// Get the records we're looking for
					$db->select( $sql );
			
				// if
				}
			
			// foreach
			}
	
		// Otherwis
		}
	
	// public
	}

	// Logging in requires different actions between the web and the mobile apps and the web db enabled.
	public function login(){
	
		global $bento,$db,$scms;
		
		// Add the js
		$bento->add_js(
					array(
						"plugin"	=>	"scms",
						"name"	=>	"login"
					)
				);
				
		// Check if we're in mobile
		if( $scms->is_mode("mobile") ){
		
			// Add the js
			$bento->add_css(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"login"
						)
					);		
			
		// if
		}
		
		// Turn it on
		$db->webdb(true);
		
	// method
	}

	// Forgot your passwords out requires different actions between the web and the mobile apps and the web db enabled.
	public function forgot(){
	
		global $bento,$db,$scms;
		
		// Add the js
		$bento->add_js(
					array(
						"plugin"	=>	"scms",
						"name"	=>	"forgot"
					)
				);
		
		// Check if we're in mobile
		if( $scms->is_mode("mobile") ){
		
			// Add the js
			$bento->add_css(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"forgot"
						)
					);		
			
		// if
		}
		
		// Turn it on
		$db->webdb(true);
		
	// method
	}

	// Logging out requires different actions between the web and the mobile apps and the web db enabled
	public function register(){
	
		global $bento,$db,$scms;
		
		// Add the js
		$bento->add_js(
					array(
						"plugin"	=>	"scms",
						"name"	=>	"register"
					)
				);
				
		// Check if we're in mobile
		if( $scms->is_mode("mobile") ){
		
			// Add the js
			$bento->add_css(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"register"
						)
					);		
			
		// if
		}
		
		// Turn it on
		$db->webdb(true);
		
	// method
	}

	// Logging out requires different actions between the web and the mobile apps
	public function logout(){
	
		global $bento,$db,$scms;
		
		// Add the js
		$bento->add_js(
					array(
						"plugin"	=>	"scms",
						"name"	=>	"logout"
					)
				);

		// Check if we're in mobile
		if( $scms->is_mode("mobile") ){
		
			// Add the js
			$bento->add_css(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"logout"
						)
					);		
			
		// if
		}
		
	// method
	}

	// This will return a lost password
	public function reset(){
	
		global $scms,$db,$scms;
		
		// Make sure there's something to work with here
		if( !$scms->q("email") || !$scms->q("token") || !$db->select("account.email=" . $scms->q("email") . " and token=" . $scms->q("token") ) ){
	
			// There's an error
			$scms->error(403);

		// if		
		}
		
		// Check if we're in mobile
		if( $scms->is_mode("mobile") ){
		
			// Add the js
			$bento->add_css(
						array(
							"plugin"	=>	"scms",
							"name"	=>	"reset"
						)
					);		
			
		// if
		}
	
	// public
	}
	
	// This will return a lost password
	public function confirm(){
	
		global $scms,$db,$scms;
		
		// Make sure there's something to work with here
		if( !$scms->q("email") || !$scms->q("token") || !$db->select(
																	array(
																		"table"	=>	"account.email=" . $scms->q("email") . " and account.token=" . $scms->q("token"),
																		"join"	=>	"account_permission_x" 
																		) 
																	)
																){
	
			// There's an error
			$scms->error(403);
	
		// Update the account as confirmed
		} else {
		
			// Update it
			$db->update(
						array(
							"table"	=>	"account",
							"values"	=>	array(
												"confirm"	=>	1,
												"token"	=>	""
											),
							"criteria" => $db->record("account.id")
								)
							);
							
			// Let's be sure to login
			$scms->login(false);
							
			// Redirect to account
			$scms->redirect(301, $scms->remembered() );
		
		// if
		}
	
	// public
	}

	// This will return a lost password
	public function account(){
	
		global $db,$scms,$form;
		
		// Make sure there's something to work with here
		if( $scms->logged_in() ){
			
			// Here is the search results
			$db->select("account.id=" . $scms->account_id() );
	
		// Otherwis
		} else {
		
			// Redirect to the home page
			$scms->redirect(301,"/");
		
		// if
		}
	
	// public
	}

// Admin Pages
	
	// This will handle the stuff for editing of a page
	public function page_list(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select("page.id>0 order by page.name asc");
			
	// public
	}

	// This will handle the stuff for editing of a page
	public function page_add(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select("permission.id>0 order by permission.name asc");
			
	// public
	}
		
	// This will handle the stuff for editing of a page
	public function page_edit(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select(
				array(
					"table"	=>	"page.slug=" . $scms->q("slug"),
					"join"	=>	"page_permission_x"
					)
				);
		
		// Check it out
		$db->select("permission");		
		
		// this is the type of content we're editing
		foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){
		
			// Get the different modes
			$scms->private->admin_content[ $mode ] = "/page/" . $mode . "/" . strtolower( $scms->public->language ) . "/" . $scms->q("slug") . ".php";
		
		// foreach
		}	
	
	// public
	}

	// This will handle the stuff for editing of a page
	public function page_delete(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select("page.slug=" . $scms->q("slug") );
			
	// public
	}

// Admin Users

	// List all the users in the system
	public function account_list(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select("account.id>0 order by account.email asc");
			
	// public
	}
	
	// List all the users in the system
	public function account_add(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select("permission.id>0 order by permission.name asc");
			
	// public
	}
	
	// Edits an existing account
	public function account_edit(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select(
				array(
					"table"	=>	"account.id=" . $scms->q("id"),
					"join"	=>	"account_permission_x"
					)
				);
			
		// Select them
		$db->select("permission");
			
	// public
	}

	// Edits an existing account
	public function account_delete(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select("account.id=" . $scms->q("id") );
			
	// public
	}
	
// Admin Users
	
	// List all the mail in the system
	public function mail_list(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select(
					array(
						"table"	=>	"mail.id>0 order by mail.subject asc",
						"join"	=>	"page_permission_x"
						)
					);

	// public
	}

	// Adds mail to the system
	public function mail_add(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select("setup");

	// public
	}

	// This will edit an email
	public function mail_edit(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select("mail.slug=" . $scms->q("slug") );
	
	// public
	}

	// Let's delete this mail
	public function mail_delete(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select("mail.slug=" . $scms->q("slug") );
	
	// public
	}

// Admin Permissions

	// This will handle the stuff for editing of a page
	public function permission_list(){
	
		global $db;
		
		// Make sure there's something to work with here
		$db->select("permission.id>0 order by permission.name asc");
			
	// public
	}
	
	// This will handle the stuff for editing of a page
	public function permission_add(){
	
		global $db;
		
		// Get all the users
		$db->select("account");
			
	// public
	}

	// Editing of permissions
	public function permission_edit(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select(
				array(
					"table"	=>	"permission.id=" . $scms->q("id"),
					"join"	=>	"account_permission_x"
					)
				);
		
		// Add the account
		$db->select("account");
	
	// public
	}
	
	// Deleting of permissions
	public function permission_delete(){
	
		global $db,$scms;
		
		// Make sure there's something to work with here
		$db->select(
					array(
						"table"	=>	"permission.id=" . $scms->q("id"),
						"join"	=>	"account_permission_x"
						)
					);
		
		// Add the account
		$db->select("account");
	
	// public
	}

	// Admin for setup
	public function setup(){
	
		global $db;
		
		// Get the records 
		$db->select("setup.id=1");
	
	// method
	}

// method
} 

// Here are some core handling files
$GLOBALS["scms_page_core"] = new scms_page_core();
?>