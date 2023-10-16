<?php

/*
 * Interface:  SqlBuilder 
 * --------------------
 *  Provides interface functions for common dbms queries
 *  Uncommon queries/methods should be interfaced for specific implementations
 *  Those methods are state-modifying and returns object for chain calling
 *  Except the exec methods which sends the query based on state
 * 
 * Methods:
 *		public select() : QueryManager;
 *		public insert(array $values) : QueryManager;
 *		public update(array $attributes, array $values) : QueryManager;
 *		public delete() : QueryManager;
 *
 *		public order(array $attributes, bool $desc) : QueryManager;
 *		public where(string $attribute, string $comparator, mixed $value) : QueryManager;
 *		public orWhere(string $attribute, string $comparator, mixed $value) : QueryManager;
 *		public notWhere(string $attribute, string $comparator, mixed $value) : QueryManager;
 *		public orNotWhere(string $attribute, string $comparator, mixed $value) : QueryManager;
 *
 *		public group(string $attribute) : QueryManager;
 *		public having(string $attribute, string $comparator, mixed $value) : QueryManager;
 *		public orHaving(string $attribute, string $comparator, mixed $value) : QueryManager;
 *		public notHaving(string $attribute, string $comparator, mixed $value) : QueryManager;
 *		public orNotHaving(string $attribute, string $comparator, mixed $value) : QueryManager;
 *
 *		public limit(int $limitUp, int $limitDown) : QueryManager;
 *
 *		public join(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager;
 *		public leftJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager;
 *		public rightJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager;
 *		public crossJoin(string $table) : QueryManager;
 *
 *		public exec() : array;
 *
 */
interface SqlBuilder
{
	/*
	* Function:  select 
	* --------------------
	*  Interface function for fetching data from tables
	*  The form of selection is dependant upon implementation
	*
	*  returns: the object of called method
	*/
	public function select() : QueryManager;

	/*
	* Function:  insert 
	* --------------------
	*  Interface function for inserting records to table
	*  The form of insert is dependant upon implementation
	*  But it requires to provide values in parameters
	*
	*  values: Provided attribute values to insert : array
	*
	*  returns: the object of called method
	*/
	public function insert(array $values) : QueryManager;

	/*
	* Function:  update 
	* --------------------
	*  Interface function for updating data inside table
	*  The form of update is dependant upon implementation
	*  But it requires to provide new values in parameters
	*
	*  values: Provided attribute values to update : array
	*
	*  returns: the object of called method
	*/
	public function update(array $attributes, array $values) : QueryManager;

	/*
	* Function:  delete 
	* --------------------
	*  Interface function for delete records inside table
	*  The form of delete is dependant upon implementation
	*
	*  returns: the object of called method
	*/
	public function delete() : QueryManager;
	
	/*
	* Function:  order 
	* --------------------
	*  Interface function for ordering fetched data records
	*  The order requires direction and attribute for comparison
	*
	*  attributes: Column names to sort upon : array
	*  desc: Specifies direction of ordering : bool
	*
	*  returns: the object of called method
	*/
	public function order(array $attributes, bool $desc) : QueryManager;
	
	/*
	* Function:  where 
	* --------------------
	*  Interface function for filtering fetched data records
	*  The where function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type and
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function where(string $attribute, string $comparator, mixed $value) : QueryManager;

	/*
	* Function:  orWhere
 
	* --------------------
	*  Interface function for filtering fetched data records
	*  The where function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type or
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function orWhere(string $attribute, string $comparator, mixed $value) : QueryManager;

	/*
	* Function:  notWhere
 
	* --------------------
	*  Interface function for filtering fetched data records
	*  The where function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type not and
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function notWhere(string $attribute, string $comparator, mixed $value) : QueryManager;

	/*
	* Function:  orNotWhere
 
	* --------------------
	*  Interface function for filtering fetched data records
	*  The where function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type not or
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function orNotWhere(string $attribute, string $comparator, mixed $value) : QueryManager;
	
	/*
	* Function:  group
 
	* --------------------
	*  Interface function for aggregating fetched data records
	*  The group function specifies attributes upon which aggregation is performed
	*
	*  attribute: Column name to group against : string
	*
	*  returns: the object of called method
	*/
	public function group(string $attribute) : QueryManager;
	
