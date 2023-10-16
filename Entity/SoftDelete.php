<?php

	/*
	* Trait:  SoftDelete 
	* --------------------
	*  Supports thrashing records in dbms, without actually removing them
	*  Thrashed record has their deleted at timestamp, and are excluded from queries
	*  in scope of object manipulation
	*  
	*  Attributes:
	*		$SoftDeleteDateFormat : String
	*  		$deletedAt : String
	*		$deletedAtValue : String
	* 
	*  Methods:
	*   Internal:
	*		protected static excludeThrash($queryManager) : MySqlEntityManager
	*	
	*	Interface:
	*		public thrash() : void
	*		public function retrieve() : void
	* 
	*/
trait SoftDelete
{
	// Delete timestamp attribute format in table
	protected static $SoftDeleteDateFormat = 'Y-m-d H:i:s';

	// Name of attribute in table
	protected static $deletedAt = 'deleted_at';

	// The actual timestamp of deleted at, empty if not thrashed
	protected $deletedAtValue = '';
	
	/*
	* Method: excludeThrash 
	* --------------------
	*  Filters thrashed elements for query 
	*
	*  queryManager: queryManager for entity supporting softDelete
	*
	*  returns: MySqlEntityManager
	*/
	protected static function excludeThrash(MySqlEntityManager $queryManager) : MySqlEntityManager
	{
		return $queryManager->where(static::$deletedAt, 'is', 'null');
	}
	
	/*
	* Method: thrash 
	* --------------------
	*  Marks record for loaded object as thrashed 
	*
	*  returns: void
	*/
	public function thrash() : void
	{
		static::update([static::$deletedAt], [getNow(Static::$SoftDeleteDateFormat)])->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
	}
	
	/*
	* Method: retrieve 
	* --------------------
	*  Demarks deleted timestamp from record of object
	*
	*  returns: void
	*/
	public function retrieve() : void
	{
		static::update([static::$deletedAt], ['null'])->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
	}
}

?>