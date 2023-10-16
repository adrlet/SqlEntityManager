<?php

/*
* Trait:  SqlTranslator 
* --------------------
*  Common SQL methods for langauge implementations that helps
*  generating query
* 
*  Methods:
*   Internal:
*		protected gueryMethod() : string
*       public aggregationToString() : string
*		public subSelectToString() : string
*
*/
trait SqlTranslator
{
	/*
	* Method: gueryMethod 
	* --------------------
	* Returns query for selected method
	*
	*  returns: String
	*/
	protected function gueryMethod() : string
	{
		switch($this->method)
		{
		case 'SELECT':
			return $this->querySelect();
			break;
			
		case 'INSERT':
			return $this->queryInsert();
			break;
			
		case 'UPDATE':
			return $this->queryUpdate();
			break;
			
		case 'DELETE':
			return $this->queryDelete();
			break;
		
		default:
			throw new Exception(get_class($this).':'.__FUNCTION__.':not supported sql query method');
		}
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
}