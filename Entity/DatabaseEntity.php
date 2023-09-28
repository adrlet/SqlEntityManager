<?php

trait DatabaseEntity
{
	protected static $database = null;
	protected static $tableName = '';
	
	protected static $primaryKeyName = 'id';
	protected static $primaryKeyType = 'int';
	protected static $autoIncrement = true;
	protected $primaryKeyValue = null;
	
	public function getPrimaryKey()
	{
		return ['name' => Static::$primaryKeyName, 'type' => Static::$primaryKeyType, 
				'value' => $this->primaryKeyValue, 'autoIncrement' => Static::$autoIncrement];
	}
	
	public function getTableName()
	{
		return $this->tableName;
	}
}

?>