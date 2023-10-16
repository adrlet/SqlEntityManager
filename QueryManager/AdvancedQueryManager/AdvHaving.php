<?php

trait AdvHaving
{
	/*
	* Trait:  AdvWhere 
	* --------------------
	*  Extends having statements to make use of callable or object types
	*  In scenario of object it will extract their having array and nest it
	*  In case of callable, then the function will return object and go to object scenario
	*
	* 
	*  Methods:
    *   Internal:
    *       protected addHaving(string|array|callable|AdvancedQueryManager $attribute, string $comparator, mixed $value,
	*		string $connector, string $boolean) : void
    *
	*	Interface:
	*  		public having(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*  		public notHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orNotHaving(string|array|callable $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public getHavingArray() : Array
	* 
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
	* Method: getHavingArray 
	* --------------------
	*  Simple getter type method for retrieving havingArray
	*  havingArray represents multidimmensionally current having statement for querys aggregation
	*
	*  returns: Array
	*/
	public function getHavingArray() : Array
	{
		return $this->havingArray;
	}
}