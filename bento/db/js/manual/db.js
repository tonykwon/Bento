// Set up a list of dbs
bento.db.db = {}

// This will use a db
bento.db.open = function( db ) {
	
  // Open up the database
  bento.db.db[ db ] = openDatabase( db , "1.0", "", (5 * 1024 * 1024) );
  
// method
}

// This will create a table
bento.db.create = function( options) {
  
  // Now create the table in a db
  bento.db.db[ options.db ].transaction(function(tx) {
	tx.executeSql("CREATE TABLE IF NOT EXISTS " + options.sql, [], bento.db.onSuccess, bento.db.onError);
  });
  
// method
}

// Select this out
bento.db.sql = function( options ) {

	// Run the transactions  
	bento.db.db[ options.db ].transaction(function(tx) {
		tx.executeSql( options.sql , [], options.function, bento.db.onError); 
	// tx
	});
  
// method
}

// Handle errors gracefully
bento.db.onError = function(tx, e) {
  // re-render the data.
  try{
  	alert( "There has been an error: " + e.message );
  } catch( err ){}
}

// Handle success gracefully
bento.db.onSuccess = function( o ) {
  // re-render the data.
  try{
  	consol.log("Success");
  } catch( err ){}
// method
}
