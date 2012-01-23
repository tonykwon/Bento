<?php
/*
@class: db
@description: Database class to work with innodb referential keys
@params:
@shortcode:  
@return:
*/
class db{

	// Load the variables
	public $public;
	public $private;
	
	/*
	@method: __construct()
	@description: Assign class variables from the database
	@params:
	@shortcode:  
	@return:
	*/
	public function __construct(){
	
		global $bento;

		// Load this when
		$bento->add_event('all','loaded','javascript');
		
	// method
	}
	
	/*
	@method: __configure()
	@description: Assign class variables from the database
	@params:
	@shortcode:  
	@return:
	*/
	public function __configure(){
		
		global $bento;
	
		// We're we keep all of our database information
		$this->private->dictionary = "information_schema"; // Sets the password for the database
		$this->private->connected = false; // Sets if we've connected to the database
		$this->private->link = false;	// Database link
		$this->private->sql = false; // This will output sql statements
		$this->private->master = array(); // This is a list of master tables that cannot spawn relational table if it is selected in a relational select
		$this->private->_preprocess = array();
		$this->private->log = array();
		$this->private->output = false;

		//db constants set in configuration.php
		$db = @mysql_connect($this->private->host,$this->private->username,$this->private->password);
		if( ! ($db = @mysql_connect($this->private->host,$this->private->username,$this->private->password)) ) {
			return false;
		// if
		}

		// Set the active db link
		$this->private->link = $db;

		// Database table
		if( !mysql_select_db( $this->private->database, $db) ){  return false; }
	
		// Output sql statements if $test_mode_output$this->sql
		if($this->private->output){ $this->sql("Connected to database " . $this->private->host, "Using username " . $this->private->username . " and password " . $this->private->password); }
	
		// Set that we've opened the database
		$this->private->connected = true;
	
		// Set Character Set to UTF8
		mysql_query(
				"SET
					character_set_results = 'latin1',
					character_set_client = 'latin1',
					character_set_connection = 'latin1',
					character_set_database = 'latin1',
					character_set_server = 'latin1'"
				);
	
		// Set the table list
		$this->private->tables = array();
	
		$tmp_sql = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY FROM " . $this->private->dictionary . ".COLUMNS WHERE TABLE_SCHEMA = '" . $this->private->database . "';";
		
		// Output sql statements if $test_mode_output$this->sql
		if($this->private->output){ $this->sql("Created a field list", $tmp_sql); }
			
		// Query the database and return array as record
		$result = mysql_query( $tmp_sql ) or $bento->error(mysql_error());
		
		// Loop through the rows and return records			
		while($row = mysql_fetch_array($result)){
			
			if(!isset($this->private->tables[ $row["TABLE_NAME"] ]) ){
			
				$this->private->tables[ $row["TABLE_NAME"] ] = array();
				$this->private->tables[ $row["TABLE_NAME"] ]["count"] = -1;
				$this->private->tables[ $row["TABLE_NAME"] ]["alias"] = false;
				$this->private->tables[ $row["TABLE_NAME"] ]["record"] = array();
				$this->private->tables[ $row["TABLE_NAME"] ]["filter"] = true;
				$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["relation"]["child"] = array();
				$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["relation"]["parent"] = array();
			
			}
	
			// Now Set the fields
			$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["field"][ $row["COLUMN_NAME"] ] = array();
			
			// Set the primary key
			if( $row["COLUMN_KEY"] == "PRI" ){
			
				$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["key"] = $row["COLUMN_NAME"];
			
			// if
			}
			
			// Check if this is a master table
			if( in_array($row["TABLE_NAME"],$this->private->master) ){
			
				$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["master"] = true;
			
			// else
			} else {
			
				$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["master"] = false;
			
			// if
			}
		
		// while
		}	
		
		// Just clear this since we don't need it
		unset($this->private->master);	
	
		//Find the child tables by referencing the keys 
		$tmp_sql = "SELECT `TABLE_NAME`,`COLUMN_NAME`,`REFERENCED_COLUMN_NAME`,`REFERENCED_TABLE_NAME` FROM `" . $this->private->dictionary . "`.`KEY_COLUMN_USAGE` WHERE `CONSTRAINT_SCHEMA` = '" . $this->private->database . "' and `REFERENCED_TABLE_NAME` IS NOT NULL ORDER BY `TABLE_NAME`,`REFERENCED_COLUMN_NAME`";
		
		// Output sql statements if $test_mode_output$this->sql
		if($this->private->output){ $this->sql("Created a table list", $tmp_sql); }
		
		// Query the database and return array as record
		$result = mysql_query( $tmp_sql )or $bento->error( mysql_error() );
		
		while( $row = mysql_fetch_array($result) ) { 
			
			if(	!is_null($row["REFERENCED_COLUMN_NAME"]) ){
	
				// Now set the relatios
				$this->private->tables[ $row["TABLE_NAME"] ]["schema"]["relation"]["parent"][ $row["REFERENCED_TABLE_NAME"] ] = array("field" => $row["REFERENCED_COLUMN_NAME"], "filter" => false);
				$this->private->tables[ $row["REFERENCED_TABLE_NAME"] ]["schema"]["relation"]["child"][ $row["TABLE_NAME"] ] = array("field" => $row["COLUMN_NAME"], "filter" => false);
		
				// If we've set a foriegn key as a child, then we'll set the parent too	and the filter
				if( !isset($this->private->tables[ $row["REFERENCED_TABLE_NAME"] ]["schema"]["relation"]["parent"]) ){
				
					$this->private->tables[ $row["REFERENCED_TABLE_NAME"] ]["filter"] = true;
					$this->private->tables[ $row["REFERENCED_TABLE_NAME"] ]["schema"]["relation"]["parent"] = array();
					
				}
	
			}
		
		// while	
		}
		
		return true;
	
	// method
	}

	/* Internal Private Functions */

