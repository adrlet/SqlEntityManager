<?php

	/*
	* Trait:  SqlMethods 
	* --------------------
	*  Basic SQL methods for DML and select statements
	*  Each of method contain specific for it variatons of providing information
	*  
	*  Attributes:
	*		$method : String
	*  		$attributes : Array
	*		$values : Array
	* 
	*  Methods:
	*	Interface:
	*  		public select(array $attributes = []) : QueryManager
	*  		public insert(array $attributes, array $values = []) : QueryManager
	*		public update(array $attributes, array $values) : QueryManager
	*		public delete() : QueryManager
	* 
	*/
trait SqlMethods
{
	// Method of the query
	protected $method = '';

	// Array of column names that query will operate on
	protected $attributes = [];

	// Values for records if update or insert is performed
	protected $values = [];

    // wariant 1 atrybuty = []
	// wariant 2 atrybuty = [...]
	/*
	* Method: select 
	* --------------------
	*  Check SqlBuilder.php
	*  Fetches records with specified attributes if provided within 1D array
	*  otherwise provides all attributes for records
	*
	*  attributes: Specified attributes to fetch or empty if all : Array
	*
	*  returns: QueryManager
	*/
	public function select(array $attributes = []) : QueryManager
	{
		$this->attributes = $attributes;
		$this->values = [];
		$this->method = 'SELECT';

		return $this;
	}
	
	// Wariant 1 atrybuty = atrybuty, wartości = [wartości1, wartości2, ...]
	// Wariant 2 atrybuty = [wartości1, wartości2, ...], $values = []
	/*
	* Method: insert 
	* --------------------
	*  Check SqlBuilder.php
	*  Insert records with specified formats:
	*  		1.Attributes should be specified in 1D array and values should hold 2D array of records
	*		2.Attributes shall have unnamed values 2D but fully and with respect to order of columns
	*
	*  attributes: Specified columns to insert data for or values for full record insert : Array
	*  values: Values if attributes are named, otherwise Null : Array
	*
	*  returns: QueryManager
	*/
	public function insert(array $attributes, array $values = []) : QueryManager
	{
		if(empty($values))
		{
			$this->attributes = [];
			$this->values = $attributes;
		}
		else
		{
			$this->attributes = $attributes;
			$this->values = $values;
		}
		$this->method = 'INSERT';
		
		return $this;
	}
	
	// Wariant 1 atrybuty = atrybuty, wartości = wartości
	/*
	* Method: update 
	* --------------------
	*  Check SqlBuilder.php
	*  Updates records with new values, both attributes and value shall be provided as 1D arrays
	*
	*  attributes: Specified columns to update : Array
	*  values: Values in the same order as columns : Array
	*
	*  returns: QueryManager
	*/
	public function update(array $attributes, array $values) : QueryManager
	{
		$this->attributes = $attributes;
		$this->values = $values;
		
		$this->method = 'UPDATE';
		
		return $this;
	}
	
	/*
	* Method: delete 
	* --------------------
	*  Check SqlBuilder.php
	*  Deletes records of a table
	*  The scope should be limited with where statement
	*
	*  returns: QueryManager
	*/
	public function delete() : QueryManager
	{
		$this->method = 'DELETE';
		
		return $this;
	}
}