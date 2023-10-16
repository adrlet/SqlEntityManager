<?php

require_once './DateTools.php';

	/*
	* Trait:  TimeStamp 
	* --------------------
	*  Supports timestamping of creation and modification of object
	*  When modification are made from object scope
	*  
	*  Attributes:
	*		$TimeStampDateFormat : String
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
trait TimeStamp
{
	protected static $TimeStampDateFormat = 'Y-m-d H:i:s';

	protected static $createdAt = 'created_at';
	protected $createdAtValue = '';

	protected static $updatedAt = 'updated_at';
	protected $updatedAtValue = '';
	
	protected static function updateTime(& $attributes, & $values, $dateFormat = 'Y-m-d H:i:s')
	{
		$attributes[] = static::$updatedAt;
		foreach($values as $value)
			$value[] = getNow($dateFormat);
	}
	
	protected static function createTime(& $attributes, & $values, $dateFormat = 'Y-m-d H:i:s')
	{
		$attributes[] = static::$createdAt;
		foreach($values as $value)
			$value[] = getNow($dateFormat);
	}
}

?>