	/*
	@method: _select( $criteria,$root=false,$ajax=false,$alias=NULL)
	@description: Creates sql for the selection of one table
	@params:
	@shortcode:  
	@return:
	*/
	private function _select( $criteria,$root=false,$ajax=false,$alias=NULL ){
		
		global $bento;
		
		// Check this out
		if( !$criteria ){  $bento->error("Database: No critera specified."); }
	
		// DO something different if this is the root
		if( $root ){
		
			// Check if we're querying with criteria or not
			if( strstr($criteria,".") ){
	
				// Split the criteria and set the first key in the array to the table name
				$table_criteria_array = explode( ".",$criteria,2 );
	
				// Set the table variable (private) as the table from the explosion above. criteria remains criteria
				$root_table = $table_criteria_array[0];
				$root_table = str_replace("(","",$root_table);
				$root_table = str_replace(")","",$root_table);
	
			// Just a table, no criteria
			} else {
			
				// add the criteria (only a table name) to the array and remove NULL for the sql criteria 
				$root_table = $criteria;
				$criteria = NULL;
				
			// if
			}
			
			// Check if it's in there
			if( !isset($this->private->tables[ $root_table ]) ){
			
				debug_backtrace();
				
				die();
				
			// if
			}
				
			// Increment the table count (so numerous picks don't get confused)
			$this->private->tables[ $root_table ]["count"]++;
			$tmp_offset = $this->private->tables[ $root_table ]["count"];
	
			// Start building the sql statement
			$tmp_sql = "select";
			
			// Check if we're injecting additional SQL
			if( isset($inject) ){
			
				$tmp_sql .= " " . $inject . " ";
				
			// if
			}
			
			// This is where we add the field names and aliases
			foreach( $this->private->tables[ $root_table ]["schema"]["field"] as $column => $junk ){
							
				$tmp_sql .= " " . $root_table . "." . $column . " as `" . $root_table . "." . $tmp_offset . "." . $column . "`,";
			
			}
			
			$tmp_sql = substr($tmp_sql,0, -1);
	
			// add it to the total sql statement
			$this->private->log[][0] = $tmp_sql;
			$this->private->log[ count($this->private->log)-1 ][1] = "";
	
			// clear this up so we don't cause problems
			$tmp_sql = "";
			
		// This is not the root table, so set the root table as the joined table
		} else {
	
			// Set that the table is the last in the criteria				
			$root_table = $criteria;
	
			// Increment the table count (so numerous picks don't get confused)
			$this->private->tables[ $root_table ]["count"]++;
			$tmp_offset = $this->private->tables[ $root_table ]["count"];
	
			// This is where we add the field names and aliases
			foreach( $this->private->tables[ $root_table ]["schema"]["field"] as $column => $junk ){
							
				$this->private->log[ count($this->private->log)-1 ][0] .= ", " . $root_table . "." . $column . " as `" . $root_table . "." . $tmp_offset . "." . $column . "`";
			
			}
	
		// if			
		}
			
		// Loop through the child relations
		foreach( $this->private->tables[$root_table]["schema"]["relation"]["child"] as $tmp_child => $value ){
	
			// Check if this table is filtered in and if the relation this relation is filtered in (from both ends)
			if( $this->private->tables[$tmp_child]["filter"] && $this->private->tables[$tmp_child]["schema"]["relation"]["parent"][$root_table]["filter"] && $this->private->tables[$root_table]["schema"]["relation"]["child"][$tmp_child]["filter"] && !(bool)(strpos( " " . $this->private->log[ count($this->private->log)-1 ][0], "SELECT " . $tmp_child)) ){
				
				// Check if we've already joined this table
				if( !strstr($this->private->log[ count($this->private->log)-1 ][1], "LEFT JOIN (`" . $tmp_child . "`)") ){
				
					// Now call the select statement
					$this->private->log[ count($this->private->log)-1 ][1] .= " LEFT JOIN (`" . $tmp_child . "`) ON (`" . $tmp_child . "`." . $value["field"] . "=" . $root_table . "." . $this->private->tables[$tmp_child]["schema"]["relation"]["parent"][$root_table]["field"] . ")";
	
				// It's already been joined so we'll add the criteria
				} else {
				
					// Now call the select statement
					$this->private->log[ count($this->private->log)-1 ][1] = str_replace("LEFT JOIN (`" . $tmp_child . "`) ON ("," LEFT JOIN (`" . $tmp_child . "`) ON (`" . $tmp_child . "`." . $value["field"] . "=" . $root_table . "." . $this->private->tables[$tmp_child]["schema"]["relation"]["parent"][$root_table]["field"] . " or ",$this->private->log[ count($this->private->log)-1 ][1]);
	
				// if
				}
				
			// if
			}
	
		// foreach
		}
		
		// Loop through the parent relations
		foreach( $this->private->tables[$root_table]["schema"]["relation"]["parent"] as $tmp_parent => $junk ){
	
			// Check if this table is filtered in
			if( $this->private->tables[$tmp_parent]["filter"] && $this->private->tables[$tmp_parent]["schema"]["relation"]["child"][$root_table]["filter"] && $this->private->tables[$root_table]["schema"]["relation"]["parent"][$tmp_parent]["filter"] && !(bool)(strpos( " " . $this->private->log[ count($this->private->log)-1 ][0], "SELECT " . $tmp_parent . ".")) ){
	
				// Check if we've already joined this table
				if( !strstr($this->private->log[ count($this->private->log)-1 ][1], "LEFT JOIN (`" . $tmp_parent . "`") ){
		
					// Now call the select statement
					$this->private->log[ count($this->private->log)-1 ][1] .= " LEFT JOIN (`" . $tmp_parent . "`) ON (`" . $tmp_parent . "`." . $this->private->tables[$root_table]["schema"]["relation"]["parent"][$tmp_parent]["field"] . "=" . $root_table . "." . $this->private->tables[$tmp_parent]["schema"]["relation"]["child"][$root_table]["field"] . ")";
	
				// It's already been joined so we'll add the criteria
				} else {
				
					// Now call the select statement
					$this->private->log[ count($this->private->log)-1 ][1] = str_replace("LEFT JOIN (`" . $tmp_parent . "`) ON ("," LEFT JOIN (`" . $tmp_parent . "`) ON (`" . $tmp_parent . "`." . $this->private->tables[$root_table]["schema"]["relation"]["parent"][$tmp_parent]["field"] . "=" . $root_table . "." . $this->private->tables[$tmp_parent]["schema"]["relation"]["child"][$root_table]["field"] . " or ",$this->private->log[ count($this->private->log)-1 ][1]);
	
				// if
				}
				
			// if
			}
	
		// foreach
		}		
		
		// Check if this is a master table and if so it can't find child/parent tables if it itself is a child/parent table
		if( !$this->private->tables[$root_table]["schema"]["master"] || $root ){
		
			// Loop through the child relations
			foreach( $this->private->tables[$root_table]["schema"]["relation"]["child"] as $tmp_child => $value ){
		
				// Check if this table is filtered in and if the relation this relation is filtered in (from both ends)
				if( $this->private->tables[$tmp_child]["filter"] && $this->private->tables[$tmp_child]["schema"]["relation"]["parent"][$root_table]["filter"] && $this->private->tables[$root_table]["schema"]["relation"]["child"][$tmp_child]["filter"] && !(bool)(strpos( " " . $this->private->log[ count($this->private->log)-1 ][0], "SELECT " . $tmp_child . ".")) ){
		
					// filter this out so we don't cause recurion
					$this->private->tables[$tmp_child]["schema"]["relation"]["parent"][$root_table]["filter"] = false;
					$this->private->tables[$root_table]["schema"]["relation"]["child"][$tmp_child]["filter"] = false;
					
					// Now call the select statement
					$this->_select( $tmp_child,false,false,$alias );
					
				// if
				}
		
			// foreach
			}
			
			// Loop through the parent relations
			foreach( $this->private->tables[$root_table]["schema"]["relation"]["parent"] as $tmp_parent => $junk ){
		
				// Check if this table is filtered in
				if( $this->private->tables[$tmp_parent]["filter"] && $this->private->tables[$tmp_parent]["schema"]["relation"]["child"][$root_table]["filter"] && $this->private->tables[$root_table]["schema"]["relation"]["parent"][$tmp_parent]["filter"] && $root_table != $tmp_parent ){
			
					// filter this out so we don't cause recurion
					$this->private->tables[$tmp_parent]["schema"]["relation"]["child"][$root_table]["filter"] = false;
					$this->private->tables[$root_table]["schema"]["relation"]["parent"][$tmp_parent]["filter"] = false;
			
					// Now call the select statement
					$this->_select( $tmp_parent,false,false,$alias );
											
				// if
				}
		
			// foreach
			}	
			
		// Master table if
		}
	
		// Testies
		if( $root ){
	
			// Criteria for the select statement is applicable
			if( strstr( $criteria,"." ) ){
			
				// Preprocess the string
				$tmp_criteria = $this->_preprocess_string( str_replace($root_table, "" . $root_table . "", $criteria) );
			
				// add a criteria for a select statement
				$tmp_sql = " where " . $tmp_criteria . ";";
			
			//if
			}
	
			// Set this temporarily
			$tmp_sql = $this->private->log[ count($this->private->log)-1 ][0] . " FROM `" . $root_table . "`" . $this->private->log[ count($this->private->log)-1 ][1] . $tmp_sql;
						
			// Unset the temporary select and criteria and reset the sql varaible so we can output what was run
			$this->private->log[ count($this->private->log)-1 ] = NULL;
	
			// Here's a record of what was executed
			$this->private->log[ count($this->private->log)-1 ] = $tmp_sql;
				
			// Output error + criteria statements if $test_mode_output_error
			if($this->private->output){
			
				// Let's take a look at our select statement				
				echo $tmp_sql;
				
			// if
			}

			// Query the database and return array as record
			$result = mysql_query( $tmp_sql ) or $bento->error( "There is an error in the sql select statement: " . $this->private->log[ count($this->private->log)-1 ] );
	
			// Loop through the rows and return records			
			while( $row = mysql_fetch_array($result, MYSQL_ASSOC) ){
			
				foreach( $row as $column => $value ){
				
					// Break our key name into a table, recordnumber, and column name
					$tmp_trc = explode(".",$column);
			
					$this->private->tables[ $tmp_trc[0] .$alias ]["schema"]["field"][ $tmp_trc[2] ][] = $value;
				
				// foreach
				}
			
			// while
			}
			
			// Now forat these into records
			foreach( $this->private->tables as $table => $junk){
	
				// This is to avoid duplicates
				$tmp_duplicates[$table] = array(); 
				
				if( !isset( $this->private->tables[$table]["schema"]["field"][ $this->private->tables[$table]["schema"]["key"] ]) ){
					
					echo $table; die();
					
				// if
				}
			
				// loop through the records
				for($i=0; $i<count($this->private->tables[$table]["schema"]["field"][ $this->private->tables[$table]["schema"]["key"] ]); $i++){
	
					// This is to avoid duplicates
					$tmp_duplicates["HOLDER"] = "";
					
					// Set the record values and that it's filtered in 
					$this->private->tables[$table]["record"][] = array("filter" => true,  "value" => array() );
						
					$tmp_record_length = 0;
							
					foreach( $this->private->tables[$table]["schema"]["field"] as $column => $value ){
				
						$tmp_record_length += strlen($this->private->tables[$table]["schema"]["field"][$column][$i]);
				
						//This is where we capture duplicates
						$tmp_duplicates["HOLDER"] .= $this->private->tables[$table]["schema"]["field"][$column][$i];
				
						$this->private->tables[$table]["record"][ count($this->private->tables[$table]["record"])-1 ]["value"][$column] = $this->private->tables[$table]["schema"]["field"][$column][$i];
					
					// foreach
					}
					
					// Check if this record actually has any information
					if($tmp_record_length == 0 ){
					
						array_pop($this->private->tables[$table]["record"]);
					
					// It's not junk so let's deal with duplicates
					} else {
					
						// Check if the last added entry was a duplicate and remove it
						if( in_array($tmp_duplicates["HOLDER"],$tmp_duplicates[$table]) ){
						
							// Remove it
							array_pop($this->private->tables[$table]["record"]);
		
						// Otherwise log it					
						} else {
						
							$tmp_duplicates[$table][] = $tmp_duplicates["HOLDER"];
							
						// if
						}
						
					}
				
				// foreach
				}
				
				// clear this varialbe
				unset($tmp_duplicates);
				
				// clear the columns
				foreach( $this->private->tables[$table]["schema"]["field"] as $column => $junk ){
				
					$this->private->tables[$table]["schema"]["field"][$column] = array();
				
				//foreach
				}
			
				// Sort everything so it looks good
				ksort($this->private->tables[$table]["schema"]);			
				ksort($this->private->tables[$table]);
			
			// foreach
			}
					
			// Return if it was successful
			if( isset($this->private->tables[$root_table . $alias ]["record"]) && count($this->private->tables[ $root_table . $alias ]["record"]) > 0 ){

				return true;
				
			} else {
				
				return false;
			
			}
	
		// root if
		}
	
	// method
	}

