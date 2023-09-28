<?php

/*
 * Trait:  GlobalDatabase 
 * --------------------
 *  Trait designed for classes representing database connections
 *  Provides capabilities of using initialized database connection
 *  from static context
 * 
 *  Attributes:
 *  	static $globalDatabase : Database|Null
 * 		static configFileName : String
 * 
 *  Methods:
 *  	static setGlobalDatabaseFromConfig(string $configFileName) : void
 *  	static getGlobalDatabase() : Database|Null
 * 
 */
trait GlobalDatabase
{
	// Class object representing database available from static scope
	protected static $globalDatabase = null;
	
	// The name of the file which represent connection info to DBMS of global database
	protected static $configFileName = 'dbms.cfg';
	
	/*
	* Method: setGlobalDatabaseFromConfig 
	* --------------------
	*  Reads the file which name is retrieved from either parameter or configFileName if the first is null,
	*  The content of file should constist of lines in form key=value, whose describes connection information,
	*  The actual keys depends on class using traits
	*  The content then is used for creating instance of database object for static context
	*
	*  configFileName: The name of other configuration data file, overrides $configFileName attribute : String
	*  
	*  returns: void
	*/
	public static function setGlobalDatabaseFromConfig(string $configFileName = '') : void
	{
		// If overriding name is not provided, get the default
		if(empty($configFileName))
			$configFileName = Database::$configFileName;

		// Loads the data configuration of format key=pair seperated by new lines and splits them into an array
		$configData = file_get_contents($configFileName);
		$configRows = explode(PHP_EOL, $configData);
		
		// Instatiaties globalDatabase by implementing class constructor
		Database::$globalDatabase = new static($configRows);
	}

	/*
	* Method: getGlobalDatabase 
	* --------------------
	*  The getter method for retrieving the globalDatabase object instance
	*  Global database object is shared and should be accessed from specific context
	*
	*  returns: implementing class object instance : Database|Null
	*/
	public static function getGlobalDatabase() : Database
	{
		// The no global database exception must be handled
		if(is_null(Database::$globalDatabase))
			throw new Exception(get_called_class().':'.__FUNCTION__.':Global Database unavailable');

		return Database::$globalDatabase;
	}

	public static function presentGlobalDatabase() : bool
	{
		return is_null(Database::$globalDatabase);
	}
}

?>