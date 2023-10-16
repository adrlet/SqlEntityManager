<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Stringizable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Database/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/Aggregation.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/BooleanTools.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/Having.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/Join.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/QueryRaw.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/SqlBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/SqlMethods.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/Where.php';

/*
 * Class:  QueryManager 
 * --------------------
 *  Abstract class providing API for querying DBMS
 *  The API works by chaining methods that change state of manager
 *  API is generic, it is only making the proper combination of attributes
 *  To generate sql query,
 *  Actual query codes for content are needed by implementing class to be declared
 * 
 *  Attributes:
 *  	$database : Database
 *		$tableName : String
 *		
 *		$whereArray : Array
 *		$orderArray : Array
 *		$orderDesc : Boolean
 *		
 *		$groupAttribute : String
 *		$havingArray : Array
 *		
 *		$limitUp : Int
 *		$limitDown : Int
 *
 *		$joinArray : Array
 * 
 *  Methods:
 * 	 Internal:
 * 		protected unnamedBind($binds)
 * 		protected namedBind($binds)
 * 		protected preparedExec($query) : array
 * 		abstract protected querySelect() : string;
 * 		abstract protected queryInsert() : string;
 * 		abstract protected queryUpdate() : string;
 * 		abstract protected queryDelete() : string;
 * 		abstract protected queryWhere() : string;
 * 		abstract protected queryOrder() : string;
 * 		abstract protected queryGroup() : string;
 * 		abstract protected queryHaving() : string;
 * 		abstract protected queryLimit() : string;
 * 		abstract protected queryJoin();
 *  	__construct(string $tableName, Database $database = null)
 * 		protected gueryMethod() : string
 *      protected joinToString() : string
 * 
 *   Interface:
 *  	public describe();
 * 		public clear() : void
 * 		public order(array $attributes, bool $desc = false) : QueryManager
 * 		public group(string $attribute) : QueryManager
 * 		public limit(int $upLimit, int $downLimit = null) : QueryManager
 * 		public methodToString() : string
 * 		public whereToString() : string
 * 		public groupToString() : string
 * 		public orderToString() : string
 * 		public limitToString() : string
 * 		public joinToString() : string
 * 		public toString() : string
 * 		public exec() : array
 * 		
 */

abstract class QueryManager implements SqlBuilder, Stringizable
{
	use SqlMethods, QueryRaw, Where, Aggregation, Having, Join;
	
	// Active database connection wraper for PDO
	protected $database = null;

	// Base table that query will be performed on
	protected $tableName = '';

	// Array of column names for sorting
	protected $orderArray = [];

	// Specifies the direction of sort
	protected $orderDesc = false;
	
	// Name of column for aggregation
	protected $groupAttribute = '';
	
	// Lower limit for record fetch
	protected $limitUp = null;

	// Upper limit for record fetch
	protected $limitDown = null;
	
	/*
	* Method: __construct 
	* --------------------
	*  QueryManager constructor sets the Database object for connection
	*  and base table that the query will operate on, if database is not provided
	*  global database is used instead
	*
	*  tableName: Base table for queryManager : string
	*  database: Database type object which is required for executing : Database
	*
	*  returns: void
	*/
	function __construct(string $tableName, Database $database = null)
	{
		if(is_null($database))
			$database = Database::getGlobalDatabase();
		
		$this->database = $database;
		$this->tableName = $tableName;
	}
	
	/*
	* Method: describe 
	* --------------------
	*  Function for getting information about table
	*  The information should be as much specific as possible
	*  Ideally there should be integrated method withing DBMS
	*
	*  returns: mixed
	*/
	abstract public function describe();
	
	/*
	* Method: clear 
	* --------------------
	*  Performs reset on queryManager state
	*  Removes any information provided for constructing query
	*  Except for table name
	*
	*  returns: void
	*/
	public function clear() : void
	{
		$this->method = '';

		$this->attributes = [];
		$this->values = [];

		$this->whereArray = [];
		$this->orderArray = [];
		$this->orderDesc = false;

		$this->groupAttribute = '';
		$this->havingArray = [];
		$this->grouped = [];

		$this->limitUp = null;
		$this->limitDown = null;

		$this->methodRaw = '';
		$this->whereRaw = '';
		$this->havingRaw = '';

		$this->joinArray = [];
	}
	
