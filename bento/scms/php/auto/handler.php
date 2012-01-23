<?php
// Make sure the file exists
if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/handler.php" ) ){ 
	require_once $_SERVER['DOCUMENT_ROOT'] . "/handler.php";
// if
}

// This is for some core functionality
class scms_handler_core extends scms_handler {

	// This will return a lost password
	public function login( $response ){
	
		global $db,$scms,$form;

		// Check if the account exists
		if( $db->recordcount("account") == 0 ){
			
			// Tell the world there was a problem
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"We can't seem to find your email address or password."
								)
							);
		
		// The account is not confirmed					
		} else if( $db->record("account.confirm") == 0 ) {
			
			// Tell the world there was a problem
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"Your account is not yet confirmed. An email was sent to you with confirmation instructions. If you did not recieve it, requesting a password reset will resend it."
								)
							);
		
		// The account is disabled
		} else if( $db->record("account.state") == 0 ) {
			
			// Tell the world there was a problem
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"Your account has been disabled. Please contact the system administrator for details."
								)
							);
							
		// Log it in
		} else {
			
			// Login now, output the response
			$scms->login( true );
			
		// if
		}
	
	// public
	}

	// This will return a lost password
	public function register( $response ){
	
		global $db,$scms,$form;
	
		// Check if we have a email address
		if( $response ){
		
			// This is the confirmation url
			$url = $scms->http() . $scms->domain() . $scms->url(
																	array(
																		"slug"	=>	"confirm",
																		"variables"	=>	array(
																							"email"	=>	$form->post("account.email"),
																							"token"	=>	$form->post("account.token")
																							)
																			)
																		);

			// Create the new user
			$scms->mail(
						array(
							"slug"	=>	"register",
							"to"	=>	$form->post("account.email"),
							"variables"	=>	array(
												"url"	=>	$url
												)
							)
						);
		
			// Here is the response
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"You've been registered. Please check your email for confirmation."
								)
							);
						
		// if
		} else {
		
			// Tell the world there was a problem
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The email address is already registered."
								)
							);
	
		// if
		}
	
	// public
	}

	// This will return a lost password
	public function logout( $response ){
	
		global $scms;
		
		// Logout now, output the response
		$scms->logout( true );
		
	// public
	}

	// This will return a lost password
	public function forgot( $response ){
	
		global $db,$scms,$form;
		
		// Check if we have a email address
		if( $response ){
		
			// Get a token
			$token = $scms->s(time());
		
			// Update the account
			$db->update(
						array(
							"table"	=>	"account",
							"values"	=> array(
												"token"	=>	$token 
											),
							"criteria"	=>	"email='" . $form->post("account.email") ."'"
							)
						);
		
			// This is the confirmation url
			$url = $scms->http() . $scms->is_domain() . $scms->url(
															array(
																"slug"	=>	"reset",
																"variables"	=>	array(
																					"email"	=>	$form->post("account.email"),
																					"token"	=>	$token
																					)
																	)
																);

			// Create the new user
			$scms->mail(
						array(
							"slug"	=>	"forgot",
							"to"	=>	$form->post("account.email"),
							"variables"	=>	array(
												"url"	=>	$url
												)
							)
						);
		
			// Here is the response
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"We've emailed you your password."
								)
							);
						
		// if
		}
		
		// Tell the world there was a problem
		$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"We can't seem to find your email address in the system."
							)
						);
	
	// public
	}

	// This will return a lost password
	public function reset( $response ){
	
		global $db,$scms,$form;
		
		// Get the account information before login
		$db->select(
				array(
					"table"	=>	"account.id=" . $form->post("account.id"),
					"join"	=>	"account_permission_x"	
				)
			);
		
		// Login
		$scms->login();
		
	// public
	}

	// Sends an email from a form
	public function email( $reponse, $form ){
	
		// Set this up for ease of use
		$scms = $this;
	
		// Check if this is a form or not
		if( $form->post("to") && $form->post("subject") ){
		
			// Here you go
			$variables["to"] = $form->post("to");
			$variables["subject"] = $form->post("subject");
			
			// Send the email
			$form->response(
							array(
								"response"	=>	$this->mail($form->post("mail")),
								"variables"	=>	$variables
								)
							);
					
		// if
		} else {
		
			// Kill it
			$form->response(
							array(
									"response"	=>	false,
									"variables"	=>	"To and subject fields must be provided."
									)
								);
		
		// if
		}

	// method
	}

