<?php

require_once './DateTools.php';

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