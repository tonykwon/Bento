<?php
/*
@class: handler
@description: handles form data from the form object (like a controller)
@params:
*/
class handler {

	// Load the variables 
	public $public;
	public $private;
	
	/*
	@method: submit()
	@description: Load the handling class for pushes and pulls of data, uploading, and autocompleting
	@params:
	@shortcode:  
	@return:
	*/ 
	public function submit() {
		
		global $db,$form;
		
		// Check if we're actually submitting stuff
		if( $form->submitting() ){
			
			// Check what type of ajax submission it is
			if( $form->is_push() ){
				
				// On pushes, we're not writing any additional data
				session_write_close();
				
				// Slow it down
				$this->push();
				
			// if
			} else {
			
				// Just pull the response
				$this->pull();
				
			// if
			}

		// if
		}
		
	// method	
	} 

	/*
	@method: push()
	@description: Checks for a push operation
	@params:
	@shortcode:  
	@return:
	*/ 
	public function push( $count=0 ){
		
		global $form,$bento;
	
		// Check it out
		if( $form->has_event() || $count>=$form->private->comet->refresh ){
			
			// Set the session again
			session_id( $_GET[ $bento->private->session_id ] );
			session_start();
	
			// Otherwise, use the pull to respond
			$this->pull();
		
		// We're at the end
		} else {

			// Sleep for a second
			sleep( $form->private->comet->iterate );	

			// Add this up a bit
			$count++;
			
			// Push it again
			$this->push( $count );
		
		// if
		}
		
	// method
	}
	
	/*
	@method: pull()
	@description: Pulls the handle (when push is called, or not)
	@params:
	@shortcode:  
	@return:
	*/ 
	public function pull(){
		
		global $form;
		
		// Check if we're doing a plaintext
		if( $_POST[ $form->public->type ] == $form->public->plaintext ){ $this->plaintext(); }
		
		// Check if we're doing a file upload
		if( $_POST[ $form->public->type ] == $form->public->upload ){ $this->upload(); }
	
		// Check if we're doing an autocomplete
		if( $_POST[ $form->public->type ] == $form->public->autocomplete ){ $this->autocomplete(); }

	// form
	}

