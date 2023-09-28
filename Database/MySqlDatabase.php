<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Database/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Database/DatabaseBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Database/GlobalDatabaseBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/MySqlQueryManager.php';

/*
 * Class:  MySqlDatabase 
 * --------------------
 *  Implementation of the abstract database class for mysql database
 *  It provides the means for access to querying mysql DBMS
 *  Both from static and specific context
 *
 *  Methods:
 * 		public table(string $tableName) : MySqlQueryManager
 * 		public static globalTable(string $tableName) : MySqlQueryManager
 *
 */
class MySqlDatabase extends Database implements DatabaseBuilder, GlobalDatabaseBuilder
{	
	/*
	* Function:  table 
	* --------------------
	*  Provides object for building mysql query using methods
	*  The object is operating on tabel specified in parameter
	*
	*  tableName: The name of table within current database : String
	*
	*  returns: object allowing to construct query by methods : MySqlQueryManager
	*/
	public function table(string $tableName) : MySqlQueryManager
	{
		return new MySqlQueryManager($tableName, $this);
	}
	
	/*
	* Function:  globalTable 
	* --------------------
	*  Provides object for building mysql query using methods
	*  The object is operating on tabel specified in parameter
	*  Global database is shared, so it may not support mysql queries
	*  If global database is created for other dbms
	*
	*  tableName: The name of table within current database : String
	*
	*  returns: object allowing to construct query by methods : MySqlQueryManager
	*/
	public static function globalTable(string $tableName) : MySqlQueryManager
	{
		return new MySqlQueryManager($tableName, MySqlDatabase::$globalDatabase);
	}
}

?>