	/*
	@method: _preprocess_string( $string )
	@description: Will _preprocess fields before selecting, inserting, deleteing or updating
	@params:
	@shortcode:  
	@return:
	*/
	private function _preprocess_string( $string ){

		// Skip like matches
		if( stristr( $string, " like " ) ){
		
			return $string;
			
		// if
		}

		// Check for limits
		if( stristr( $string, " order by " ) ){
		
			$tmp = explode(" order by ", $string);
			$string = $tmp[0];
			$order_by = " order by " . $tmp[1];

		// else
		}
		
		// Check for limits
		if( stristr( $string, " limit " ) ){
		
			$tmp = explode(" limit ", $string);
			$string = $tmp[0];
			$limit = " limit " . $tmp[1];

		// else
		}
		
		$criterias = preg_split("/( or )|( and )/", str_replace("'","",$string),-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		$string = "";
		
		foreach( $criterias as $key => $criteria ){
		
			// Check if this is criteria or operator
			if( preg_match('/(.*).(.*)((!=)|=|<|>)(.*)/',$criteria) ){

				// Break criteria up			
				$tmp_criteria = preg_split("/(!=)|(=)|(<)|(>)/", $criteria,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			
				if( !isset( $tmp_criteria[2] ) ){
			
					echo "Line 588 db.php " . $tmp_criteria[0] . "<br>";

					echo "<pre>";
					print_r( debug_backtrace(false) );
					echo "</pre>";								
										
					die();
					
				// if
				}
			
				// Check if there's rules for this database field
				$tmp_value = $this->_preprocess( $tmp_criteria[0],$tmp_criteria[2] );

				// See if the criteria is numeric or not
				if( !is_numeric($tmp_value) || ( substr($tmp_value, -1) == ")" && !is_numeric(substr($tmp_value, 0, -1)) ) ){
				
					// Check if we've got a following ) so we don't put the ' on the wrong side
					if( substr($tmp_value, -1) == ")" ){

						$tmp_value =  "'" . trim( substr($tmp_value, 0, -1) ) . "')";
				
					} else {
					
						$tmp_value =  "'" . trim($tmp_value) . "'";
						
					// if
					}
				
				// Otherwise
				} else {
				
					 // It's only numeric so no need to wrap in '
					 $tmp_value = trim($tmp_value);
				
				// if
				}
			
				// rebuild the sql statement while checking if there's processing rules
				$string .= $tmp_criteria[0] . $tmp_criteria[1] . $tmp_value . (isset($criterias[ $key+1 ]) ? $criterias[ $key+1 ] : "") ;
						
			// if
			}
		
		// foreach
		}

		// Check for a limit
		if( isset( $order_by ) ){
		
			$string .= $order_by;
			
		// if
		}
		
		// Check for a limit
		if( isset( $limit ) ){
		
			$string .= $limit;
			
		// if
		}

		return $string;
	
	// method
	}
	
	/*
	@method: _preprocess( $field,$value )
	@description: Will _preprocess fields before selecting, inserting, deleteing or updating
	@params:
	@shortcode:  
	@return:
	*/
	private function _preprocess( $field,$value ){

		global $encryption;
		
		// Clear up the value
		$value = mysql_escape_string(trim($value));
		
		//Encrypt passwords
		if( stristr( $field,"password" ) && $value != "" ){
		
			$value = $encryption->encrypt( $value );
		
		// if
		} else if( stristr( $field,"password" ) && trim($value) == "" ){
			
			 return NULL;
		
		// if	
		}
		
		// Let's fix some dates
		if( stristr($field,"date") || stristr($field,"time") ){
			
			// Check if we can change it to a unix timestamp
			if( strtotime($value) ){
				
				$value = strtotime($value);
			
			// if
			}
			
		// if
		}
	
		// this is where we check to see if there's a _preprocessor function for this field
		foreach( $this->private->_preprocess as $junk => $pp_array ){
		
			// Check if this field is in the array
			if( $pp_array[0] == $field ){
				
				// Check if the passed
				if( function_exists( $pp_array[1] ) ){
				
					// Create an array to pass to the _preprocessing function
					$variable_array =  array( $value );
					
					// add additional variables
					if( isset( $pp_array[2] ) && $pp_array[2] != "" ){
					
						if( stristr($pp_array[2],",") ){
					
							// Break it up
							$tmp_additional = explode(",",$pp_array[2]);
							
							// add the additional to the rest
							foreach( $tmp_additional as $tmp_var ){
							
								$variable_array[] = $tmp_var;
							
							// foreach
							}
						
						// Not a coma delimited list	
						} else{
						
							$variable_array[] = $pp_array[2];
						
						// if
						}
					
					// if	
					}
				
					// Run the _preprocessor function
					$value = call_user_func_array  ( $pp_array[1], $variable_array );
				
				// if
				}
			
			// if
			}
		
		}
		
		return $value;
	
	// method
	}
	
	/*
	@method: _order_array($y, $x)
	@description: A callback function that orders arrays
	@params:
	@shortcode:  
	@return:
	*/
	private function _order_array($y, $x){
	
		//Get the column in which we order by
		global $tmp_field,$tmp_order;
		
		//Check if we've got an ordering criteria
		if( isset($tmp_field) ){
	
			// Check if the column exists in the array
			if( isset($x[$tmp_field]) ){
			
				if( strtoupper($tmp_order)=="asc" ){
	
					// Reorder based on
					if ( $x[$tmp_field] == $y[$tmp_field] )
					 return 0;
					else if ( $x[$tmp_field] < $y[$tmp_field] )
					 return -1;
					else
					 return 1;
					 
				   } else {
				
					// Reorder based on
					if ( $y[$tmp_field] == $x[$tmp_field] )
					 return 0;
					else if ( $y[$tmp_field] < $x[$tmp_field] )
					 return -1;
					else
					 return 1;
				
				}
				 
			// Column does not exist in array
			}
	
		// The is no column to order by		 
		}
		
	// method
	}
	
	/*
	@method: data_type( $request )
	@description: Format sql wrapping apostrophes around non-numeric selection criteria
	@params:
	@shortcode:  
	@return:
	*/
	private function _data_type( $request ){
			
		// Check if string, if so add '
		if( !is_numeric( trim($request) ) || (ctype_xdigit($request) && strlen($request)==6) ){
		
			$request = "'" . trim($request) . "'";
		
		// if
		}
		
		// Return the formated string
		return $request;
			
	// method
	}
	
	/* Internal Private Functions */

	/*
	@method: select( $criteria,$include_list=NULL,$alias=NULL )
	@description: A simple way to select records from all linked tables using the currently selected database
	@params:
	@shortcode:  
	@return:
	*/
	public function select( $options ){
	
		global $encryption;
		
		// Check this out
		if( !is_array($options) ){ $options = array("table"	=>	$options); }
		
		// Set this up
		$criteria = isset($options["table"]) ? $options["table"] : false;
		$include_list = isset($options["join"]) ? $options["join"] : false;
		$alias = isset($options["alias"]) && !is_null($options["alias"]) ? $options["alias"] : NULL;
		
		// Break it up
		if( !$criteria ){ return false; }
	
		// First this we do is initial a new sql compiler
		$this->private->log[] = "";
		
		// add this table to the include list
		if( stristr($criteria,".") ){
			
			$tmp = explode(".", $criteria);
			if( count($tmp) == 0 ){ $tmp_table = $criteria; } else { $tmp_table = $tmp[0]; }
			$tmp_table = str_replace("(","",$tmp_table);
			$tmp_table = str_replace(")","",$tmp_table);	
		
		// No need to break it up	
		} else {
			
			$tmp_table = $criteria;
		
		// if
		}

		// Check if there's an as in the search
		if( !is_null( $alias ) ){

			$alias = "_" . $alias;		
		
		// This table isn't aliased so we'll throw it into it's own recordset
		} else {
		
			$alias = NULL;

		// if		
		}
	
		// Check if we want to include everything
		if( stristr($include_list,"*") ) {
			
			// Loop through the table list and add it to the call list	
			foreach($this->private->tables as $table => $junk ){
			
				$include_list[] = $table;
				
			// foreach
			}
				
		} elseif($include_list == "") {
		
			$include_list = array( $tmp_table );
		
		// We have a specific list
		} else {
	
			// Check if it's got mutliple entries
			if( stristr($include_list,",") ){
			
				// Convert it into an array
				$include_list = explode(",", $include_list );
				$include_list[] = $tmp_table;
				
			// Only one entry
			} else {
	
				//Convert it into an array
				$include_list = array($include_list,$tmp_table);
		
			// if
			}
			
		// if	
		}

		// Check if we need to create an as table
		if( !is_null($alias) ){
				
			// First create the scheme
			foreach( $include_list as $table ){
				
				// Check if this alias table exists or not
				if( !isset( $this->private->tables[ $table . $alias ] ) ){
				
					$this->private->tables[ $table . $alias ] = $this->private->tables[ $table ];
					$this->private->tables[ $table . $alias ]["record"] = array();
					$this->private->tables[ $table . $alias ]["alias"] = true;
	
					// Loop through the parent and child relations
					foreach( array("child","parent") as $relation ){
						
						foreach( $this->private->tables[ $table . $alias ]["schema"]["relation"][ $relation ] as $tbl => $junk ){
					
							// Check if we want to create a new reltion to the aliased tables selected
							if( in_array($tbl,$include_list) ){
					
								$this->private->tables[ $table . $alias ]["schema"]["relation"][ $relation ][ $tbl . $alias ] = $this->private->tables[ $table . $alias ]["schema"]["relation"][ $relation ][ $tbl ];
								
							// if
							}							
	
							// Don't need these because we're working with aliases
							unset( $this->private->tables[ $table . $alias ]["schema"]["relation"][ $relation ][ $tbl ] );
		
						// foreach
						}
					
					// foreach
					}
			
				
				// if
				}
			
			// for
			}
		
		// if
		}
					
		// Disable all and en
		foreach( $this->private->tables as $tmp_table => $junk ){
	
			// Check if we're enabling this for relational selection (related tables and the root)
			if( in_array($tmp_table,$include_list) ){
			
				// Remove from the array
				$this->private->tables[ $tmp_table ]["filter"] = true;
				
				// Set the children and parent tables are filtered in
				foreach( $this->private->tables[ $tmp_table ]["schema"]["relation"]["child"] as $child => $junk ){
				
					// Turn forign key on or off if c
					if( in_array($child,$include_list) ){
				
						$this->private->tables[ $tmp_table ]["schema"]["relation"]["child"][ $child ]["filter"] = true;
					
					} else {
					
						$this->private->tables[ $tmp_table ]["schema"]["relation"]["child"][ $child ]["filter"] = false;
					
					}
				
				}
				
				// Set the children and parent tables are filtered in
				foreach( $this->private->tables[ $tmp_table ]["schema"]["relation"]["parent"] as $parent => $junk){
				
					$this->private->tables[ $tmp_table ]["schema"]["relation"]["parent"][ $parent ]["filter"] = true;
				
				}
				
			// Disable
			} else {
					
				// Remove from the array
				$this->private->tables[ $tmp_table ]["filter"] = false;
			
			// if
			}
					
		//foreach
		}
					
		// Convert the public SELECT -> private table_select with parse variables. The result is used to check if we should do something when there is no recordset.
		$response = $this->_select( $criteria,true,false,$alias );
		
		// If we have at least a root recordset then we're going to do somethings
		if( $response ){
			
			// Check if we've got an action for sucecss events
			if( isset($on_success) ){

				// eval the syntax
				eval( $on_success . ";");

			// if		
			}

		// No root recordset returned
		} else {
		
			// Check if we've got an action for failure events
			if( isset($on_failure) ){

				// eval the syntax
				eval( $on_failure . ";");

			// if		
			}

		// if		
		}
		
		// return the result
		return $response;
		
	// method
	}
	
	/*
	@method: insert( $table,$values,$echo=true )
	@description: A simple way to insert using the currently selected database
	@params:
	@shortcode:  
	@return:
	*/
	public function insert( $options ){
	
		global $bento,$encryption;
	
		// Check this out
		if( !is_array($options) ){ $options = array("table"	=>	$options); }
		
		// Set this up
		$table = isset($options["table"]) ? $options["table"] : false;
		$values = isset($options["values"]) ? $options["values"] : false;
		
		// Get rid of it
		if( !$table && !$values ){ return false; }
		
		// Check if date create exists, if so make the record
		if( $this->field_exists( $table . ".date_insert") && !isset($values["date_insert"]) ){ $values["date_insert"] = time(); }

		// Check if date create exists, if so make the record
		if( $this->field_exists( $table . ".date_update") && !isset($values["date_update"]) ){ $values["date_update"] = time(); }
	
		//Create sql Statement
		$sql = "insert into `" . $table . "` ";
		$sql_fields = ""; $sql_values = "";
		
		//Run through the fields and values
		foreach($values as $field => $value) {

				$sql_fields .= "" . $field . ", "; 
				
				// Preprocess checking
				$value = $this->_preprocess( $table . "." . $field, $value );
				
				$sql_values .=  $this->_data_type( $value ) . ", ";
		
		// foreach		
		}
		
		//Remove trailing , from $sql
		$sql_fields = substr_replace($sql_fields,"",-2);
		$sql_values = substr_replace($sql_values,"",-2);
	
		$sql .= "(" . $sql_fields . ") values (" . $sql_values . ");";

		// Output sql statements if $test_mode_output$this->sql
		if($this->private->output){ $this->sql("Executed sql Statement (form insert to table)", $sql); }
	
		// Query the database and update records
		$query = mysql_query( $sql ) /*or $bento->error( mysql_error() )*/;
		$tmp_last_id = mysql_insert_id();
	
		// Return if it was successful
		if($query) {
				
				return $tmp_last_id;
			
		} else {
				
				return false;
		
		// if
		}
	
	// method
	}
	
	/*
	@method: update( $table,$values,$criteria=NULL,$echo=true  )
	@description: A simple way to update records using the currently selected database
	@params:
	@shortcode:  
	@return:
	*/
	public function update( $options ){
	
		global $bento,$encryption;
	
		// Check this out
		if( !is_array($options) ){ $options = array("table"	=>	$options); }
		
		// Set this up
		$table = isset($options["table"]) ? $options["table"] : false;
		$values = isset($options["values"]) ? $options["values"] : false;
		$criteria = isset($options["criteria"]) ? $options["criteria"] : false;

		// Break it up
		if( !$table || !$values || !$criteria ){ return false; }

		// Check if date create exists, if so make the record
		if( $this->field_exists( $table . ".date_update") && !isset($values["date_update"]) ){ $values["date_update"] = time(); }
	
		// Check if we have a corresponding record open already
		if( $criteria == NULL && $this->recordcount( $table ) > 0 ){
		
			// Set the criteria
			$criteria = "id=" . $this->record( $table . ".id");
		
		// if
		} else if( $criteria == NULL && $this->recordcount( $table ) == 0 ){
		
			return false;
		
		// if
		}
		
		//Create sql Statement
		$sql = "update `" . $table . "` set ";
		
		//Run through the fields and values
		foreach($values as $field => $value) {

			// Preprocess checking
			$value = $this->_preprocess( $table . "." . $field, $value );
			
			// Make sure it went okay
			if( !is_null($value) ){

				$sql .= "`" . $field . "` = " . $this->_data_type( $value );

				// add , to sql if not last record
				$sql .= ", ";

			// if
			}

		// foreach
		}
		
		//Remove trailing , from $sql
		$sql = substr_replace($sql,"",-2);
	
		if( isset($criteria) ){
		
			$sql .= " where " . $criteria . ";";
		
		}

		// Output sql statements if $test_mode_output$this->sql
		if($this->private->output){ $this->sql("Executed sql Statement (form update to table)", $sql); }
		
		// Query the database and update records
		$query = mysql_query( $sql ) /* or $bento->error(mysql_error())*/;
		
		// Return if it was successful
		if($query) {
			
			return true;
			
		} else {
			
			return false;
		
		}
	
	// method		
	}
	
	/*
	@method: delete( $table,$criteria,$echo=true )
	@description: A simple way to update records using the currently selected database
	@params:
	@shortcode:  
	@return:
	*/
	public function delete( $options ){
	
		global $encryption,$bento;
	
		// Check this out
		if( !is_array($options) ){ $options = array("table"	=>	$options); }
		
		// Set this up
		$table = isset($options["table"]) ? $options["table"] : false;
		$criteria = isset($options["criteria"]) ? $options["criteria"] : false;

		// Break it up
		if( !$table || !$criteria ){ return false; }
	
		//Run through the fields and values
		$ssql = "";
		
		foreach($criteria as $field => $value) {
		
			// Preprocess checking
			$value = $this->_preprocess( $table . "." . $field, $value );

			$ssql .= "`" . $field . "` = " . $this->_data_type( $value );

			// add , to sql if not last record
			$ssql .= " and ";

		// foreach
		}
		
		// Remove the training and
		$ssql = substr_replace($ssql,"",-5);

		//Create sql Statement
		$sql = "delete from `" . $table . "`";
		
		// add criteria NO MATTER WHAT!
		$sql .= " where " . $ssql. ";";
			
		// Output sql statements if $test_mode_output$this->sql
		if($this->private->output){ $this->sql("Executed sql Statement (form delete from table)", $sql); }
		
		// Query the database and update records
		$query = mysql_query( $sql ) or $bento->error(mysql_error());
		
		// Return if it was successful
		if($query) {
				
			return true;
			
		} else {
				
			return false;
		
		// if
		}
	
	// method	
	}
	
	/*
	@method: select_count( $tc )
	@description: A simple way to get a recordcount
	@params:
	@shortcode:  
	@return:
	*/
	public function select_count( $tc ){
		
		global $bento;

		// Smash it up
		$tmp_tc = explode(".",$tc);
		
		if( is_array($tmp_tc) and count($tmp_tc ) > 1 ){
		
			$table = $tmp_tc[0];
			$criteria = " where " . $tc;
		
		} else {
		
			$table = $tc;
			$criteria = "";
		
		}
		
		$sql = "select count(*) as count from " . $table . " " . $criteria . ";";
	
		// Output sql statements if $test_mode_output$this->sql
		if( $this->private->output ){ $this->sql("Executed sql statement (select count)", $sql . "<br><br>"); }
		
		// Query the database and return array as record
		$query = mysql_query( $sql ) or $bento->error( mysql_error() . " when running " . $sql );
	
		// Loop through the rows and return records			
		$row = mysql_fetch_array($query, MYSQL_ASSOC);
		
		// Return it all
		return $row["count"];
	
	// method
	}
	
	/*
	@method: select_sum( $tf,$criteria )
	@description: Select the sum of a field
	@params:
	@shortcode:  
	@return:
	*/
	public function select_sum( $tf,$criteria ){
	
		// Smash it up
		$tmp_tf = explode(".",$tf);
		
		$table = $tmp_tf[0];
		$field = $tmp_tf[1];
	
		//	Get the field and criteria
		$tmp_fc = explode(" ",$tmp_tf[1],1);
		
		$sql = "select SUM(" . $field . ") from " . $table . " where " . $criteria . ";";
		
		// Query the database and return array as record
		$query = mysql_query( $sql ) or $bento->error( mysql_error() . " when running " . $sql );
	
		// Loop through the rows and return records			
		$row = mysql_fetch_array($query);
		
		// Return it all
		return $row[0];
	
	// method
	}
	
	/*
	@method: select_average( $tf,$criteria )
	@description: Select the average from a field
	@params:
	@shortcode:  
	@return:
	*/
	public function select_average( $tf,$criteria ){
	
		// Smash it up
		$tmp_tf = explode(".",$tfc);
		
		$table = $tmp_tf[0];
		$field = $tmp_tf[1];
	
		//	Get the field and criteria
		$tmp_fc = explode(" ",$tmp_tf[1],1);
		
		$sql = "select AVG(" . $field . ") from " . $table . " " . $criteria . ";";
		
		// Query the database and return array as record
		$query = mysql_query( $sql )or $bento->error( mysql_error() . " when running " . $sql );
	
		// Loop through the rows and return records			
		$row = mysql_fetch_array($query);
		
		// Return it all
		return $row[0];
	
	// method
	}
	
	/*
	@method: record( $recordset,$alias=NULL )
	@description: Returns a single record from a recordset (the first if multiple exists)
	@params:
	@shortcode:  
	@return:
	*/
	public function record( $recordset,$alias=NULL ){
	
		// Get the recordset
		$result = $this->recordset( $recordset, $alias ); 
				
		//Check if there is a record to return
		if( is_array($result) ){
		
			// Check if the internal pointer is set
			if( current($result) == "" ){
		
				$tmp_return = "";
				
			} else {
			
				// check if this is an array as well
				if( is_array($result[0]) ){
			
					$tmp_return = "";
					
				} else {
				
					$tmp_return = $result[0];
				
				}
			
			//if 
			}
		
		} else {
		
			$tmp_return = $result;
		
		}
		
		// Return the record
		return $tmp_return;
	
	// method
	}
	
	/*
	@method: recordset( $recordset,$alias=NULL )
	@description: Returns an array of records from table or table.field from a recordset
	@params:
	@shortcode:  
	@return:
	*/
	public function recordset( $recordset,$alias=NULL ){
		
		// Check if there's alias or not
		if( !is_null($alias) ){
		
			$alias = "_" . $alias;
			
		// if
		}
	
		// First we're going to smash the recordset into table, field, record number
		if( stristr($recordset,".") ){
			
			// Smash it up
			$tmp_recordset = explode(".",$recordset);
			
			$table = $tmp_recordset[0];
			$field = $tmp_recordset[1];
			
			// Check if we've got a record number
			if( count($tmp_recordset) == 3 ){
			
				$recordnumber = $tmp_recordset[2];
			
			// if
			}
		
		// we only have a table name
		} else {
		
			$table = $recordset;
		
		// if
		}
			
		// Check if the $table is filled in and the $table recordset exists
		if( isset($table) && isset($this->private->tables[ $table . $alias ]["record"]) ){
				
			// Check if the recordnumber varaible or the recordnumber in the recordset exists
			if( isset($recordnumber) && isset($this->private->tables[ $table . $alias ]["record"][ $recordnumber ]) ){
			
				//format the record number
				$recordnumber = (int)$recordnumber;
		
				// Check if the field exists in the recordset
				if( isset($this->private->tables[ $table . $alias ]["record"][$recordnumber ]["value"][$field]) ){
				
					// Check if we've filtered out the records
					if( $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["filter"] ){
	
						// Return our field value
						$tmp_return = $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["value"][$field];
					
					// if	
					} else {
					
						$tmp_return = array();
					
					}
				
				// field doesn't exist in recordset
				} else {
							
					// Check if we've filtered in the records
					if( $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["filter"] ){
	
						//Create the temporary array
						$tmp_array[] = $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["value"];
						
						//return the temporary array
						$tmp_return = $tmp_array;
	
					// if		
					} else {
	
						//return the temporary array
						$tmp_return = array();
					
					
					}
				
				// if
				} 
			
			// No recordnumber specified so we'll return an array of the single field	
			} else {
				
				//If the field name is epcified just return an array of the field values 
				if( isset($field) && isset($this->private->tables[ $table . $alias ]["record"][0]["value"][$field]) ) {
					
					//Loop through the array
					foreach($this->private->tables[ $table . $alias ]["record"] as $recordnumber => $junk ){
					
						// Check if we've filtered out the records
						if( $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["filter"] ){
			
							//Create the temporary array
							$tmp_array[] = $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["value"][$field];
							
						// if
						}
					
					// foreach
					}
	
					//Check if there's no records and return nothing
					if( isset($tmp_array) && is_array($tmp_array) && count($tmp_array) == 1 ){
	
						//return the temporary array
						$tmp_return = $tmp_array;
						
					//Check if there's no records and return nothing
					} elseif( isset($tmp_array) && is_array($tmp_array) && count($tmp_array) > 1 ){
	
						//return the temporary array
						$tmp_return = $tmp_array;
					
					//More than one record return an array (for looping)
					} else {
	
						//return the temporary array
						$tmp_return = false;
						
					}
					
				//otherwise just return the entire recordset
				} else {
				
					// Create a temporary array
					$tmp_array = array();
					
					//Loop through the array
					foreach($this->private->tables[ $table . $alias ]["record"] as $recordnumber => $junk ){
					
						// Check if we've filtered out the records
						if( $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["filter"] && $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["value"][ $this->private->tables[ $table . $alias ]["schema"]["key"] ] != "" ){
			
							//Create the temporary array
							$tmp_array[] = $this->private->tables[ $table . $alias ]["record"][ $recordnumber ]["value"];
							
						}
							
					}
					
					//return the temporary array
					$tmp_return = $tmp_array;
						
				}
				
			// if
			}
		
		// Either the table variable or the table recordset exist
		} else {
			
			// Return an empty array to NULLify PHP errors in loops
			$tmp_return = array();
	
		// if
		}
		
		// Return the record
		return $tmp_return;
		
	// method
	}
	
	/*
	@method: fieldcount( $table,$alias=NULL )
	@description: Returns a count of fields in a table
	@params:
	@shortcode:  
	@return:
	*/
	public function fieldcount( $table,$alias=NULL ){
	
		// Check if there's alias or not
		if( !is_null($alias) ){
		
			$alias = "_" . $alias;
			
		// if
		}	
		
		// Check if this pertains to a certain record
		if( isset($table) ) {
		
			// Setup a temporary variable to count the fields
			$tmp_count = count($this->private->tables[ $table . $alias ]["schema"]["field"]);
	
			// Remove the filter and the link if need be
			if( isset($this->private->tables[ $table . $alias ]["record"]["value"][0]["filter"]) ){
				//remove a record
				$tmp_count--;
			}
			if( isset($this->private->tables[ $table . $alias ]["record"]["value"][0]["relation"]) ){
				// Remove a record
				$tmp_count--;
			}
		
			// Return an array
			return $tmp_count;
		
		// Return an error	
		}
	
	// method
	}
	
	/*
	@method: recordcount( $table,$filtered=true,$alias=NULL )
	@description: Returns a count of fields in a table
	@params:
	@shortcode:  
	@return:
	*/
	public function recordcount( $table,$filtered=true,$alias=NULL ){
		
		// Check if there's alias or not
		if( !is_null($alias) ){
		
			$alias = "_" . $alias;
			
		// if
		}		
		
		// Check if this pertains to a certain record
		if( isset($this->private->tables[$table]["record"]) ) {
	
			// Check for the filtered items
			if( $filtered ){
	
				// Return the count
				$tmp_return = count( $this->recordset("$table") );
	
			} else {
			
				// Return the count
				$tmp_return = count( $this->private->tables[$table]["record"] );
			
			}
		
		} else {
		
			// Return that there are no records		
			$tmp_return = 0;
		
		// if	
		}
	
		// Return it
		return $tmp_return;
	
	// method
	}

	/*
	@method: order($tf,$order="asc",$alias=NULL )
	@description: Orders a recordset by criteria
	@params:
	@shortcode:  
	@return:
	*/
	public function order($tf,$order="asc",$alias=NULL ){
		
		// Check if there's alias or not
		if( !is_null($alias) ){
		
			$alias = "_" . $alias;
			
		// if
		}		
		
		//First we're going to smash the recordset into table, field, record number
		if( stristr($tf,".") ){
			
			//break it up
			$tmp_recordset = explode(".",$tf);
			
			$table = $tmp_recordset[0];
			$field = $tmp_recordset[1];
		
		// if	
		}
		
		// Set what column to sort by
		$GLOBALS["tmp_field"] = $field;
		
		// Check if the recordset exists
		if( $this->field_exists( $tf ) && $this->recordcount( $table . $alias ) > 0 ){
			
			// Check which way we're ordering
			if( isset($order) && (strtoupper($order) == "desc" || strtoupper($order) == "descending") ){
				
				// Set how we want the results order	
				$GLOBALS["tmp_order"] = $order;
					
			} else {
					
				// Set how we want the results order	
				$GLOBALS["tmp_order"] = "asc";
					
			}
			
			// Check if there's an alias on this table
			foreach($this->private->tables[ $table . $alias ]["record"] as $rno => $values){
	
				// Create a temporary filter field to store this flat
				$this->private->tables[ $table . $alias ]["record"][ $rno ]["value"]["{TEMPORARY FILTER}"] = $this->private->tables[ $table . $alias ]["record"][ $rno ]["filter"];
	
				// Create a temporary flat array to order
				$tmp_array[] = $this->private->tables[ $table . $alias ]["record"][ $rno ]["value"];
			
			}
		
			// Sort the array for priority
			usort($tmp_array, array( $this,'_order_array'));
			
			// clear our current recordset so we can read it
			$this->clear( $table . $alias );
			
			// add a record number
			$i = 0;
			
			// Now restore the correct filter variable
			foreach($tmp_array as $rno => $values){
	
				// Set in correct order, add the filter, and the values
				$this->private->tables[ $table . $alias ]["record"][$i]["filter"] = $values["{TEMPORARY FILTER}"];
				$this->private->tables[ $table . $alias ]["record"][$i]["value"] = $values;
	
				// Remove the temporary filter from the recordset
				unset($this->private->tables[ $table . $alias ]["record"][ $i ]["value"]["{TEMPORARY FILTER}"]);
			
				// iterate our record number
				$i++;
			
			}
			
			// clear these variables for safety
			unset( $GLOBALS["tmp_field"] );
			unset( $GLOBALS["tmp_order"] );
			
		} else {
		
			return false;
	
		}
	
	// method
	}

	/*
	@method: field_merge($tf,$order="asc",$alias=NULL )
	@description: merges 2 fields into a final field
	@params:
	@shortcode:  
	@return:
	*/
	public function field_merge($table,$f1,$f2,$f3,$alias=NULL ){
		
		// Check if there's alias or not
		if( !is_null($alias) ){
		
			$alias = "_" . $alias;
			
		// if
		}
		
		// Check if the recordset exists
		if( $this->field_exists( $table . "." . $f1 ) && $this->field_exists( $table . "." . $f2 ) && $this->recordcount( $table . $alias ) > 0 ){
			
			// Check if there's an alias on this table
			foreach($this->private->tables[ $table . $alias ]["record"] as $i => $values){
	
				// Set in correct order, add the filter, and the values
				$this->private->tables[ $table . $alias ]["record"][$i]["value"][ $f3 ] = $this->private->tables[ $table . $alias ]["record"][$i]["value"][ $f1 ] . " " . $this->private->tables[ $table . $alias ]["record"][$i]["value"][ $f2 ];
			
			}

		// if	
		}
	
	// method
	}
	
	/*
	@method: sum( $tf )
	@description: Calculates the sum of a field in a table
	@params:
	@shortcode:  
	@return:
	*/
	public function sum( $tf ){
		
		//First we're going to smash the recordset into table, field, record number
		if( stristr($tf,".") ){
			
			//break it up
			$tmp_recordset = explode(".",$tf);
			
			$table = $tmp_recordset[0];
			$field = $tmp_recordset[1];
		
		// if	
		}
		
		// Set the initial sum value
		$sum = 0;
			
		// Check if this is a numeric column
		if( isset($this->private->tables[$table]["record"][0]["value"]) && isset($this->private->tables[$table]["record"][0]["value"][$field]) && is_numeric( intval( $this->private->tables[$table]["record"][0]["value"][$field] ) ) ){
		
			// Loop through and get the sum
			foreach($this->private->tables[$table]["record"] as $srecord){
			
				// Check if we've filtered out the records
				if( !isset($srecord["filter"]) || $srecord["filter"] ){
			
					$sum += $srecord["value"][$field];
					
				}
			
			}
		
			// Return the sum
			$tmp_return = $sum;
						
		// This is not a numeric column, therefore we can't get the sum
		} else {
		
			$tmp_return = 0;
		
		// if
		}
		
		return $tmp_return;
			
	// method
	}
	
	/*
	@method: random( $tf )
	@description: Gets a random record from a recordset
	@params:
	@shortcode:  
	@return:
	*/
	public function random( $tf ){

		//First we're going to smash the recordset into table, field, record number
		if( stristr($tf,".") ){
			
			//break it up
			$tmp_recordset = explode(".",$tf);
			
			$table = $tmp_recordset[0];
			$field = $tmp_recordset[1];
		
		// if no field, just return the table
		} else {
		
			$table = $tf;
		
		}
			
		// Check if this table exists
		if( isset( $table ) && isset( $this->private->tables[$table]["record"] ) ){
	
			// First we'll count the record and generated a random number
			$tmp_record_number = (rand( 1, $this->recordcount( $table ) ) - 1);
				
			// Return the field of the randomized record
			if( isset($this->private->tables[$table]["record"][$tmp_record_number]["value"][$field]) ){
		
				return $this->private->tables[$table]["record"][$tmp_record_number]["value"][$field];
				
			// Return the randomized record
			} else {
			
				return $this->private->tables[$table]["record"][$tmp_record_number]["value"];
			
			// if
			}
									
		// This is not a numeric column, therefore we can't get the sum
		}
		
	// method
	}
	
	/*
	@method: filter( $filter,$clear=true,$related=true,$relation=NULL,$alias=NULL )
	@description: Filters records in/out (**** This is buggy)
	@params:
	@shortcode:  
	@return:
	*/
	public function filter( $filter,$clear=true,$related=true,$relation=NULL,$alias=NULL ){
	
		// Smash up our filter, first by comparison operator. Replace the CO with a dot, then explode into table, field, value.
		if( stristr($filter, "!=") ) { $co = "!="; $filter = str_replace($co,".",$filter); }
		elseif( !isset($co) && stristr($filter, ">=") ) { $co = ">="; }
		elseif( !isset($co) && stristr($filter, "<=") ) { $co = "<="; }
		elseif( !isset($co) && stristr($filter, ">") ) { $co = ">"; }
		elseif( !isset($co) && stristr($filter, "<") ) { $co = "<"; }
		elseif( !isset($co) && (stristr($filter, "=") || stristr($filter, "==")) ){ $co = "==";}
	
		//Explode into table field and value
		$tmp_filter = explode(".",$filter);
	
		// Check the alias
		if( !is_null($alias) ){
		
			$alias = "_" . $alias;
		
		// if
		}
	
		//Check if we've got the required info
		if( count($tmp_filter) >= 2 ){
	
			// this is for filter and un-filter
			$table = $tmp_filter[0];
			$tmp_field = explode( str_replace("==","=",$co),$tmp_filter[1]);
			$field = $tmp_field[0];
			
			// Explode the filter into a value
			$tmp_value = explode(str_replace("==","=",$co), $filter);
					
			// Assign the value
			$value = $tmp_value[1];
		
		// if	
		}
		
		// add this criteria to the do not call list
		if( isset($relation) ){
		
			$parent_table = $relation;
	
		} else {
		
			$parent_table = "";
		
		}
	
		// Create our do not call list for the root variable
		if( $relation==NULL ){
			
			// Reset the do not call list
			$this->private->donotcall =  array();
	
			// If we're clearing our filters, filter everything out, but only at the beginnig
			if( $clear ){
			
				// This filters everything out
				$this->unfilter( NULL,false );	
			
			// if
			}
	
		// if
		}
	
		$table = $table . $alias;
	
		// Check if the variables are set
		if( isset($table) && isset($field) && isset($co) && isset($this->private->tables[$table]["record"][0]["value"][$field]) ){
		
			// Loop through and get the add it to the filter
			foreach($this->private->tables[$table]["record"] as $recordnumber => $junk ){
			
				// Temporary field to make comparison easier
				$tmp_field = $this->private->tables[$table]["record"][$recordnumber]["value"][$field];
	
				// Check if the record matches
				if( eval("return('$tmp_field' $co '$value');") ){
				
					// add/Update the column name __filter__ to be included excluded
					$this->private->tables[$table]["record"][$recordnumber]["filter"] = true;
					
						foreach($this->private->tables[$table]["schema"]["relation"]["child"] as $child_table => $tmp_field ){
							
							// add to a temporary list to get once we're through filtering all these in
							$tmp_related_filters[] = $child_table . "." . $tmp_field["field"] . "=" . $this->private->tables[$table]["record"][$recordnumber]["value"][ $this->private->tables[$child_table]["schema"]["relation"]["parent"][$table]["field"] ];
							
						// foreach	
						}
						
						foreach($this->private->tables[$table]["schema"]["relation"]["parent"] as $parent_table => $tmp_field ){
						
							// echo $parent_table;
							
							// add to a temporary list to get once we're through filtering all these in
							$tmp_related_filters[] = $parent_table . "." . $tmp_field["field"] . "=" . $this->private->tables[$table]["record"][$recordnumber]["value"][ $this->private->tables[$parent_table]["schema"]["relation"]["child"][$table]["field"] ];
							
						// foreach	
						}
				
				// if	
				} else {
				
					// filter this out if we're set to clear current results otherwise leave it
					if( $clear ){
					
						// $this->private->tables[$table]["record"][$recordnumber]["filter"] = false;		
						
					}
				
				}
	
			// foreach		
			}
					
			// list that this table can't be called
			$this->private->donotcall[] = $table;
			
			// Now we'll remove duplicate filters from the array
			if( isset($tmp_related_filters) && is_array($tmp_related_filters)){
	
				array_flip($tmp_related_filters);
			
			} else { $tmp_related_filters = array(); } 
			
			// Loop through the related array and do some business		
			foreach($tmp_related_filters as $relation_filter){	
	
				// Get this page
				$tmp_table = substr( $relation_filter,0, strpos($relation_filter, "."));	
				
				// add this to the allowed called list if it hasn't been called by another table yet
				if( !in_array($tmp_table,$this->private->donotcall) ){
				
					// list that this table can't be called
					$this->private->donotcall[] = $table . "." . $tmp_table;
				
				}
			
				// Check if we've filtered from this
				if( !in_array($tmp_table,$this->private->donotcall) || in_array( $table . "." . $tmp_table,$this->private->donotcall) ){
				
					$this->filter($relation_filter,false,true,$table);
					
				// if
				}
				
			// foreach
			}
			
				
		// if
		}
		
		if( $relation==NULL ){ unset($this->private->donotcall); }
			
	// method
	}

	/*
	@method: unfilter( $table=NULL,$in=true ) 
	@description: Clears filters on one or all recordsets
	@params:
	@shortcode:  
	@return:
	*/
	public function unfilter( $table=NULL,$in=true ){
	
		// Check if we're filtering a certain table out
		if( !isset($table) ){
	
			//Loops throught the recordsets
			foreach($this->private->tables as $table => $junk ){
	
				if( $table != "donotcall") {
				
					// Check if we have records
					if( isset($this->private->tables[$table]["record"]) ){
	
						foreach($this->private->tables[$table]["record"] as $recordnumber => $record){
			
							$this->private->tables[$table]["record"][$recordnumber]["filter"] = (bool)$in;
							
						}
	
					}
					
				}
					
			}
		
		} else {
		
			// Check if isset
			if( isset($this->private->tables[$table]["record"]) ){
			
				if( $table != "donotcall" ) {
		
					foreach($this->private->tables[$table]["record"] as $recordnumber => $record){
			
						$this->private->tables[$table]["record"][$recordnumber]["filter"] = (bool)$in;
						
					}
					
				}
		
			}
		
		}
	
	//function	
	}
	
	/*
	@method: records( $simple=false )
	@description: Outputs a list of opened recordsets for reference.
	@params:
	@shortcode:  
	@return:
	*/
	public function records( $simple=false ){ $this->recordsets( $simple ); }
	public function recordsets( $simple=false ){ 

		// Loop through record set variables
		foreach($this->private->tables as $table => $junk){
		
			if( !$simple ){
		
				//Check if we just want records and not recordsets
				if( (isset($this->private->tables[$table]["record"]) && count($this->private->tables[$table]["record"])>0) ){
			
					// Oooo - the counter
					$i = 0;
			
					//spit it out
					echo "<table background='#000' cellspacing='1'>
							<tr bgColor='#808080'>
							 <td colspan='" . (count($this->private->tables[$table]["record"][0]["value"])-1) . "'><font size='3'>&nbsp;" . $table . "</h1></td>
							 <td colspan='2'>&nbsp;" . count($this->private->tables[$table]["record"]) . " Records</td>
							</tr>
							<tr>";
					
						// Loop thorugh the field headers
						foreach( $this->private->tables[$table]["record"] as $recordnumber => $record ){
			
							// 
							if($i==0){
		
								echo "<tr bgColor='#E0E0E0'>";
								
								// Loop through the records for the fields headers
								foreach( $this->private->tables[$table]["record"][$i]["value"] as $sfield => $value ){
								
									// Check if filter field which doesn't need to be displayed
									if( $sfield != "filter" ){
										
										if( $sfield != "relation" ){
											echo "<td nowrap width='100'>&nbsp;" . $sfield . "</td>";
										 } else {
											echo "<td width='100'>&nbsp;linked To</td>";												
										}
									
									// if	
									}
		
								}	
		
								echo "</tr>";
								
							}
		
							// Check if this record is filtered. If so change the color
							if( !$this->private->tables[$table]["record"][$i]["filter"]){
								echo "<tr bgColor=\"#FF0000\">";
							// Not filtered
							} else {
								echo "<tr bgColor='#FFFFFF'>";
							}
			
							// Loop through the records for the fields
							foreach($this->private->tables[$table]["record"][$i]["value"] as $sfield => $value){
			
								// Check if we're dealing with the link field
								if( $sfield == "relation" ){
								
									echo "<td nowrap>";
									
										foreach($value as $srelation){
							
											$table_array = explode(".", $srelation);
											$field_array = explode("=",$table_array[1]);
											
											$tmp_table = $table_array[0];
											$tmp_field = $field_array[0];
											$tmp_value = $field_array[1];
											
											//This is where we filter out the relations
											echo $tmp_table . "&nbsp;&nbsp;" . $tmp_field . "  " . $tmp_value . "<br>";
										
										// foreach	
										}
										
									echo "</td>";
									
								// Write out the field and the value
								} elseif( $sfield != "filter" ) {
									echo "<td nowrap>&nbsp;" . $value . "</td>";
								}
										
							}
							
						echo "
						</tr>";
						
						// count up through the records			
						$i++;
						
						// foreach statement	
						}
										
					echo "</table>
					
					<br><br>";
		
				//if	
				}
				
			} else {
			
				//Check if we just want records and not recordsets
				if( (isset($this->private->tables[$table]["record"]) && count($this->private->tables[$table]["record"])>0) ){

					// Oooo - the counter
					$i = 0;

					echo $table . " " . count($this->private->tables[$table]["record"]) . "\r\n";
									
					// Loop thorugh the field headers
					foreach( $this->private->tables[$table]["record"] as $recordnumber => $record ){
					
						foreach($this->private->tables[$table]["record"][$i]["value"] as $sfield => $value){
						
							echo "-" . $sfield . " " . $value. "\r\n";
	
						}
						
						// count up through the records			
						$i++;
	
					// foreach
					}
					
					echo "\r\n\r\n===================================\r\n\r\n";
					
				// if
				}
			
			}
	
		// foreach				
		}
	
	// method
	}

	/*
	@method: clear( $tfv=NULL,$alias=NULL )
	@description: Clears one or more recordsets
	@params:
	@shortcode:  
	@return:
	*/
	public function clear( $tfv=NULL,$alias=NULL ){
		
		// Get an alias if there is one
		if( !is_null( $alias ) ){
		
			$alias = "_" . $alias;
		
		// if
		}
			
		//Check if we're clearing a specific tables
		if( isset($tfv) ){
				
			// Check if there is more than one table
			if( strstr($tfv,",") ){
		
				$tmp_tables = explode(",",$tfv);
	
			} else {
			
				$tmp_tables = array($tfv);
				
			// if
			}
			
			// Loop through the tables
			foreach($tmp_tables as $tmp_table){
			
				// Check if we're going to clear one or more records
				if( strstr($tmp_table,".") && strstr($tmp_table,"=") ){

					// split the table and field
					$tmp = explode(".",$tmp_table);
					$tmp_table0 = $tmp[0];
					$tmp = explode("=",$tmp[1]);
					$field = $tmp[0];
					$value = $tmp[1];

					// clear the records, the parent/child table lists, & the do not call list
					foreach( $this->private->tables[ $tmp_table0 . $alias ]["record"] as $key => $record ){
					
						// check if the record matached
						if( $this->private->tables[ $tmp_table0 . $alias ]["record"][ $key ]["value"][ $field ] == $value ){
						
							// Remove the 
							unset($this->private->tables[ $tmp_table0 . $alias ]["record"][ $key ]);
						
						// if
						}
					
					// if
					}
					
					$this->private->tables[ $tmp_table0 ]["record"] = array_values($this->private->tables[$tmp_table0]["record"]);

				// Just clear the table
				} else {
				
					if( isset($this->private->tables[ $tmp_table . $alias ]) ){
				
						// check if it's an alias or not
						if( $this->private->tables[ $tmp_table . $alias ]["alias"] ){
						
							// Clear the schema
							unset($this->private->tables[ $tmp_table . $alias ]);
						
						// just clear the records
						} else {
					
							// clear the records, the parent/child table lists, & the do not call list
							$this->private->tables[ $tmp_table . $alias ]["record"] = array();
	
						// if
						}

					// if
					}
					
				// if
				}
				
			}
	
		} else {
		
			// Loop through the tables
			foreach( $this->private->tables as $tmp_table => $junk ){
	
				// check if it's an alias or not
				if( $this->private->tables[ $tmp_table ]["alias"] ){
				
					// Clear the schema
					unset($this->private->tables[ $tmp_table ]);
				
				// just clear the records
				} else {
			
					// clear the records, the parent/child table lists, & the do not call list
					$this->private->tables[ $tmp_table ]["record"] = array();

				// if
				}
	
			// foreach
			}
					
		}
	
	// method
	}
	
	/*
	@method: clear_unfiltered()
	@description: Clears one or more recordsets, removing only filtered out records (**** Should be merged with clear)
	@params:
	@shortcode:  
	@return:
	*/
	public function clear_unfiltered(){
		
		// Loop through the tables
		foreach( $this->private->tables as $tmp_table => $junk ){
		
			// A counter
			$j=0;
			
			//print_r( $this->private->tables[$tmp_table]["record"] );
		
			// Loop through and get the sum
			foreach($this->private->tables[$tmp_table]["record"] as $rno => $srecord){
			
				// Check if we've filtered out the records
				if( isset($this->private->tables[$tmp_table]["record"][$rno]["filter"]) && $this->private->tables[$tmp_table]["record"][$rno]["filter"] != 1 ){
			
					// Remove the record from the set
					unset($this->private->tables[$tmp_table]["record"][$rno]);
					
					// Reset the count
					//$this->private->tables[$tmp_table]["count"]--;
				
				// if
				} 
				
			// for each$
			}

			// Get rid of the missing keys
			$this->private->tables[$tmp_table]["record"] = array_values($this->private->tables[$tmp_table]["record"]);
			
			//print_r( $this->private->tables[$tmp_table]["record"] );
	
		// foreach
		}
	
	// method
	}
	
	/*
	@method: table_exists ( $table )
	@description: Returns is a table exists or not
	@params:
	@shortcode:  
	@return:
	*/
	public function table_exists ( $table ){
		
		// Check if the table exiss
		if( isset($this->private->tables[$table]) ){
		
			return true;
			
		} else {
		
			return false;
		
		}
	
	// method
	}
	
	/*
	@method: field_exists ( $tf )
	@description: Returns is a field exists or not
	@params:
	@shortcode:  
	@return:
	*/
	public function field_exists ( $tf ){
		
		// Check if this igood or not
		if( strstr( $tf,".") ){
		
			$tmp = explode(".",$tf);
			
		} else {
		
			return false;
		
		// if
		}
		
		// Check if the table exiss
		if( isset($this->private->tables[ $tmp[0] ]["schema"]["field"][ $tmp[1] ]) ){
		
			return true;
			
		} else {
		
			return false;
		
		}
	
	// method
	}

	/*
	@method: join( $tf,$join,$associated="",$order="" )
	@description: Join a table
	@params:
	@shortcode:  
	@return:
	*/
	public function join( $options ){
		
		// Set everything up
		$table = isset( $options["table"] ) ?$options["table"] : false;
		$on = isset( $options["on"] ) ? $options["on"] : false;
		
		// Return it
		if( !$table || !$on ){ return false; }
		
		// Check it
		$join = isset( $options["join"] ) ? $options["join"] : NULL;
		$alias = isset( $options["alias"] ) ? $options["alias"] : NULL;
		$criteria = isset( $options["criteria"] ) ? $options["criteria"] : "";

		// Get the right join
		$tmp = explode(".",$on);
		
		// Set up the ordering
		$criteria = ($criteria != "") ? " " . $criteria : ""; 

		// Check it
		if( $this->recordcount($tmp[0]) > 0 ){

			// Get the select
			$this->select( 
						array(
							"table"	=>	$table . "=" . implode(" or " . $table . "=", $this->recordset( $on )) . $criteria, 
							"join"	=>	$join,
							"alias"	=>	$alias
							)
						);

		// if
		}

	// method
	}

	/*
	@method: limit( $tf,$join )
	@description: Takes a recordset and limites it
	@params:
	@shortcode:  
	@return:
	*/
	public function limit( $tf,$join ){

		// Get the select
		$this->select( $tf . "=" . implode(" or " . $tf . "=", $this->recordset( $join )) );

	// method
	}
	
	/*
	@method: flatten( $table,$meta,$order=NULL,$by="asc" )
	@description: Flattens a meta table into a table
	@params:
	@shortcode:  
	@return:
	*/
	public function flatten( $table,$meta,$order=NULL,$by="asc" ){
	
		// Check if there's account information open
		if( $this->recordcount( $table ) > 0 && $this->recordcount( $meta ) > 0 ){
	
			// Now loop through to add the value to the schema for ordering
			foreach( $this->recordset( $meta ) as $tmp_meta ){

				// Add field to schema
				$this->private->tables[ $table ]["schema"]["field"][ $tmp_meta["name"] ] = array();
				
				// Add a blank value to start
				foreach( $this->recordset( $table ) as $rno => $tmp_table ){
				
					// Make sure we dont' overwrite anything
					if( !isset( $this->private->tables[ $table ]["record"][ $rno ]["value"][ $tmp_meta["name"] ] ) ){
	
						// Add the meta to the record
						$this->private->tables[ $table ]["record"][ $rno ]["value"][ $tmp_meta["name"] ] = "";
						
					// if
					}
				
				// foreach
				}
				
			// foreach
			}
					
			// Loop through the account to add appropriate information
			foreach( $this->recordset( $table ) as $rno => $tmp_table ){
			
				// Filter in the record according to the key
				$this->filter( $table . "." . $this->private->tables[ $table ]["schema"]["key"] . "=" . $tmp_table[ $this->private->tables[ $table ]["schema"]["key"] ] );
			
				// Now loop through to add the value to the schema for ordering
				foreach( $this->recordset( $meta ) as $tmp_meta ){
	
					// Add the meta to the record
					$this->private->tables[ $table ]["record"][ $rno ]["value"][ $tmp_meta["name"] ] = $tmp_meta["value"];
					
				// if
				}
			
			// foreach
			}	

			// Order if applicable
			if( !is_null( $order ) ){
			
				// Make sure the field exists
				if( $this->field_exists( $table . "." . $order ) ){
			
					$this->order( $table . "." . $order,$by );
					
				// if
				}
			
			// if
			}
			
			// Lastly, clear up the $meta table to speed things up
			$this->clear( $meta );

			// Clear the filters
			$this->unfilter();
			
			// response
			return true;
		
		// if
		} else {

			// response
			return false;
			
		// if
		}
		
	// method
	}
	
	/*
	@method: execute( $sql )
	@description: Executes an sql file
	@params:
	@shortcode:  
	@return:
	*/
	public function execute( $sql ){

		$queries = explode( ";", $sql ); 
		for( $i=0; $i<count($queries); $i++ ) { 
		  $queries[$i] = trim($queries[$i]); 
		} 

		foreach( $queries as $query){
			
			// Query the database and return array as record
			$result = mysql_query( $query );
		
		// foreach
		}
		
		return true;

	// method
	}

	/*
	@method: output()
	@description: Turns SQL outputting on/off for debugging purposes.
	@params:
	@shortcode:  
	@return:
	*/
	public function output(){ 
		
		// Check if we're starting the stopwatch or endinf it
		if( !$this->private->output ){
	
			//Output sql
			$this->private->output = true;
	
		// Output the time
		} else {
		
			// Start counting
			$this->private->output = false;
		
		}
	
	// method
	}
	
	/*
	@method: sql( $title,$sql )
	@description: Formats sql into a readable text and echo's it
	@params:
	@shortcode:  
	@return:
	*/
	public function sql( $title,$sql ){
	
		//Output the sql
		echo $sql;
		
	// method
	}

	/*
	@method: webdb( $title,$sql )
	@description: enables web db
	@params:
	@shortcode:  
	@return:
	*/
	public function webdb( $onoff=true ){
		
		// Enable it
		$this->private->webdb = $onoff;	
		
	// method
	}

	/*
	@method: javascript( $title,$sql )
	@description: Add the js library if we required web db
	@params:
	@shortcode:  
	@return:
	*/
	public function javascript(){
		
		global $bento;
		
		// Check if we're adding the js database or not
		if( $this->private->webdb ){
			
			// Add the javascript
			$bento->add_js(
						array(
								"plugin"	=>	"db",
								"name"	=>	"db"
							)
						);
			
		// if
		}
		
	// method
	}	

// class
}
?>