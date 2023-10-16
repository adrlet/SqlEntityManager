<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Tools/ArrayTools.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/QueryManager.php';

/*
 * Class:  AdvancedQueryManager 
 * --------------------
 *  Extends greatly capabilities of queryManager
 *  The improvements lies in executing statements for arrays of data
 *  If the arrays were already supported then array of arrays is
 *  Adds support for subqueries in selecting and where'ing or having
 *  Allows using callbacks and object instances for subqueries, where'ing and having
 * 
 *  Attributes:
 *  	$mode : String
 *
 *		$from : String
 *		$subSelectArray : Array
 * 
 *  Methods:
 *  	protected static namedValues(array $values) : bool
 * 		public select(array $attributes = []) : QueryManager
 * 		public from(string|AdvancedQueryManager|callable $from, string $as = '') : QueryManager
 * 		public subselect(string|AdvancedQueryManager|callable $subquery, string $as)
 * 		public insert(array $attributes, array $values = []) : QueryManager
 * 		public update(array $attributes, array $values = []) : QueryManager
 * 		public getWhereArray()
 * 		protected addWhere(string|array|callable $attribute, string $comparator, mixed $value,
 *		string $connector, string $boolean) : void
 *		public where(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public notWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public orWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public orNotWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public getHavingArray()
 *		protected addHaving(string|array $attribute, string $comparator, mixed $value,
 *		string $connector, string $boolean) : void
 *		public having(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public notHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public orHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public orNotHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
 *		public exec() : array
 *		public subSelectToString() : string
 *		public toString() : string
 * 		
 */
abstract class AdvancedQueryManager extends QueryManager
{
	use AdvSqlMethods, AdvWhere, AdvHaving;

	// State defining whether AQM is working in multiple array mode and executes for different inputs
	// The states are single (default) and multi
	protected $mode = 'single';

	function __construct(string $tableName, Database $database = null)
	{
		parent::__construct($tableName, $database);
		$this->oldTableName = $tableName;
	}

	public function clear() : void
	{
		parent::clear();
		$this->mode = 'single';
		$this->tableName = $this->oldTableName;
		$this->from = '';
		$this->subSelectArray = [];
	}
	
	/*
	* Method: exec 
	* --------------------
	*  Extends exec from queryManager
	*  Constructs and executes mysql query for simple query or
	*  for dynamic query whose attributes and values changes
	*  May return array of data if method support its and there is data
	*
	*  returns: Array
	*/
	public function exec() : array
	{
		// If method is in single mode, use the queryManager exec
		if($this->mode == 'single')
			return parent::exec();
		
		$data = [];
		$attributes = $this->attributes;
		$values = $this->values;
		for($i = 0; $i < count($attributes); $i++)
		{
			// Update state attributes and values if method support values
			$this->attributes = $attributes[$i];
			if(empty($this->values) == false && $this->method != 'select')
				// insert requires one more dimmension due to specifity in queryManager
				$this->values = ($this->method == 'insert' ? [$values[$i]] : $values[$i]);
			$data[] = parent::exec();
		}
		// Return the state to previous
		$this->attributes = $attributes;
		$this->values = $values;
		
		return $data;
	}
}

?>