	/*
	* Method: order 
	* --------------------
	*  Check SqlBuilder.php
	*  Orders by specified attributes with specified direction
	*
	*  attributes: Array of attributes to roder with respect to order in array : Array
	*  desc: Should records be ordered descending : Boolean
	*
	*  returns: QueryManager
	*/
	public function order(array $attributes, bool $desc = false) : QueryManager
	{
		$this->orderArray = $attributes;
		$this->orderDesc = $desc;
		
		return $this;
	}
	
	/*
	* Method: group 
	* --------------------
	*  Check SqlBuilder.php
	*  Aggregates records by specified attribute
	*
	*  attribute: Aggregating column name : string
	*
	*  returns: QueryManager
	*/
	public function group(string $attribute) : QueryManager
	{
		$this->groupAttribute = $attribute;
		
		return $this;
	}
	
	/*
	* Method: limit 
	* --------------------
	*  Check SqlBuilder.php
	*  Sets the limit of fetched records for specified number of records
	*  Or for offseted specified number of records
	*
	*  upLimit: Sets the limit of specified records or the offset if downLimit is not null : Int
	*  downLimit: Sets the number of records : Int
	*
	*  returns: QueryManager
	*/
	public function limit(int $upLimit, int $downLimit = null) : QueryManager
	{
		$this->limitUp = $upLimit;
		$this->limitDown = $downLimit;
		
		return $this;
	}

	/*
	* Method: methodToString 
	* --------------------
	*  Acquires the implementation of method in sql code from specific implementation
	*  In form of string
	*
	*  returns: String
	*/
	public function methodToString() : string
	{
		if(empty($this->methodRaw))
			return $this->gueryMethod();
		else
			return $this->queryMethodRaw();
	}

	/*
	* Method: whereToString 
	* --------------------
	*  Acquires the implementation of where in sql code from specific implementation
	*  The acquired string should be constructed from where table or derived from raw where string
	*
	*  returns: String
	*/
	public function whereToString() : string
	{
		if(empty($this->whereRaw) == false)
		{
			if($this->method == 'insert')
				throw new Exception(get_class($this).':'.__FUNCTION__.':insert doesnt support where clausule');

			return ' '.$this->whereRaw;
		}
		elseif(empty($this->whereArray) == false)
		{
			if($this->method == 'insert')
				throw new Exception(get_class($this).':'.__FUNCTION__.':insert doesnt support where clausule');
			
			return $this->queryWhere();
		}

		return '';
	}

	/*
	* Method: groupToString 
	* --------------------
	*  Converts aggregation with having to sql code
	*  The acquired string should be constructed from having table or derived from raw having string
	*
	*  returns: String
	*/
	public function groupToString() : string
	{
		if(empty($this->groupAttribute) == false)
		{
			if($this->method != 'select')
				throw new Exception(get_class($this).':'.__FUNCTION__.':group by clausule supported only by select');
			
			$query = $this->queryGroup();

			if(empty($this->havingRaw) == false)
				$query .= ' '.$this->havingRaw;
			elseif(empty($this->havingArray) == false)
				$query .= ' '.$this->queryHaving();
		}
		else
			return '';

		return $query;
	}

	/*
	* Method: orderToString 
	* --------------------
	*  Converts the ordering statement to sql code
	*
	*  returns: String
	*/
	public function orderToString() : string
	{
		if(empty($this->orderArray) == false)
		{
			if($this->method == 'insert')
				throw new Exception(get_class($this).':'.__FUNCTION__.':insert doesnt support order clausule');
			
			return $this->queryOrder();
		}

		return '';
	}

	/*
	* Method: orderToString 
	* --------------------
	*  Builds limiting statement to string
	*
	*  returns: String
	*/
	public function limitToString() : string
	{
		if(is_null($this->limitUp) == false)
		{
			if($this->method == 'insert')
				throw new Exception(get_class($this).':'.__FUNCTION__.':insert doesnt support limit clausule');
			
			return $this->queryLimit();
		}

		return '';
	}

	/*
	* Method: toString 
	* --------------------
	*  Returns sql query string builded from manager state
	*
	*  returns: String
	*/
	public function toString() : string
	{
		return $this->methodToString().' '.
		$this->joinToString().' '.
		$this->whereToString().' '.
		$this->groupToString().' '.
		$this->orderToString().' '.
		$this->limitToString();
	}
	
