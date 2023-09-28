<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Tools/ArrayTools.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager.php';

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
	// State defining whether AQM is working in multiple array mode and executes for different inputs
	// The states are single (default) and multi
	protected $mode = 'single';

	protected $oldTableName = '';
	// From is the actual base source attributes are selected on, it is either table name or subquery with alias
	protected $from = '';
	// Holds the subqueries in array
	protected $subSelectArray = [];

	function __construct(string $tableName, Database $database = null)
	{
		parent::__construct($tableName, $database);
		$this->oldTableName = $tableName;
	}

	public function clear() : void
	{
		parent::clear();
		$this->tableName = $this->oldTableName;
		$this->from = '';
		$this->subSelectArray = [];
	}

	/*
	* Method: namedValues 
	* --------------------
	*  Check whether array holds attribute values with attribute names
	*  The array format should be [[name1 => value1, name2 => value2, ...]]
	*
	*  values: Holds the array of named values or not : array
	*
	*  returns: boolean
	*/
	protected static function namedValues(array $values) : bool
	{
		$key1D = key($values);
		$key2D = key($values[$key1D]);
		if(is_string($key2D))
			return true;
		
		return false;
	}
	
	// wariant 1 atrybuty = []
	// wariant 2 atrybuty = [...]
	// wariant 3 atrybuty = [[...]...]

	/*
	* Method: select 
	* --------------------
	*  Extends select over various attribute selection
	*  The content is either 1D array of attribute names
	*  Or 2D array of arrays with attribute names that can be various
	*
	*  attributes: Empty for all attributes, 1D array for single selection format,
	*  2D array for multiple selection formats : array
	*
	*  returns: QueryManager
	*/
	public function select(array $attributes = []) : QueryManager
	{
		// Multi mode is activated if attribute array is 2D
		$this->mode = (checkArrayDim($attributes) > 1 ? 'multi' : 'single');
		
		return parent::select($attributes);
	}

	/*
	* Method: from 
	* --------------------
	*  Allows to specify base for query data fetching
	*
	*  from: Is string for table name or raw query (in braces), callable to construct query and pass it,
	*  AQM object for query : mixed
	*  as: Should be specified if subquery is provided as the alias name to be used for attributes
	*
	*  returns: QueryManager
	*/
	public function from(string|AdvancedQueryManager|callable $from, string $as = '') : QueryManager
	{
		// Callback should return advanced query manager object
		if(is_callable($from))
			$from = call_user_func($from, new Static($this->tableName));

		// Or the object is provided directly
		if(is_a($from, get_class()))
		{
			$this->from = '('.$from->toString().') ';
			$this->tableName = $as;
		}
		else
			$this->tableName = $from; // or we have direct table name

		return $this;
	}

	/*
	* Method: subselect 
	* --------------------
	*  Adds the subquery to subquery array with alias, it is mostly used in context of where or having statements
	*
	*  subquery: Is string for table name or raw query (in braces), callable to construct query and pass it,
	*  AQM object for query : mixed
	*  as: Should be specified if subquery is provided as the alias name to be used for attributes
	*
	*  returns: QueryManager
	*/
	public function subselect(string|AdvancedQueryManager|callable $subquery, string $as)
	{
		// Callback should return advanced query manager object
		if(is_callable($subquery))
			$subquery = call_user_func($subquery, new Static($this->tableName));
		
		// Or the object is provided directly
		if(is_a($subquery, get_class()))
			$this->subSelectArray[$as] = $subquery->toString();
		else
			$this->subSelectArray[$as] = $subquery; // or we have direct table name

		return $this;
	}
	
	// Wariant 1 atrybuty = atrybuty, wartości = [wartości1, wartości2, ...]
	// Wariant 2 atrybuty = [wartości1, wartości2, ...], $values = []
	// Wariant 3 atrybuty = [[atrybut1 => wartość1, atrybut2 => wartość2, ...], ...], $values = []

	/*
	* Method: insert 
	* --------------------
	*  Extends insert statement to allow specifying different record formula
	*  Supports either attributes named once and multiples values, unnamed multiple values and
	*  Multiple varying named attributes directly specifying value
	*
	*  attributes: Specifies record formula, values for unnamed formula or directly specified values : array
	*  values: Holds values for named attributes : array
	*
	*  returns: QueryManager
	*/
	public function insert(array $attributes, array $values = []) : QueryManager
	{
		/*$this->mode = (checkArrayDim($attributes) > 1 &&
		is_string(key($attributes[0])) ?
		'multi' : 'single');*/
		
		// This checks whether the statement should use multiple mode, for insert
		// it is for 2D array and nammed values
		if(empty($values) && static::namedValues($attributes))
		{
			$this->mode = 'multi';
			$values = array_map('array_values', $attributes);
			$attributes = array_map('array_keys', $attributes);
		}
		else
			$this->mode = 'single';
		
		return parent::insert($attributes, $values);
	}
	
	// Wariant 1 atrybuty = atrybuty, wartości = wartości
	// Wariant 2 atrybuty = [[atrybut1 => wartość1, atrybut2 => wartość2, ...], ...], $values = []

	/*
	* Method: update 
	* --------------------
	*  Extends update statement to allow specifying many updates
	*  Supports either attributes named once and values provided once or multiple names with values
	*
	*  attributes: Holds the name for one update or array of directly specified multiple updates : array
	*  values: Holds values one update : array
	*
	*  returns: QueryManager
	*/
	public function update(array $attributes, array $values = []) : QueryManager
	{
		// This checks whether the statement should use multiple mode, for update
		// it is for 1D
		$this->mode = (checkArrayDim($attributes) > 1 ? 'multi' : 'single');
		
		if(empty($values))
		{
			$values = array_map('array_values', $attributes);
			$attributes = array_map('array_keys', $attributes);
		}
		
		return parent::update($attributes, $values);
	}

	public function delete() : QueryManager
	{
		$this->mode = 'single';
		return parent::delete();
	}

	/*
	* Method: getWhereArray 
	* --------------------
	*  Simple getter type method for retrieving whereArray
	*  whereArray represents multidimmensionally current where statement for query
	*
	*  returns: Array
	*/
	public function getWhereArray()
	{
		return $this->whereArray;
	}

	/*
	* Method: addWhere 
	* --------------------
	*  Extends passing where statements for callable function which represents
	*  object with constructed where statement or
	*  allows to pass callable function as value, to access subquery
	*
	*  attribute: Name of attribute, array of statements or callable function : String|Array|Callable
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable subquery : Mixed
	*  connector: The boolean operator for considering statement : String
	*  boolean: State whether it should be true or not : String
	*
	*  returns: void
	*/
	protected function addWhere(string|array|callable|AdvancedQueryManager $attribute, string $comparator, mixed $value,
	string $connector, string $boolean) : void
	{
		if(empty($this->whereArray))
			$this->initWhere();

		if(is_callable($attribute))
		{
			$whereBuilder = call_user_func($attribute, new Static($this->tableName));
			$this->whereArray[$connector][$boolean][] = $whereBuilder->getWhereArray();
		}
		elseif(is_array($attribute))
			foreach($attribute as $value)
			{
				$value[2] = Static::whereString($value[1], $value[2]);
				$this->whereArray[$connector][$boolean][] = $value;
			}
		elseif(is_string($attribute))
		{
			if(is_callable($value))
				$value = call_user_func($value, new Static($this->tableName));

			if(is_a($value, get_class()))
				$this->whereArray[$connector][$boolean][] = [$attribute, $comparator, '('.$value->toString().')'];
			else
				$this->whereArray[$connector][$boolean][] = [$attribute, $comparator, Static::whereString($comparator, $value)];
		}	
	}

	/*
	* Method: where 
	* --------------------
	*  Extends where from queryManager to pass callables
	*  Joins condition with and
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function where(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'and', 'ton');
				
		return $this;
	}

	/*
	* Method: notWhere 
	* --------------------
	*  Extends notWhere from queryManager to pass callables
	*  Joins condition with and not
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function notWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'and', 'not');
				
		return $this;
	}
	

	/*
	* Method: orWhere 
	* --------------------
	*  Extends orWhere from queryManager to pass callables
	*  Joins condition with or
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function orWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'or', 'ton');
				
		return $this;
	}
	
	/*
	* Method: orNotWhere 
	* --------------------
	*  Extends orNotWhere from queryManager to pass callables
	*  Joins condition with or not
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function orNotWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'or', 'not');
				
		return $this;
	}

	/*
	* Method: getHavingArray 
	* --------------------
	*  Simple getter type method for retrieving havingArray
	*  havingArray represents multidimmensionally current having statement for querys aggregation
	*
	*  returns: Array
	*/
	public function getHavingArray()
	{
		return $this->havingArray;
	}

	/*
	* Method: addHaving 
	* --------------------
	*  Extends passing where statements for callable function which represents
	*  object with constructed where statement or
	*  allows to pass callable function as value, to access subquery
	*
	*  attribute: Name of attribute, array of statements or callable function : String|Array|Callable
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable subquery : Mixed
	*  connector: The boolean operator for considering statement : String
	*  boolean: State whether it should be true or not : String
	*
	*  returns: void
	*/
	protected function addHaving(string|array|callable $attribute, string $comparator, mixed $value,
								 string $connector, string $boolean) : void
	{
		if(empty($this->havingArray))
			$this->initHaving();
		
		if(is_callable($attribute))
		{
			$havingBuilder = call_user_func($attribute, new Static($this->tableName));
			$this->havingArray[$connector][$boolean][] = $havingBuilder->getHavingArray();
		}
		if(is_string($attribute))
			$this->havingArray[$connector][$boolean][] = [$attribute, $comparator,
			(is_string($value) ? '\''.$value.'\'' : $value)];
		else
			foreach($attribute as $value)
				$this->havingArray[$connector][$boolean][] = $value;
	}
	
	/*
	* Method: having 
	* --------------------
	*  Extends having from queryManager to pass callables
	*  Joins condition with and
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function having(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addHaving($attribute, $comparator, $value, 'and', 'ton');
				
		return $this;
	}
	
	/*
	* Method: notHaving 
	* --------------------
	*  Extends notHaving from queryManager to pass callables
	*  Joins condition with and not
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function notHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addHaving($attribute, $comparator, $value, 'and', 'not');
				
		return $this;
	}
	
	/*
	* Method: orHaving 
	* --------------------
	*  Extends orHaving from queryManager to pass callables
	*  Joins condition with or
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function orHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addHaving($attribute, $comparator, $value, 'or', 'ton');
				
		return $this;
	}
	
	/*
	* Method: orNotHaving 
	* --------------------
	*  Extends orNotHaving from queryManager to pass callables
	*  Joins condition with or not
	*
	*  attribute: Name of compared attribute, array of comparision statements  or callable: String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt or callable for subquery : Mixed
	*
	*  returns: QueryManager
	*/
	public function orNotHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{	
		$this->addHaving($attribute, $comparator, $value, 'or', 'not');

		return $this;
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

	/*
	* Method: subSelectToString 
	* --------------------
	*  Creates and returns substring of subqueries for query
	*  The subquery generation is implementation independent because
	*  Subqueries are provided as statement already
	*
	*  returns: String
	*/
	public function subSelectToString() : string
	{
		if(empty($this->subSelectArray) == false)
		{
			return implode(', ', array_map(function($key, $value) {
				// generate subquery in brackets with alias
				return '('.$value.') '.$key;
			}, array_keys($this->subSelectArray), $this->subSelectArray)).' ';
		}

		return '';
	}

	/*
	* Method: toString 
	* --------------------
	*  Extends toString of queryManager for experimental purposes
	*
	*  returns: String
	*/
	public function toString() : string
	{
		return parent::toString();
	}
}

?>