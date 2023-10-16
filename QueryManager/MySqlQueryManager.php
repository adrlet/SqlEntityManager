<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/AdvancedQueryManager.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/QueryManager/SqlTranslator.php';

/*
 * Class:  MySqlQueryManager 
 * --------------------
 *  Implementation of AdvancedQueryManager for MySQL DBMS
 *  It includes methods for translating manager state into sql statement
 *  It may provided MySql specific interfaces
 * 
 * 
 *  Methods:
 * 		public describe() : array
 * 		protected querySelect() : string
 * 		protected queryInsert() : string
 * 		protected queryUpdate() : string
 * 		protected queryDelete() : string
 * 		protected processBoolean(array $whereArray) : string
 * 		protected queryWhere() : string
 * 		protected queryOrder() : string
 * 		protected queryGroup() : string
 * 		protected queryHaving() : string
 * 		protected queryLimit() : string
 * 		protected queryJoin() : string
 * 		
 */
class MySqlQueryManager extends AdvancedQueryManager
{
	use SqlTranslator;

	/*
	* Method: describe 
	* --------------------
	*  Implementation for MySQL DBMS
	*  MySQL has built in describe method that describes the table completely
	*  Returns array of information
	*
	*  returns: Array
	*/
	public function describe() : array
	{
		$pdoConnection = $this->database->getConnection();
		return $pdoConnection->query('DESCRIBE '.$this->tableName)->fetchAll();
	}
	
	/*
	* Method: querySelect 
	* --------------------
	*  Create subquery for select method in mysql
	*  Includes every attribute with prefix
	*  From includes base table with every subquery
	*
	*  returns: String
	*/
	protected function querySelect() : string
	{
		$aggregation = $this->aggregationToString();
		if($aggregation !== '')
			$aggregation = ', '.$aggregation.' ';

		// Maps attributes to attributes with table name prefix
		$prefix = $this->tableName.'.';
		$fullAttributes = [];
		if(empty($this->attributes) == false)
			$fullAttributes = array_map(function($element) use($prefix) {
				//echo $element.' '.(strpos($element, '.') == false).'<br>';
				return (strpos($element, '.') === false ? $prefix.$element : $element);
			}, $this->attributes);
		
		// Checks for no attribute case, includes attributes with prefixes
		// After that joins aggregation methods
		// Then specifies tables, including base table and subqueries
		return 'SELECT '.(empty($this->attributes) ? $prefix.'*' : implode(',', $fullAttributes)).' '.
		$aggregation.
		'FROM '.$this->from.$this->tableName.' '.
		(empty($this->subSelectArray) ? '' :
		','.implode(',', array_map(function ($a, $b) {
			return '('.$a.')'.' '.$b;
		}, $this->subSelectArray, array_keys($this->subSelectArray)))).' ';
	}
	
	/*
	* Method: queryInsert 
	* --------------------
	*  Create subquery for insert method in mysql
	*
	*  returns: String
	*/
	protected function queryInsert() : string
	{	
		// Unnamed bind exists for wholy described records
		if(empty($this->attributes))
			$attributesBind = $this->unnamedBind($this->values[0]);
		else {
			$attributesBind = $this->namedBind(($this->attributes));
		}
		
		return 'INSERT INTO '.$this->tableName.' '.
		(empty($this->attributes) ? '' : '('.implode(',', $this->attributes).') ').
		'VALUES '.'('.implode(',', $attributesBind).') ';
	}
	
	/*
	* Method: queryUpdate 
	* --------------------
	*  Create subquery for update method in mysql
	*
	*  returns: String
	*/
	protected function queryUpdate() : string
	{
		// Supports only named pairs of attribute, value
		$attributesBind = $this->namedBind(($this->attributes));
		$setters = array_map(function($a, $b) {
			return $a.' = '.$b.' ';
		}, $this->attributes, $attributesBind);
		
		return 'UPDATE '.$this->tableName.' '.
		'SET '.implode(',', $setters);
	}
	
	/*
	* Method: queryDelete 
	* --------------------
	*  Create subquery for delete method in mysql
	*
	*  returns: String
	*/
	protected function queryDelete() : string
	{
		return 'DELETE FROM '.$this->tableName.' ';
	}
	
	/*
	* Method: queryWhere 
	* --------------------
	*  Create subquery for where method in mysql
	*
	*  returns: String
	*/
	protected function queryWhere() : string
	{
		return 'WHERE '.processBoolean($this->whereArray);
	}
	
	/*
	* Method: queryOrder 
	* --------------------
	*  Create subquery for order method in mysql
	*
	*  returns: String
	*/
	protected function queryOrder() : string
	{
		return 'ORDER BY '.implode(', ', $this->orderArray).' '.
		($this->orderDesc ? 'DESC ' : 'ASC').' ';
	}
	
	/*
	* Method: queryGroup 
	* --------------------
	*  Create subquery for group method in mysql
	*
	*  returns: String
	*/
	protected function queryGroup() : string
	{
		return 'GROUP BY '.$this->groupAttribute.' ';
	}
	
	/*
	* Method: queryHaving 
	* --------------------
	*  Create subquery for having method in mysql
	*
	*  returns: String
	*/
	protected function queryHaving() : string
	{
		return 'HAVING '.processBoolean($this->havingArray);
	}
	
	/*
	* Method: queryLimit 
	* --------------------
	*  Create subquery for limit method in mysql
	*
	*  returns: String
	*/
	protected function queryLimit() : string
	{
		return 'LIMIT '.$this->limitUp.
		($this->limitDown == null ? ' ' : ', '.$this->limitDown.' ');
	}

	/*
	* Method: queryJoin 
	* --------------------
	*  Create subquery for join method in mysql
	*  Method performs upon joinArray
	*
	*  returns: String
	*/
	protected function queryJoin() : string
	{
		$stringQuery = '';

		// First walks by join type then by specific joins in those types
		foreach($this->joinArray as $joinType => $queryArray)
			foreach($queryArray as $query)
				$stringQuery .= ($joinType == 'CROSS' ?
				'CROSS JOIN '.$query.PHP_EOL :
				$joinType.' JOIN '.$query[0].' ON '.$query[1].' '.$query[2].' '.$query[3].PHP_EOL
				).PHP_EOL;
		
		
		return $stringQuery;
	}
}

?>