// Admin Pages

	// Adds a new page
	public function page_add( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		// Make sure we can create a new page
		if( $response ){
		
			// Update the page
			$db->update(
					array(
						"table"	=>	"page",
						"values"	=>	 array(
											"slug"	=>	$form->post("page.slug")
											)
										)
									);
			
			// Check it
			if( $form->post("permissions") && is_array( $form->post("permissions") ) ){
			
				// Assign the new ones
				foreach( $form->post("permissions") as $permission ){
				
					// Insert the permission
					$db->insert(
								array(
									"tables"	=>	"page_permission_x",
									"values"	=>	array(
														"page_id"	=>	$response,
														"permission_id"	=>	$permission	
														)
													)
												);
				
				// foreach
				}
				
			// if
			}
			
			// this is the type of content we're editing
			foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){
			
				// Check if we have content
				if( trim($form->post($mode . "_page")) != "" ){
				
					// Write the file
					$file->write( "pages/" . $mode . "/" . strtolower( $scms->public->language ) . "/" . $form->post("page.slug") . ".php", $form->post($mode . "_page") );
			
				// if
				}
			
			// foreach
			}	
			
			// Tell it it's all good.
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"Page created successfully."
								)
							);
			
		// Check it out
		} else {
		
			// Response to javascript
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The URL or name enter is a duplicate."
								)
							);
		
		// form
		}
	
	// method
	} 

	// Edits a page
	public function page_edit( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		if( $response ){
		
			// Delete the old permissions
			$db->delete(
					array(
						"table"	=>	"page_permission_x",
						"criteria"	=>	array(
											"page_id"	=>	$form->post("id") 
											) 
										)	
									);
			
			// Check it
			if( $form->post("permissions") && is_array( $form->post("permissions") ) ){
			
				// Assign the new ones
				foreach( $form->post("permissions") as $permission ){
				
					// Insert the permission
					$db->insert(
								array(
									"table"	=>	"page_permission_x",
									"values"	=>	array(
													"page_id"	=>	$form->post("id"),
													"permission_id"	=>	$permission	
												)
											)
										);
				
				// foreach
				}
				
			// if
			}
			
			// this is the type of content we're editing
			foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){
			
				// Check if we have content
				if( $form->post($mode . "_page") != "" ){
				
					// Write the file
					$file->write( "pages/" . $mode . "/" . strtolower( $scms->public->language ) . "/" . $form->post("slug") . ".php", $form->post($mode . "_page") );
			
				// if
				} else {
				
					@unlink( $_SERVER['DOCUMENT_ROOT'] . "/pages/" . $mode . "/" . strtolower( $scms->public->language ) . "/" . $form->post("slug") . ".php", $form->post($mode . "_page") );
				
				// if
				}
			
			// foreach
			}	
			
			// Tell it it's all good.
			$form->response( true );
			
		// Check it out
		} else {
		
			$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"The URL or name enter is a duplicate."
							)
						);
		
		// form
		}
	
	// method
	} 

	// Deletes a page
	public function page_delete( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		// Make sure we can delete
		if( $response ){
		
			// this is the type of content we're editing
			foreach( array("web","mobile","app","facebook","kiosk","narrowcast") as $mode ){
			
				// Check if we have content
				@unlink( $_SERVER['DOCUMENT_ROOT'] . "/pages/" . $mode . "/" . strtolower( $scms->public->language ) . "/" . $form->post("slug") . ".php" );
		
			// foreach
			}	
			
			// Tell it it's all good.
			$form->response( true );
			
		// Check it out
		} else {
		
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"Unable to delete the pages."
								)
							);
		
		// form
		}
	
	// method
	} 

// Admin Email

	// Adds a new page
	public function mail_add( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		// Make sure we can create a new page
		if( $response ){
			
			// Write the file
			$file->write( "/mail/" . strtolower( $scms->public->language ) . "/" . $form->post("mail.slug") . ".php", $form->post("content") );
		
			// Tell it it's all good.
			$form->response(
							array(
									"response"	=>	true,
									"message"	=>	"Mail added."
								)
							);
			
		// Check it out
		} else {
		
			// Respond to js
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The slug or name entered is a duplicate."
								)
							);
		
		// form
		}
	
	// method
	} 
	
	// Adds a new page
	public function mail_edit( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		// Make sure we can create a new page
		if( $response ){
			
			// Write the file
			$file->write( "/mail/" . strtolower( $scms->public->language ) . "/" . $form->post("mail.slug") . ".php", $form->post("content") );
		
			// Tell it it's all good.
			$form->response(
							array(
									"response"	=>	true,
									"message"	=>	"Mail updated."
								)
							);
			
		// Check it out
		} else {
		
			// Respond to js
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The slug or name entered is a duplicate."
								)
							);
		
		// form
		}
	
	// method
	} 
	
	// Adds a new page
	public function mail_delete( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
			
		// Write the file
		unlink( $_SERVER['DOCUMENT_ROOT'] . "/mail/" . strtolower( $scms->public->language ) . "/" . $form->post("mail.slug") . ".php" );
	
		// Tell it it's all good.
		$form->response(
						array(
								"response"	=>	true,
								"message"	=>	"Mail deleted."
							)
						);
	
	// method
	} 

