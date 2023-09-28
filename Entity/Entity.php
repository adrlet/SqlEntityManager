<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Model/Model.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/EntityBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/AttributeCast.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/DatabaseEntity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/MySqlEntityManager.php';

abstract class Entity extends Model implements EntityBuilder
{
	use AttributeCast, DatabaseEntity;
	
	protected static $attributeKeys = [];
	protected static $traits = [];
	
	private $loaded = false;

	public function isLoaded()
	{
		return $loaded;
	}

	public static function init(string $tableName = '', Database $database = null)
	{
		Static::initTableName($tableName);
		Static::initDatabase($database);
		Static::initTraits();
		Static::initAttributeKeys();
	}

	protected static function initTableName(string $tableName = '')
	{
		if(empty($tableName))
			$tableName = Static::defaultTableName();

		Static::$tableName = $tableName;
	}

	protected static function initDatabase(Database $database = null)
	{
		if(is_null($database))
			$database = Database::getGlobalDatabase();

		Static::$database = $database;
	}

	protected static function initTraits()
	{
		Static::$traits = class_uses(get_called_class());
	}

	protected static function initAttributeKeys()
	{
		$attributeKeys = array_keys(Static::$attributes);

		Static::$attributeKeys = $attributeKeys;
	}
	
	public static function hasTrait($trait)
	{
		return in_array($trait, Static::$traits);
	}
	
	function __construct(Database $database = null, array $attributes = [])
	{
		parent::__construct();
		
		$this->fillArray($attributes);
	}
	
	public static function defaultTableName()
	{
		$name = get_called_class();
		$head = strtolower($name[0]);
		return $head.strtolower(preg_replace('/[A-Z]/', '$1_', substr($name, 1)));
	}

	public function fillArray(array $values) : void
	{
		if(array_key_exists(Static::$primaryKeyName, $values))
		{
			$this->primaryKeyValue = $values[Static::$primaryKeyName];
			unset($values[Static::$primaryKeyName]);
		}

		if(Static::hasTrait('SoftDelete') && array_key_exists(Static::$deletedAt, $values))
		{
			$this->deletedAtValue = $values[Static::$deletedAt];
			unset($values[Static::$deletedAt]);
		}

		if(Static::hasTrait('TimeStamp'))
		{
			if(array_key_exists(Static::$createdAt, $values))
			{
				$this->createdAtValue = $values[Static::$createdAt];
				unset($values[Static::$createdAt]);
			}

			if(array_key_exists(Static::$updatedAt, $values))
			{
				$this->updatedAtValue = $values[Static::$updatedAt];
				unset($values[Static::$updatedAt]);
			}
		}

		parent::fillArray($values);
	}
	
	public function instatiateObject(array $attributes) : static
	{
		$object = new static(Static::$database, $attributes);
		if(empty($attributes) == false)
			$object->loaded = true;
		
		return $object;
	}
	
	public static function builder()
	{
		return new MySqlEntityManager(static::$tableName,  new static(), static::$database);
	}
	
	public static function select(array $attributes = [])
	{
		$queryManager = Static::builder()->select($attributes);
		if(Static::hasTrait('SoftDelete'))
			$queryManager = static::excludeThrash($queryManager);
		
		return $queryManager;
	}
	
	public static function insert(array $attributes, array $values = [])
	{
		if(empty($values))
		{
			$values = $attributes;
			$attributes = array_keys(Static::$attributes);
		}

		if(static::hasTrait('Timestamp'))
		{
			Static::createTime($attributes, $values);
			Static::updateTime($attributes, $values);
		}
		
		return static::builder()->insert($attributes, $values);
	}
	
	public static function update(array $attributes, array $values = [])
	{	
		if(static::hasTrait('Timestamp'))
			static::updateTime($attributes, $values);
		
		$queryManager = static::builder()->update($attributes, $values);
		if(static::hasTrait('SoftDelete'))
			$queryManager = static::excludeThrash($queryManager);
		
		return $queryManager;
	}
	
	public static function delete()
	{
		return static::builder()->delete();
	}
	
	public static function all()
	{	
		return static::select()->exec();
	}
	
	public static function where(string|array $attribute, string $comparator = '', mixed $value = null)
	{
		return static::select()->where($attribute, $comparator, $value)->exec();
	}
	
	public static function firstWhere(string|array $attribute, string $comparator = '', mixed $value = null)
	{
		return static::select()->where($attribute, $comparator, $value)->order([$attribute], true)->first();
	}
	
	public static function find(mixed $id)
	{
		return static::firstWhere(Static::$primaryKeyName, '=', $id);
	}
	
	public function refresh()
	{
		$this->fillArray(static::firstWhere(Static::$primaryKeyName, '=', $this->primaryKeyValue)->toArray());
		$this->sharify();
	}
	
	public function save() : void
	{
		parent::save();
		
		if($this->loaded == false)
		{	
			if(Static::$autoIncrement)
				static::insert(Static::$attributeKeys, [$this->changed])->exec();
			else
				static::insert(array_merge(Static::$attributeKeys, [Static::$primaryKeyName]), [array_merge($this->changed, [$this->primaryKeyValue])])->exec();
		}
		else
			static::update($this->attributeKeys, [$this->changed])->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
		
		$this->sharify();
	}
	
	public function remove()
	{
		static::delete()->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
		$this->invalidate();
	}
}

?>