<?php

	/*
	* Trait:  Where 
	* --------------------
	*  Handles filtering of SQL query using logical expressions
	*  Supports adding single comparison expression that are joined together
	*  Using boolean operators: 'and', 'or' and their negations
	*  
	*  Attributes:
	*  		$whereArray : Array
	* 
	*  Methods:
    *   Internal:
    *       protected initWhere() : void
    *       protected addWhere(string|array $attribute, string $comparator, mixed $value,
	string $connector, string $boolean) : void
    *
	*	Interface:
	*  		public where(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*  		public notWhere(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orWhere(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orNotWhere(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	* 
	*/
trait Where
{
	// Multi-dimensional array containing logical expression in form of tree for where statement
	protected $whereArray = [];

    /*
	* Method: initWhere 
	* --------------------
	*  Initializies where table for inserting all types of conditions
	*  Where table can hold nested expression, in braces
	*  At any dimmensional depth
	*
	*  returns: void
	*/
	protected function initWhere() : void
	{
		$this->whereArray = [
			'and' => [ 'ton' => [ ], 'not' => [ ] ],
			'or' =>  [ 'ton' => [ ], 'not' => [ ] ],
		];
	}

	/*
	* Method: addWhere 
	* --------------------
	*  Interprets passed where parameters and adds them into table
	*  The interpretations differents between direct statement or array of statement
	*  The array should consist of [attribute, comparator, values]
	*
	*  attribute: Name of compared attribute or array of comparision statements : String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : String
	*  connector: The boolean operator for considering statemtn : String
	*  boolean: State whether it should be true or not : String
	*
	*  returns: void
	*/
	protected function addWhere(string|array $attribute, string $comparator, mixed $value,
	string $connector, string $boolean) : void
	{
		if(empty($this->whereArray))
			$this->initWhere();

		if(is_string($attribute))
			$this->whereArray[$connector][$boolean][] = [$attribute, $comparator, whereString($comparator, $value)];
		else
			foreach($attribute as $value)
			{
				$value[2] = whereString($value[1], $value[2]);
				$this->whereArray[$connector][$boolean][] = $value;
			}
	}
	
	/*
	* Method: where 
	* --------------------
	*  Check SqlBuilder.php
	*  Joins condition with and
	*
	*  attribute: Name of compared attribute or array of comparision statements : String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : Mixed
	*
	*  returns: QueryManager
	*/
	public function where(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'and', 'ton');
				
		return $this;
	}
	
	/*
	* Method: notWhere 
	* --------------------
	*  Check SqlBuilder.php
	*  Joins condition with and not
	*
	*  attribute: Name of compared attribute or array of comparision statements : String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : String
	*
	*  returns: QueryManager
	*/
	public function notWhere(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'and', 'not');
				
		return $this;
	}
	
	/*
	* Method: orWhere 
	* --------------------
	*  Check SqlBuilder.php
	*  Joins condition with or
	*
	*  attribute: Name of compared attribute or array of comparision statements : String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : String
	*
	*  returns: QueryManager
	*/
	public function orWhere(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'or', 'ton');
				
		return $this;
	}
	
	/*
	* Method: orWhere 
	* --------------------
	*  Check SqlBuilder.php
	*  Joins condition with or not
	*
	*  attribute: Name of compared attribute or array of comparision statements : String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : String
	*
	*  returns: QueryManager
	*/
	public function orNotWhere(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addWhere($attribute, $comparator, $value, 'or', 'not');
				
		return $this;
	}
}