	/*
	* Function:  having
 
	* --------------------
	*  Interface function for filtering aggregated data records
	*  The having function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type and
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function having(string $attribute, string $comparator, mixed $value) : QueryManager;

	/*
	* Function:  orHaving
 
	* --------------------
	*  Interface function for filtering aggregated data records
	*  The having function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type or
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function orHaving(string $attribute, string $comparator, mixed $value) : QueryManager;

	/*
	* Function:  notHaving
 
	* --------------------
	*  Interface function for filtering aggregated data records
	*  The having function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type not and
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function notHaving(string $attribute, string $comparator, mixed $value) : QueryManager;

	/*
	* Function:  orNotHaving
 
	* --------------------
	*  Interface function for filtering aggregated data records
	*  The having function requires the attribute base, method of comparison and value for comparison
	*  Specified filter is of type not or
	*
	*  attribute: Compared column name : string
	*  comparator: The operator of comparison : string
	*  value: The value to compare against : mixed
	*
	*  returns: the object of called method
	*/
	public function orNotHaving(string $attribute, string $comparator, mixed $value) : QueryManager;
	
	/*
	* Function:  limit
 
	* --------------------
	*  Interface function for limiting the data by range scope
	*  The limit function requires the upper and down bound of data
	*
	*  limitUp: The maximum limit of records or the offset : int
	*  limitDown: The maximum limit of records : int
	*
	*  returns: the object of called method
	*/
	public function limit(int $limitUp, int $limitDown) : QueryManager;

	/*
	* Function:  join
 
	* --------------------
	*  Interface function for joing table data together
	*  Requirements are the joined table, comparison column from base table, comparison method and comparison method from joined table
	*  This type of join is inner, so both records must be available
	*
	*  table: The joined table : String
	*  columnFirst: The name of column of base table to compare against : String
	*  comparator: The operator of comparison : string
	*  columnSecond: The name of column of joined table to compare against : String
	*
	*  returns: the object of called method
	*/
	public function join(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager;

	/*
	* Function:  leftJoin
 
	* --------------------
	*  Interface function for joing table data together
	*  Requirements are the joined table, comparison column from base table, comparison method and comparison method from joined table
	*  This type of join is base oriented, so record from base table are preserved
	*
	*  table: The joined table : String
	*  columnFirst: The name of column of base table to compare against : String
	*  comparator: The operator of comparison : string
	*  columnSecond: The name of column of joined table to compare against : String
	*
	*  returns: the object of called method
	*/
	public function leftJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager;

	/*
	* Function:  rightJoin
 
	* --------------------
	*  Interface function for joing table data together
	*  Requirements are the joined table, comparison column from base table, comparison method and comparison method from joined table
	*  This type of join is join oriented, so record from joined table are preserved
	*
	*  table: The joined table : String
	*  columnFirst: The name of column of base table to compare against : String
	*  comparator: The operator of comparison : string
	*  columnSecond: The name of column of joined table to compare against : String
	*
	*  returns: the object of called method
	*/
	public function rightJoin(string $table, string $columnFirst, string $comparator, string $columnSecond) : QueryManager;

	/*
	* Function:  crossJoin
 
	* --------------------
	*  Interface function for joing table data together
	*  Requirements are the joined table, comparison column from base table, comparison method and comparison method from joined table
	*  This type of join is outer, so record from both tables are preserved
	*
	*  table: The joined table : String
	*
	*  returns: the object of called method
	*/
	public function crossJoin(string $table) : QueryManager;
	
	/*
	* Function:  exec
 
	* --------------------
	*  Interface function for executing query builded from the state of object
	*  The result is based upon use of state-modifying methods
	*  If the query fetches data functions returns this data
	*  Otherwise it gives empty array
	*
	*  returns: array
	*/
	public function exec() : array;
}

?>