<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Model/Model.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/EntityBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/AttributeCast.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/DatabaseEntity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/MySqlEntityManager.php';

	/*
	* Trait:  Entity 
	* --------------------
	*  Extension of Model class for database interaction
	*  Allows to bridge inheriting class with its dbms table counterpart
	*  Also supports methods for quick interaction of object with its table
	*  This class allows for modularity in sense that there exists traits
	*  That can be used by inheriting class with support from Entity superclass
	*
	*  Subclass should specify attributes with their type, extra traits for use
	*  and its name should be cammel case for coresponding snake case of table name
	*  
	*  Attributes:
	*		$attributes : Array
	*  		$attributesFillable : Array
	*		$attributesGuarded : Array
	*		$attributesHidden : Array
	* 
	*  Methods:
	*	Internal:
	*		
	*	Interface:
	*		public getState() : string
	*		public exists(int|string $key) : bool
	*		public get(string|int $key) : mixed
	*		public set(string|int $key, mixed $value) : void
	*		public save() : void
	*		public revert() : void
	*		public sharify() : void
	*		public invalidate() : void
	* 
	*/
abstract class Entity extends Model implements EntityBuilder
{
	use AttributeCast, DatabaseEntity, SoftDelete, EntityQuery;
	
	// Holds attributes names extracted from model dynamic array
	protected static $attributeKeys = [];

	// Holds extra traits for entity class but implemented by subclass
	protected static $traits = [];
	
	// Holds information wheter object is filled with data from dbms
	private $loaded = false;
	
	/*
	* Method: __construct 
	* --------------------
	*  Calls model constructor and entity value filling if provided
	*
	*  database: database object connected to dbms containing corresponding table
	*  attributes: initial values to fill object
	*
	*  returns: void
	*/
	function __construct(Database $database = null, array $attributes = [])
	{
		parent::__construct();
		
		$this->fillArray($attributes);
	}

	/*
	* Method: fillArray 
	* --------------------
	*  Fills object with provided values using model fill
	*  But before separates specific data from values for Entity
	*
	*  values: database object connected to dbms containing corresponding table
	*
	*  returns: void
	*/
	public function fillArray(array $values) : void
	{
		// Extract primary key
		if(array_key_exists(Static::$primaryKeyName, $values))
		{
			$this->primaryKeyValue = $values[Static::$primaryKeyName];
			unset($values[Static::$primaryKeyName]);
		}

		// Extract deleted at timestamp if present and if softDelete trait if present
		if(Static::hasTrait('SoftDelete') && array_key_exists(Static::$deletedAt, $values))
		{
			$this->deletedAtValue = $values[Static::$deletedAt];
			unset($values[Static::$deletedAt]);
		}

		// Extract created at and update at timestap if present
		if(Static::hasTrait('TimeStamp'))
		{
			if(array_key_exists(Static::$createdAt, $values))
			{
				$this->createdAtValue = $values[Static::$createdAt];
				unset($values[Static::$createdAt]);
			}

			if(array_key_exists(Static::$updatedAt, $values))
			{
				$this->updatedAtValue = $values[Static::$updatedAt];
				unset($values[Static::$updatedAt]);
			}
		}

		// Fill model with rest data
		parent::fillArray($values);
	}

	/*
	* Method: isLoaded 
	* --------------------
	*  Getter method for loaded
	*
	*  returns: boolean
	*/
	public function isLoaded() : bool
	{
		return $loaded;
	}

	/*
	* Method: instatiateObject 
	* --------------------
	*  Creates new instance of object using same dbms connection but custom attributes
	*  Should be used whenever object is loaded from db record
	*
	*  values: database object connected to dbms containing corresponding table
	*
	*  returns: static (this object)
	*/
	public function instatiateObject(array $attributes) : static
	{
		$object = new static(Static::$database, $attributes);
		if(empty($attributes) == false)
			$object->loaded = true;
		
		return $object;
	}

	/*
	* Method: init 
	* --------------------
	*  Method that should be called by subclass in its definition file
	*  Initializes table name, database, extra trais and extracts attributes name
	*
	*  tableName: name of corresponding table, empty string if it should be extracted from class name
	*  database: database object connected to dbms
	*
	*  returns: void
	*/
	public static function init(string $tableName = '', Database $database = null) : void
	{
		Static::initTableName($tableName);
		Static::initDatabase($database);
		Static::initTraits();
		Static::initAttributeKeys();
	}

	/*
	* Method: initTableName 
	* --------------------
	*  This method will initialize table name attribute from parameter
	*  or will call name constructor from class name
	*
	*  tableName: name of corresponding table or empty string
	*
	*  returns: void
	*/
	protected static function initTableName(string $tableName = '') : void
	{
		if(empty($tableName))
			$tableName = Static::defaultTableName();

		Static::$tableName = $tableName;
	}

	/*
	* Method: initDatabase 
	* --------------------
	*  Initializes database connection object
	*  Uses global database if null passed
	*
	*  database: database object or null
	*
	*  returns: void
	*/
	protected static function initDatabase(Database $database = null) : void
	{
		if(is_null($database))
			$database = Database::getGlobalDatabase();

		Static::$database = $database;
	}

	/*
	* Method: initTraits 
	* --------------------
	*  This trait fills traits array with actual traits from inheriting classes
	*  It allows to use extra functionalities dynamically checking if traits are present
	*
	*  returns: void
	*/
	protected static function initTraits() : void
	{
		Static::$traits = class_uses(get_called_class());
	}

	/*
	* Method: initAttributeKeys 
	* --------------------
	*  Extract attributes names from attribute array of model
	*  Used only for convenient operation on dbms
	*
	*  returns: void
	*/
	protected static function initAttributeKeys() : void
	{
		$attributeKeys = array_keys(Static::$attributes);

		Static::$attributeKeys = $attributeKeys;
	}
	
	/*
	* Method: hasTrait 
	* --------------------
	*  Checks whether certain trait is used by class or subclass
	*
	*  trait: name of trait in string
	*
	*  returns: void
	*/
	public static function hasTrait($trait) : void
	{
		return in_array($trait, Static::$traits);
	}

	/*
	* Method: defaultTableName 
	* --------------------
	*  Converts cammel case name of class into snake case name of table
	*
	*  returns: string
	*/
	public static function defaultTableName() : string
	{
		$name = get_called_class();
		$head = strtolower($name[0]);
		return $head.strtolower(preg_replace('/[A-Z]/', '$1_', substr($name, 1)));
	}
}

?>