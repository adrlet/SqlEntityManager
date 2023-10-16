<?php

	/*
	* Trait:  AdvWhere 
	* --------------------
	*  Extends where statements to make use of callable or object types
	*  In scenario of object it will extract their wheir array and nest it
	*  In case of callable, then the function will return object and go to object scenario
	*
	* 
	*  Methods:
    *   Internal:
    *       protected addWhere(string|array|callable|AdvancedQueryManager $attribute, string $comparator, mixed $value,
	*		string $connector, string $boolean) : void
    *
	*	Interface:
	*  		public where(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*  		public notWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orNotWhere(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public getWhereArray() : Array
	* 
	*/
trait AdvWhere
{
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
				$value[2] = whereString($value[1], $value[2]);
				$this->whereArray[$connector][$boolean][] = $value;
			}
		elseif(is_string($attribute))
		{
			if(is_callable($value))
				$value = call_user_func($value, new Static($this->tableName));

			if(is_a($value, get_class()))
				$this->whereArray[$connector][$boolean][] = [$attribute, $comparator, '('.$value->toString().')'];
			else
				$this->whereArray[$connector][$boolean][] = [$attribute, $comparator, whereString($comparator, $value)];
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
	* Method: getWhereArray 
	* --------------------
	*  Simple getter type method for retrieving whereArray
	*  whereArray represents multidimmensionally current where statement for query
	*
	*  returns: Array
	*/
	public function getWhereArray() : Array
	{
		return $this->whereArray;
	}
}