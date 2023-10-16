<?php

	/*
	* Trait:  AdvSqlMethods 
	* --------------------
	*  Expands basic methods to handle the case of multiple execeution
	*  Also adds new methods to expand query capabilities by accessing other tables
	*  
	*  Attributes:
	*  		$oldTableName : String
	*		$from : String
	*		$subSelectArray : Array
	* 
	*  Methods:
    *   Internal:
    *       protected static namedValues(array $values) : bool
    *
	*	Interface:
	*  		public select(array $attributes = []) : QueryManager
	*  		public from(string|AdvancedQueryManager|callable $from, string $as = '') : QueryManager
	*		public subselect(string|AdvancedQueryManager|callable $subquery, string $as)
	*		public insert(array $attributes, array $values = []) : QueryManager
	*		public delete() : QueryManager
	* 
	*/
trait AdvSqlMethods
{
    // Restores original table name
	protected $oldTableName = '';
	// From is the actual base source attributes are selected on, it is either table name or subquery with alias
	protected $from = '';
	// Holds the subqueries in array
	protected $subSelectArray = [];

    /*
	* Method: namedValues 
	* --------------------
	*  Check whether array holds attribute values with attribute names
	*  The array format should be [[name1 => value1, name2 => value2, ...], ...]
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
}