	/*
	@method: text()
	@description: Is a third party uploader. swfupload.
	@params:
	@shortcode:  
	@return:
	*/ 
	public function plaintext(){	
	
		global $form,$db;

		// If we've got post data, let's use it
		if( isset($_POST) && is_array($_POST) && count($_POST>0) && isset($_POST[ $form->public->token_name ]) ){
		
			// Let's check our referer and the token generated
			if( 
				( isset($_POST[ $form->public->token_name ]) ) && 
				( isset( $form->private->token_value ) ) /* && 
				( $form->decrypt($_POST[ $form->public->token_name ]) == $form->private->token_value )*/
				){
	
				// Check if there's a database operation to fulfil
				if( isset($_POST[ $form->public->operation ]) ){
					
					// Get the opersation
					$operation = $_POST[ $form->public->operation ];
					
					// decrypt it - decipher it
					$operation = trim($form->decrypt($operation,true));
					
					// Get the opersation
					$table = $_POST[ $form->public->table ];
					
					// decrypt it - decipher it
					$table = trim($form->decrypt($table,true));

					// Make sure this is set
					if( isset($_POST[ $form->public->criteria ]) ){

						// decrypt it - decipher it
						$criteria = $table . trim($form->decrypt($_POST[ $form->public->criteria ],true));
					
					// if
					}
					
					// check if we're doing a joined select
					if( stristr($table,",") ){
					
						$joins = explode(",",$table);
						$table = $joins[0];
						unset($joins[0]);
						$joins = implode(",",$joins);
					
					// Nothing
					} else {
					
						// Join it together
						$joins = "";
					
					// if
					}
		
					// We don't need criteria for insert statements
					if(	isset($to[2]) ){
						
						// Turn it up
						$criteria = trim($to[1] . "." . $to[2]);
						
					// if	
					}
		
					// Check that we need to do something with the database
					if( class_exists("db") && isset($db->private->tables[ $table ]["schema"]["field"]) ){
			
						// Get a field listing
						foreach( $db->private->tables[ $table ]["schema"]["field"] as $tmp_table => $junk){
			
							$field_list[] = $tmp_table;
			
						// foreach
						}
			
						$tmp_array = array();
			
						//Loop though the forms
						foreach( $_POST as $field => $value ){
			
							$field = $form->decrypt($field);
							
							// Check it
							if( !is_array($value) ){
								$value = stripslashes($value);
							}
			
							// Check if this is a table field
							if( strstr($field, ".") ){
								$tmp_table = explode(".", $field);
							}
		
							if( (isset($tmp_table)) && (is_array($tmp_table)) && ($tmp_table[0] == $table) && (in_array( trim($tmp_table[1]),$field_list )) ){
								$tmp_array[ trim(str_replace($table . ".","",$field)) ] = $value;
								}
			
							// Unset the array for security
							unset($tmp_table);
			
						// foreach
						}
						
						// Security measure. There must be one field to complete an operation
						if( count($tmp_array) > 0 ){
						
							// See what's what's being output
							if( isset($_POST[ $form->public->debug ]) && $_POST[ $form->public->debug ] == "true" ){
						
								// Set that sql is to be output
								$db->output();
								
								// Buffer the output
								ob_start();
								
							// if
							}
							
							// This is for the response
							$variables = array();
			
							// Check if we're doing and insert query
							if( strtolower($operation) == "select" ){
			
								$tmp_criteria = "";
								$tmp_tables = array();
									
								// Loop through the images
								foreach( $tmp_array as $tmp_field => $tmp_value ){
			
									$tmp_criteria .= $table . "." . $tmp_field  . "=" . $tmp_value . " and ";
									$tmp_tables[] = $table;
			
								// foreach
								}
			
								$tmp_criteria = substr($tmp_criteria,0,-4);
			
								//Send information to the insert function which inserts into table
								$response = $db->select( 
															array(
																"table"	=>	$tmp_criteria,
																"join"	=>	$joins														
																)
															);
								
								// Now get the variables
								foreach( $tmp_tables as $table ){
								
									$variables[ $table ] = $db->recordset( $table );
								
								// foreach
								}
			
							// if
							}
			
							// Check if we're doing and insert query
							if( strtolower($operation) == "insert" ){
			
								// Send information to the insert function which inserts into table
								$response = $db->insert( 
														array(	
															"table"	=>	$table,
															"values"	=>	$tmp_array 
															)
														);
													
								// Variables to return to js
								$variables = array("id"	=>	$response );
								
								// Make sure it went well
								if( $response ){
								
									// Get the records to play with
									$db->select(
												array(
													"table" => $table . ".id=" . $response
													)
												);
								
								// if
								}
		
							// if	
							}
			
							// Check if we're doing an update query
							if( strtolower($operation) == "update" ){
			
								// Send information to the insert table
								$response = $db->update( 
														array(
															"table"	=>	$table,
															"values"	=>	$tmp_array,
															"criteria"	=>	$criteria 
														)
													);
													
								// Something to return
								$variables = array();
								
								// Make sure it went well
								if( $response ){
								
									// Get the records to play with
									$db->select(
												array(
													"table" => $criteria
													)
												);
								
								// if
								}
			
							// if
							}
			
							// Check if we're removing a record
							if( strtolower($operation) == "delete"){
			
								// Send information to the delete function which removes from the table
								$response = $db->delete(
													array(
														"table"	=> $table,
														"criteria"	=>	$tmp_array 
														)
													);
													
								// Return
								$variables = array();
			
							// if
							}
							
							// See what's what's being output
							if( isset($_POST[ $form->public->debug ]) && $_POST[ $form->public->debug ] == "true" ){
		
								// Get the contents of the buffer
								$tmp_contents = ob_get_contents();
								
								// turn off the buffer
								ob_clean();
						
								// Set that sql is to be output
								$db->output();
							
								// Output the response, kill the process
								$form->response(
												array(
													"response"	=>	(bool)$response,
													"message"	=>	$tmp_contents,
													"variables"	=>	$variables
													)
												);
								
							// if
							}				
			
							// If there's an error with the operation return false
							if( !isset( $response ) ){ $response = false; }
			
							// Check if there's a handler
							$form->response( 
											array(
												"response"	=>	$form->handler( $response ), 
												"message"	=>	"Database operation.",
												"variables"	=>	$variables
												)
											);
			
						// if
						}
			
						// Unset the array for security
						unset($tmp_array);
	
					// if
					}
							
				// Jut a handler to use
				} else {
				
					// Check if there's a handler
					$form->response( 
									array(
										"response"	=>	$form->handler( true ), 
										"message"	=>	"No database operation."
										)
									);
							
				// if
				}
				
			//if
			} else {
			
				// Different rules for uploads
				if( !isset($_GET[ $form->public->upload ] ) ){
				
					// See what's what's being output
					if( isset($_POST[ $form->public->debug ]) && $_POST[ $form->public->debug ] == "true" ){
					
						// Return it
						echo json_encode( 
									array(
										"response"	=>	"false",
										"message"	=>	"Unauthorized post.",
										"variables"	=>	array(
															"A token is posted"	=>	isset($_POST[ $form->public->token_name ]),
															"A token is sessioned"	=>	isset( $form->private->token_value ),
															"Tokens match"	=> ( $form->decrypt($_POST[ $form->public->token_name ]) == $form->private->token_value )
															)													
															
										) 
									);
				
					// Don't output any errors
					} else {
		
						echo json_encode( 
										array(
											"response"	=>	"false",
											"message"	=>	"Unauthorized post."
											)
										);
			
					// if
					}
					
				// if
				}
		
			// if
			}
	
		// if
		}
			
		// No need to do any more
		die();

	// method
	}

