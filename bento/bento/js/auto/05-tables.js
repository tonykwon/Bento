// Load this all up, with storage
bento.tables = {tables:{}}
bento.tables.domupdate = function(){
	
	// Check if we've got some content
	$$('table.sortable').each(function(e){
		
								// Check if we've dealt with this yet
								if( bento.storage.reserve({
															'id':e,
															'set':'tables'
															}) 
														){
										
									// Check if we're sorted reverse
									sortReverse = e.hasClass('reverse') ? true : false;
									
									// Create the sort
									bento.tables.tables[ e.id ] = new HtmlTable(e,{
																					sortReverse:true,
																					classSortable:'',
																					classHeadSort:'header headerSortDown',
																					classHeadSortRev: 'headerSortUp',
																					sortable: true
																					});
								
									// Chech this out
									i = 0;
									e.getChildren('thead > tr > th').each(function(f){
										// Check it
										if( f.hasClass('sortable') ){
											
											// output
											sortReverse = f.hasClass('reverse') ;
											
											// Order it now
											bento.tables.tables[ e.id ].sort(i,sortReverse);
											
										// 
										}
										i++;
									});
									
								// if
								}
								
							// if
							});							

// method
}