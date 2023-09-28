<?php

	/*
	* Trait:  Join 
	* --------------------
	*  Basic SQL methods for DML and select statements
	*  Each of method contain specific for it variatons of providing information
	*  
	*  Attributes:
	*  		$joinArray : Array
	* 
	*  Methods:
    *   Internal:
    *       protected initJoin() : void
    *       protected addJoin(string $type, array|string $join) : void
    *       protected joinToString() : string
    *
	*	Interface:
	*  		public join(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	*  		public leftJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	*		public rightJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	*		public crossJoin(string $table) : QueryManager
	* 
	*/
trait Join
{
    // Multi-dimensional array of arrays that contain joined tables, form of joining and comparator for joining
	protected $joinArray = [];

    /*
	* Method: initJoin 
	* --------------------
	*  Initializes the array of join information, each element is specific join type
	*
	*  returns: void
	*/
	protected function initJoin() : void
	{
		$this->joinArray = [
			'inner' => [],
			'left' => [],
			'right' => [],
			'outer' => [],
			'cross' => [],
		];
	}

	/*
	* Method: addJoin 
	* --------------------
	*  Saves join information into join table
	*
	*  type: Join method : String
	*  values: Full expression for joining table or name for cross join : Array|String
	*
	*  returns: void
	*/
	protected function addJoin(string $type, array|string $join) : void
	{
		if(empty($this->joinArray))
			$this->initJoin();

		$this->joinArray[$type][] = $join;
	}

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

	/*
	* Method: join 
	* --------------------
	*  Saves inner join statement for specified table with specified join condition
	*  Inner join requires both records to exists
	*
	*  table: Joined table name : String
	*  columnFirst: First column for comparsion, might be either base or joined table attribute : String
	*  comparator: Comparison operation for columns : String
	*  columnSecond: Second column for comparsion for other side : String
	*
	*  returns: QueryManager
	*/
	public function join(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	{
		$this->addJoin('inner', [$table, $columnFirst, $comparator, $columnSecond]);
		
		return $this;
	}

	/*
	* Method: leftJoin 
	* --------------------
	*  Saves left join statement for specified table with specified join condition
	*  Left join requires base table record to exist
	*
	*  table: Joined table name : String
	*  columnFirst: First column for comparsion, might be either base or joined table attribute : String
	*  comparator: Comparison operation for columns : String
	*  columnSecond: Second column for comparsion for other side : String
	*
	*  returns: QueryManager
	*/
	public function leftJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	{
		$this->addJoin('left', [$table, $columnFirst, $comparator, $columnSecond]);
		
		return $this;
	}

	/*
	* Method: rightJoin 
	* --------------------
	*  Saves right join statement for specified table with specified join condition
	*  Right join requires joined table to exist
	*
	*  table: Joined table name : String
	*  columnFirst: First column for comparsion, might be either base or joined table attribute : String
	*  comparator: Comparison operation for columns : String
	*  columnSecond: Second column for comparsion for other side : String
	*
	*  returns: QueryManager
	*/
	public function rightJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	{
		$this->addJoin('right', [$table, $columnFirst, $comparator, $columnSecond]);
		
		return $this;
	}

    // Deprec
	/*public function outerJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager
	{
		$this->addJoin('outer', [$table, $columnFirst, $comparator, $columnSecond]);
		
		return $this;
	}*/

	/*
	* Method: crossJoin 
	* --------------------
	*  Saves cross join statement for specified table with specified join condition
	*  Cross join works for either existing records
	*
	*  table: Joined table name : String
	*  columnFirst: First column for comparsion, might be either base or joined table attribute : String
	*  comparator: Comparison operation for columns : String
	*  columnSecond: Second column for comparsion for other side : String
	*
	*  returns: QueryManager
	*/
	public function crossJoin(string $table) : QueryManager
	{
		$this->addJoin('cross', $table);
		
		return $this;
	}
}