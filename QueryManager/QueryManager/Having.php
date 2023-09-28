<?php

	/*
	* Trait:  Having 
	* --------------------
	*  Handles filtering of SQL aggregation query using logical expressions
	*  Supports adding single comparison expression that are joined together
	*  Using boolean operators: 'and', 'or' and their negations
	*  
	*  Attributes:
	*  		$havingArray : Array
	* 
	*  Methods:
    *   Internal:
    *       protected initHaving() : void
    *       protected addHaving(string|array $attribute, string $comparator, mixed $value,
	*       string $connector, string $boolean) : void
    *
	*	Interface:
	*  		public having(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*  		public notHaving(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orHaving(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	*		public orNotHaving(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	* 
	*/
trait Having
{
    // Multi-dimensional array containing logical expression in form of tree for having statement
	protected $havingArray = [];

    /*
	* Method: initHaving 
	* --------------------
	*  Initializies having table for inserting all types of conditions
	*  Having table can hold nested expression, in braces
	*  At any dimmensional depth
	*
	*  returns: void
	*/
	protected function initHaving() : void
	{
		$this->havingArray = [
			'and' => [ 'ton' => [ ], 'not' => [ ] ],
			'or' =>  [ 'ton' => [ ], 'not' => [ ] ],
		];
	}

	/*
	* Method: addHaving 
	* --------------------
	*  Interprets passed where parameters and adds them into table
	*  The interpretations differs between direct statement or array of statement
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
	protected function addHaving(string|array $attribute, string $comparator, mixed $value,
								 string $connector, string $boolean) : void
	{
		if(empty($this->havingArray))
			$this->initHaving();

		if(is_string($attribute)) // Adds single statement
			$this->havingArray[$connector][$boolean][] = [$attribute, $comparator, whereString($comparator, $value)]; // Adds delimiters for ' if string
		else
			foreach($attribute as $value) // Adds multiple statements
			{
				$value[2] = whereString($value[1], $value[2]);
				$this->havingArray[$connector][$boolean][] = $value;
			}
	}
	
	/*
	* Method: having 
	* --------------------
	*  Check SqlBuilder.php
	*  Joins condition with and
	*
	*  attribute: Name of compared attribute or array of comparision statements : String|Array
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : String
	*
	*  returns: QueryManager
	*/
	public function having(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addHaving($attribute, $comparator, $value, 'and', 'ton');
				
		return $this;
	}
	
	/*
	* Method: notHaving 
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
	public function notHaving(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addHaving($attribute, $comparator, $value, 'and', 'not');
				
		return $this;
	}
	
	/*
	* Method: orHaving 
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
	public function orHaving(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{
		$this->addHaving($attribute, $comparator, $value, 'or', 'ton');
				
		return $this;
	}
	
	/*
	* Method: orNotHaving 
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
	public function orNotHaving(string|array $attribute, string $comparator = '', mixed $value = null) : QueryManager
	{	
		$this->addHaving($attribute, $comparator, $value, 'or', 'not');

		return $this;
	}
}