	/*
	* Method: exec 
	* --------------------
	*  Sends query constructed from state to DBMS and returns result
	*
	*  returns: Array
	*/
	public function exec() : array
	{
		return $this->preparedExec($this->toString());
	}

	/*
	* Method: unnamedBind 
	* --------------------
	*  Binds the values for unnamed attributes
	*  It should expect to be bounded naturally to order of columns
	*  The returned array holds the unnamed bindings
	*
	*  returns: Array
	*/
	protected function unnamedBind($binds)
	{
		return array_map(function($value, $i) {
			return '?';
		}, $binds, array_keys($binds));
	}

	/*
	* Method: namedBind 
	* --------------------
	*  Binds the values for named and provided attributes
	*  The returned array holds the named bindings
	*
	*  returns: Array
	*/
	protected function namedBind($binds)
	{
		return array_map(function ($key) {
			return ':'.$key;
		}, $binds);
	}
	
	/*
	* Method: preparedExec 
	* --------------------
	*  Joins data and query together by prepared logic
	*  The behaviour differs by method use
	*
	*  returns: Array
	*/
	protected function preparedExec($query) : array
	{
		$pdoConnection = $this->database->getConnection();
		$stmt = $pdoConnection->prepare($query);
		
		$fetchedData = [];
		switch($this->method)
		{
		case 'INSERT':
			$attributesData = [];

			// Non empty attribute array means only some sort of columns are specified
			if(empty($this->attributes) == false)
				$iterated = $this->attributes;
			// Empty attribute array means insert records for all columns in respect to order
			else
				$iterated = array_map(function($id) {
					return $id+1; // Unnamed placehorders are need to be bound by index, this creates index array
				}, array_keys($this->values[0]));


			// Binds parameters for temporal value array
			$i = 0;
			foreach($iterated as $value)
			{
				$attributesData[$i] = null;
				$stmt->bindParam($value, $attributesData[$i]);
				$i++;
			}

			// Executes for every value row
			foreach($this->values as $row)
			{
				$i = 0;
				foreach($row as $value)
					$attributesData[$i++] = $value;
				$stmt->execute();
			}

			break;

		case 'UPDATE':
			$attributesData = [];

			// Update needs named bindings
			$i = 0;
			foreach($this->attributes as $value)
			{
				$attributesData[$i] = null;
				$stmt->bindParam($value, $attributesData[$i]);
				$i++;
			}
			
			// Saves values to temporal binding array and executes
			$i = 0;
			foreach($this->values as $value)
				$attributesData[$i++] = $value;
			$stmt->execute();
			
			break;
			
		case 'SELECT':
			// Executes query and returns result
			$stmt->execute();
			$fetchedData = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			break;
			
		case 'DELETE':
			// Simply executes delete query
			$stmt->execute();
			break;
		}
		
		return $fetchedData;
	}

	/*
	* Method: gueryMethod 
	* --------------------
	* Returns query for selected method
	*
	*  returns: String
	*/
	protected function gueryMethod() : string
	{
		switch($this->method)
		{
		case 'SELECT':
			return $this->querySelect();
			break;
			
		case 'INSERT':
			return $this->queryInsert();
			break;
			
		case 'UPDATE':
			return $this->queryUpdate();
			break;
			
		case 'DELETE':
			return $this->queryDelete();
			break;
		
		default:
			throw new Exception(get_class($this).':'.__FUNCTION__.':not supported sql query method');
		}
	}

	abstract protected function querySelect() : string;
	abstract protected function queryInsert() : string;
	abstract protected function queryUpdate() : string;
	abstract protected function queryDelete() : string;

	/*
	* Method: joinToString 
	* --------------------
	*  Creates sql code for every type and every table of join within join table	
	*
	*  returns: String
	*/
	protected function joinToString() : string
	{
		if(empty($this->joinArray) == false)
		{
			if($this->method != 'select')
				throw new Exception(get_class($this).':'.__FUNCTION__.':left clausule is only supported for select');

			return $this->queryJoin();
		}

		return '';
	}

	abstract protected function queryJoin();
	
	abstract protected function queryWhere() : string;
	abstract protected function queryOrder() : string;
	
	abstract protected function queryGroup() : string;
	abstract protected function queryHaving() : string;
	
	abstract protected function queryLimit() : string;
}

?>