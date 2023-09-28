<?php

/*
 * Interface:  GlobalDatabaseBuilder 
 * --------------------
 *  Provides interface function to access database builder object
 *  The implementation shall be for specific dbms
 *  Global database is shared and shall be called from proper class namespace
 *
 */
interface GlobalDatabaseBuilder
{
	/*
	* Function:  globalTable	 
	* --------------------
	*  Provides database builder
	*
	*  returns: object building db queries
	*/
	public static function globalTable(string $tableName) : QueryManager;
}

?>