	/*
	@method: upload()
	@description: Is a third party uploader. swfupload.
	@params:
	@shortcode:  
	@return:
	*/ 
	public function upload(){	
	
		global $form;

		// Get the name of the object
		if( !isset($_POST["name"]) ){
		
			// Break the file into file name and type to rename with timestamp
			$tmp = explode(".",$_POST["Filename"]);
		
			// Add the media with a timestamp for the filename
			$tmp_name = str_replace(".","",microtime(true)) . "." . strtolower($tmp[1]);
			
		} else {
		
			$tmp_name = preg_replace("/[^a-zA-Z0-9\s-_]/", "", $_POST["name"]) . "." . strtolower($tmp[1]);
			
		// if
		}

		// Make sure there's a directory to upload
		if( !isset($_POST["directory"]) ){
		
			$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"Could not upload. No directory specified."
							)
						);
		
		// if
		}

		// First check if the upload was a success	
		if ( !move_uploaded_file($_FILES["Filedata"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $_POST["directory"] . $tmp_name )) {
		
			$form->response(
						array(
							"response"	=>	false,
							"message"	=>	"Could not save file because it's too large or unsupported.", 
							"variables"	=>	array( 
												$_FILES["Filedata"], 
												$_SERVER['DOCUMENT_ROOT'] . $_POST["directory"] . $_FILES["Filedata"]["name"] 
												)  
											)	
										);
		
		// The file was uploaded, now do the rest
		} else {
			
			// Let's check to see what our handler is
			if( isset($_POST[ $form->public->handler ]) ){

				// Check for a handler
				$form->handler( $tmp_name );

			// Everything went as planned
			} else {			
			
				$form->response("true","Complete.");
				
			// if
			}

		// if
		}	
		
		die();
		
	// method
	}

	/*
	@method: autocomplete()
	@description: Returns a reponse from the autcompleter with a json response.
	@params:
	@shortcode:  
	@return:
	*/ 
	private function autocomplete(){
	
		global $db,$form;
		
		// Somewhere to store the sql
		$tmp_sql = "";
		
		// If
		if( isset( $_POST[ $form->public->handler ] ) ){
				
			// Remove brackets if need be STRIP_TAGS() COULD WorK?
			$tmp_handler = str_replace( "(","", $_POST[ $form->public->handler ] );
			$tmp_handler = str_replace( ")","",$tmp_handler );
			$tmp_handler = str_replace( ";","",$tmp_handler );
		
			// Check if method or function
			if( strstr($tmp_handler,">") ){
			
				$tmp_array = explode("->",$tmp_handler);
				$tmp_class = $tmp_array[0];
				$tmp_handler = $tmp_array[1];
	
				// Check if the function exists
				if( method_exists($tmp_class,$tmp_handler) ){

					// Now run the handler
					call_user_func_array( array($tmp_class,$tmp_handler), array( true,$form ) );

				} else {
				
					// We may need to include the library - check if it's in the assets folder
					if( file_exists($GLOBALS[sole]["assets"]["php"] . "sole." . $tmp_class . ".php") ){
				
						// Include the library
						require_once $GLOBALS[sole]["assets"]["php"] . "sole." . $tmp_class . ".php";
						
						if( method_exists($tmp_class,$tmp_handler) ){
						
							// Now run the handler
							call_user_func_array( array($tmp_class,$tmp_handler), array( true,$form ) );
						
						} else {
						
							$form->response("false","Method handler " . $tmp_class . "->" . $tmp_handler . " not found.");
						
						// if
						}
	
					// if
					} else {

						$form->response("false","Method handler " . $tmp_class . "->" . $tmp_handler . " not found.");
						
					// if
					}

				// if
				}
				
			// if
			}
			
			return;
				
		// if
		}
	
		if( isset($_POST) && count( $_POST ) > 0 ){
	
			// Loop through the form fields
			foreach( $_POST as $field => $value ){
			
				// Decrpyt the field
				$tmp_field = solecipher($field,true);
								
				// Check if it's got a period in it making it a sole field.
				if( strstr($tmp_field,".") ){

					$table = explode(".",$tmp_field);				
					
					// Check if we're looking for some or all
					if( $value != "*" ){
					
						// if
						if( is_numeric($value) ){
					
							$tmp_sql .= "" . $tmp_field . "=" . $value . " and ";
							
						} else {
						
							$tmp_sql .= "" . $tmp_field . " like '%" . $value . "%' and ";
						
						// if
						}
					
					// Different rules and stuff
					}
				
				// if
				}
			
			// foreach
			}

			// Root records don't get returned			
			$tmp_sql .= "" . $table[0] . ".id!=1 and ";

			// Strip the last and
			$tmp_sql = substr($tmp_sql,0,-5);		
			$tmp_sql .= " order by " . $tmp_field . " asc limit 0,25";

			// Select the records
			$db->select("criteria" . $tmp_sql);
	
			// Check if there's records to return
			if( $db->recordcount( $table[0] ) > 0 ){
			
				header("Content-type: application/json");

				// Output the results
				print_r( json_encode( $db->recordset($table[0]) ) );
	
			// if results
			}
			
		// if
		}
		
		die();
	
	// method
	}

// class
}?>