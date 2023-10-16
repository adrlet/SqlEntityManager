<?php

	/*
	* Trait:  DatabaseEntity 
	* --------------------
	*  Extends class on database communication functionalities
	*  Trait provides link between database table counterpart and php class 
	*  
	*  Attributes:
	*		$database : Database
	*		$tableName : String
	*		$primaryKeyName : String
	*		$primaryKeyType : String
	*		$autoIncrement : Bool
	*		$primaryKeyValue : Mixed
	* 
	*  Methods:
	*	Interface:
	*		public getPrimaryKey() : array
	*		public function getTableName() : string
	* 
	*/
trait DatabaseEntity
{
	// Database type object connected to dbms containing counterpart table
	protected static $database = null;

	// Specifies name of the counterpart table
	protected static $tableName = '';
	
	// The name of attribute representing record identificator
	protected static $primaryKeyName = 'id';

	// Type of primary key
	protected static $primaryKeyType = 'int';

	// Boolean check for autoincrement records within dbms
	protected static $autoIncrement = true;

	// The value of identifier of object
	protected $primaryKeyValue = null;
	
	/*
	* Method: getPrimaryKey 
	* --------------------
	*  Returns full information about primary key
	*
	*  returns: array
	*/
	public function getPrimaryKey() : array
	{
		return ['name' => Static::$primaryKeyName, 'type' => Static::$primaryKeyType, 
				'value' => $this->primaryKeyValue, 'autoIncrement' => Static::$autoIncrement];
	}
	
	/*
	* Method: getTableName 
	* --------------------
	*  Returns table name of counterpart
	*
	*  returns: string
	*/
	public function getTableName() : string
	{
		return $this->tableName;
	}
}

?>