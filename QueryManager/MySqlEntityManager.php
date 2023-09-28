<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/MySqlQueryManager.php';

/*
 * Class:  MySqlEntityManager 
 * --------------------
 *  MySqlQueryManager extension working as interface for entity class
 *  Allows entities to perform operations over their data in mysql database
 * 
 *  Attributes:
 *  	$class : Entity
 * 		
 * 
 *  Methods:
 *  	__construct(string $tableName, $class, Database $database = null)
 * 		public exec() : array
 * 		public first() : mixed
 * 		
 */
class MySqlEntityManager extends MySqlQueryManager
{
	// Holds the object of entity class that calls the manager
	protected $class;
	
	/*
	* Method: __construct 
	* --------------------
	*  Constructor of class that should only be called from within entity scope
	*  passes the table entity and its instance
	*
	*  tableName: Holds the array of named values or not : String
	*  class: Holds the array of named values or not : Entity
	*  database: Holds the array of named values or not : Database
	*
	*  returns: void
	*/
	function __construct(string $tableName, $class, Database $database = null)
	{
		$this->class = $class;
		parent::__construct($tableName, $database);
	}
	
	/*
	* Method: exec 
	* --------------------
	*  Executes SQL statement builded using entity manager
	*  if the statement returns data then manager creates entity object array
	*
	*  returns: array
	*/
	public function exec() : array
	{
		$data = parent::exec();
		$objects = [];
		
		foreach($data as $value)
		{
			$object = $this->class->instatiateObject($value);
			$object->sharify();
			$objects[] = $object;
		}
			
		return $objects;
	}
	
	/*
	* Method: first 
	* --------------------
	*  Performs similar action to exec but returns without instantiation
	*  first record
	*
	*  returns: mixed
	*/
	public function first() : mixed
	{
		return $this->limit(1)->exec()[0];
	}
}

?>