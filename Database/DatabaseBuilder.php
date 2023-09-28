<?php

/*
 * Interface:  DatabaseBuilder 
 * --------------------
 *  Provides interface function to access database builder object
 *  The implementation shall be for specific dbms
 *
 */
interface DatabaseBuilder
{
	/*
	* Function:  table	 
	* --------------------
	*  Provides database builder
	*
	*  returns: object building db queries
	*/
	public function table(string $tableName) : QueryManager;
}


?>