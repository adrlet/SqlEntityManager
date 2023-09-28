<?php

/*
* Trait:  Aggregation 
* --------------------
*  Manages aggregation function for queryManager 
*  Allows to add a specific sql aggregation upon attribute
*  And convert it to string
*  List based upon MySQL aggregations: https://dev.mysql.com/doc/refman/8.0/en/aggregate-functions.html
*
*  Attributes:
*  		$grouped : Array
* 
*  Methods:
*   Internal:
*  		protected addToGrouped(string $aggregation, string $attribute) : void
*       public aggregationToString() : string
*
*   Interfaces:
*  		public avg(string $attribute) : queryManager
*		public bitAnd(string $attribute) : queryManager
*		public bitOr(string $attribute) : queryManager
*       public bitXor(string $attribute) : queryManager
*       public count(string $attribute) : queryManager
*       public groupConcat(string $attribute) : queryManager
*       public jsonArrayAgg(string $attribute) : queryManager
*       public jsonObjectAgg(string $attribute) : queryManager
*       public max(string $attribute) : queryManager
*       public min(string $attribute) : queryManager
*       public std(string $attribute) : queryManager
*       public stdDevSamp(string $attribute) : queryManager
*       public sum(string $attribute) : queryManager
*       public variance(string $attribute) : queryManager
*       public varSamp(string $attribute) : queryManager
*
* 
*/
trait Aggregation
{
	// Multi-dimmensional array of aggregating function
    // Each aggregating function is array of attributes upon which the function will be performed
	protected $grouped = [];

	/*
	* Method: addToGrouped 
	* --------------------
	*  Inserts attribute in proper aggregation method
	*  Creates aggregation method array if doesnt exist
	*
	*  aggregation: Name of the aggregation method : string
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: void
	*/
	protected function addToGrouped(string $aggregation, string $attribute) : void
	{
		if(key_exists($aggregation, $this->grouped) == false)
			$this->grouped[$aggregation] = [];
		
		$this->grouped[$aggregation][] = $attribute;
	}

    /*
	* Method: aggregationToString 
	* --------------------
	*  Convert array of aggregation methods with attributes to one string
	*  Adds table name prefix to attributes from base table
	*  Attributes from subqueries should have prefix provided within aggregation method
	*
	*  returns: string
	*/
	//protected function aggregationToString()
	public function aggregationToString() : string
	{
		$prefix = $this->tableName.'.';
		$aggrString = '';

        // Check each method
		foreach($this->grouped as $method => $aggrArr)
		{
            // Add prefix to attribute and wrap it within method
			$prefixed = array_map(function($attribute) use($prefix, $method)
			{
                // This strpos is used assuming that attributes from other than base table
                // has been provided with table prefixes, otherwise it will mostly fail
				return $method.'('.
				(strpos($attribute, '.') === false ? $prefix.$attribute : $attribute).
				')';
			}, $aggrArr);

            // Concatenate string
			$aggrString .= implode(', ', $prefixed);
		}

		return $aggrString;
	}

	/*
	* Method: avg 
	* --------------------
	*  Mysql average aggregation function 
	*  This function computes average of provided attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function avg(string $attribute) : queryManager
	{
		$this->addToGrouped('avg', $attribute);

		return $this;
	}

	/*
	* Method: bitAnd 
	* --------------------
	*  Mysql binary and aggregation function 
	*  This function performs binary and operation on provided attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function bitAnd(string $attribute) : queryManager
	{
		$this->addToGrouped('bit_and', $attribute);

		return $this;
	}

	/*
	* Method: bitOr 
	* --------------------
	*  Mysql binary or aggregation function 
	*  This function performs binary or operation on provided attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function bitOr(string $attribute) : queryManager
	{
		$this->addToGrouped('bit_or', $attribute);

		return $this;
	}

	/*
	* Method: bitXor 
	* --------------------
	*  Mysql binary xor aggregation function 
	*  This function performs binary xor operation on provided attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function bitXor(string $attribute) : queryManager
	{
		$this->addToGrouped('bit_xor', $attribute);

		return $this;
	}

	/*
	* Method: count 
	* --------------------
	*  Mysql count aggregation function 
	*  This function counts number of records for attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function count(string $attribute) : queryManager
	{
		$this->addToGrouped('count', $attribute);

		return $this;
	}

	/*
	* Method: groupConcat 
	* --------------------
	*  Mysql group concat aggregation function 
	*  This function concatenates attribute values
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function groupConcat(string $attribute) : queryManager
	{
		$this->addToGrouped('group_concat', $attribute);

		return $this;
	}

	/*
	* Method: jsonArrayAgg
	* --------------------
	*  Mysql group json array agg aggregation function 
	*  This function concatenates into array of attribute records
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function jsonArrayAgg(string $attribute) : queryManager
	{
		$this->addToGrouped('json_array_agg', $attribute);

		return $this;
	}

	/*
	* Method: jsonObjectAgg 
	* --------------------
	*  Mysql group json object agg aggregation function 
	*  This function concatenates into json objects, pairs of key : value
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function jsonObjectAgg(string $attribute) : queryManager
	{
		$this->addToGrouped('json_object_agg', $attribute);

		return $this;
	}

	/*
	* Method: max 
	* --------------------
	*  Mysql group max aggregation function 
	*  This function return max for aggregated attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function max(string $attribute) : queryManager
	{
		$this->addToGrouped('max', $attribute);

		return $this;
	}

	/*
	* Method: min 
	* --------------------
	*  Mysql group min aggregation function 
	*  This function return min for aggregated attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function min(string $attribute) : queryManager
	{
		$this->addToGrouped('min', $attribute);

		return $this;
	}

	/*
	* Method: std 
	* --------------------
	*  Mysql group std aggregation function 
	*  This function return standard devation for values of attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function std(string $attribute) : queryManager
	{
		$this->addToGrouped('std', $attribute);

		return $this;
	}

	/*
	* Method: stdDevSamp 
	* --------------------
	*  Mysql group std dev samp aggregation function 
	*  This function return standard devation sample for values of attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function stdDevSamp(string $attribute) : queryManager
	{
		$this->addToGrouped('std_dev_samp', $attribute);

		return $this;
	}

	/*
	* Method: sum 
	* --------------------
	*  Mysql group sum aggregation function 
	*  This function return sum of attributes
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function sum(string $attribute) : queryManager
	{
		$this->addToGrouped('sum', $attribute);

		return $this;
	}

	/*
	* Method: variance 
	* --------------------
	*  Mysql group variance aggregation function 
	*  This function return standard variance over values
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function variance(string $attribute) : queryManager
	{
		$this->addToGrouped('variance', $attribute);

		return $this;
	}

	/*
	* Method: varSamp 
	* --------------------
	*  Mysql group var samp function 
	*  This function return population standard variance for values of attribute
    *  If you use attribute not from base table, then full name should provided
    *  which consits of tablename concatenated with attribute name using dot
	*
	*  attribute: the full attribute name that aggregation works upon : string
	*
	*  returns: queryManager
	*/
	public function varSamp(string $attribute) : queryManager
	{
		$this->addToGrouped('var_samp', $attribute);

		return $this;
	}
}