// Permissions

	// Add permissions
	public function permission_add( $response ){
	
		global $form,$db,$scms;
	
		// Check it out
		if( $response ){
	
			// Check it
			if( $form->post("accounts") && is_array( $form->post("accounts") ) ){
			
				// Assign the new ones
				foreach( $form->post("accounts") as $account ){
				
					// Insert the permission
					$db->insert(
								array(
									"table"	=>	"account_permission_x",
									"values"	=>	array(
													"permission_id"	=>	$response,
													"account_id"	=>	$account	
												)
											)
										);
				
				// foreach
				}
				
			// if
			}
						
			// get it
			$form->response(
						array(
							"response"	=>	true,
							"message"	=>	"Permission added."
							)
						);
			
		// if
		}
	
		// Out
		$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"Please use a unique permission name."
							)
						);
	
	// method
	}
	
	// Edit permissions
	public function permission_edit( $response ){
	
		global $form,$db,$scms;
	
		// Check it out
		if( $response ){
		
			// Get rid of the old associations
			$db->delete(
					array(
						"table"	=>	"account_permission_x",
						"criteria"	=>	array(
											"permission_id"	=>	$form->post("permission.id")
											)
										)	
									);
	
			// Check it
			if( $form->post("accounts") && is_array( $form->post("accounts") ) ){
			
				// Assign the new ones
				foreach( $form->post("accounts") as $account ){
				
					// Insert the permission
					$db->insert(
								array(
									"table"	=>	"account_permission_x",
									"values"	=> array(
														"permission_id"	=>	$response,
														"account_id"	=>	$account	
												)
											)
										);
				
				// foreach
				}
				
			// if
			}
						
			// get it
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"Permission updated."
								)
							);
			
		// if
		}
	
		// Out
		$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"Please use a unique permission name."
							)
						);
	
	// method
	}

// Accounts

	// Edits an account
	public function account_add( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		if( $response ){
		
			// Delete the old permissions
			$db->delete(
					array(
						"table"	=>	"account_permission_x",
						"criteria"	=>	array(
											"account_id"	=>	$response 
											) 
										)
									);
			
			// Check it
			if( $form->post("permissions") && is_array( $form->post("permissions") ) ){
			
				// Assign the new ones
				foreach( $form->post("permissions") as $permission ){
				
					// Insert the permission
					$db->insert(
								array(
									"table"	=>	"account_permission_x",
									"values"	=> array(
													"account_id"	=>	$response,
													"permission_id"	=>	$permission	
												)
											)
										);
				
				// foreach
				}
				
			// if
			}
			
			// Tell it it's all good.
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"Account updated."
								)
							);
			
		// Check it out
		} else {
		
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The URL or name enter is a duplicate."
								)
							);
		
		// form
		}
	
	// method
	}

	// Edits an account
	public function account_edit( $response ){
	
		// Load it up
		global $form,$db,$file,$scms;
		
		if( $response ){
				
			// Insert the permission
			$scms->set_permissions(
								 array(
										"account_id"	=>	$form->post("account.id"),
										"permissions"	=>	$form->post("permissions")
									)
								);
			
			// Tell it it's all good.
			$form->response(
							array(
								"response"	=>	true,
								"message"	=>	"Account updated."
								)
							);
			
		// Check it out
		} else {
		
			$form->response(
							array(
								"response"	=>	false,
								"message"	=>	"The URL or name enter is a duplicate."
								)
							);
		
		// form
		}
	
	// method
	} 

	/*
	@method: notifications( $feed, $method=NULL )
	@description: Update notifications
	@params:
	@shortcode:  
	@return:
	*/
	public function notifications( $response ){
		
		global $scms,$form;
	
		// Un notify the client
		$scms->unnotify();
		
		// Fire any events
		$scms->fire_events();
	
		// Respond this
		$form->response(
					array(
						"response"	=>	true,
						"message"	=>	"Notifications updated."				
					)
				);
		
	// method
	}

// method
} 

// Here are some core handling files
$GLOBALS["handler"] = new scms_handler_core(); 
//?>