<?php

trait SoftDelete
{
	protected static $SoftDeleteDateFormat = 'Y-m-d H:i:s';

	protected static $deletedAt = 'deleted_at';
	protected $deletedAtValue = '';
	
	protected static function excludeThrash($queryManager)
	{
		return $queryManager->where(static::$deletedAt, 'is', 'null');
	}
	
	public function thrash()
	{
		static::update([static::$deletedAt], [getNow(Static::$SoftDeleteDateFormat)])->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
	}
	
	public function retrieve()
	{
		static::update([static::$deletedAt], ['null'])->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
	}
}

?>