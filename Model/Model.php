<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Database/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/MySqlQueryManager.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Stringizable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Arrayable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Jsonable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Model/Restorable.php';

abstract class Model implements ArrayAccess, Arrayable, Jsonable, Stringizable
{
	use Restorable;
	
	protected static $attributes = [];
	protected static $attributesFillable = [];
	protected static $attributesGuarded = [];
	protected static $attributesHidden = [];
	
	function __construct()
	{
		$this->prepareAttributes();
	}
	
	protected function prepareAttributes() : void
	{
		foreach(Static::$attributes as $key => $value)
		{
			if(Static::validateAttributeType($value) == false)
				throw new Exception(get_class($this).':'.__FUNCTION__.':invalid attribute php type');
			
			$this->original[$key] = null;
			$this->changed[$key] = null;
		}
	}
	
	protected static function validateAttributeType($type) : bool
	{
		switch($type)
		{
		case 'int':
		case 'float':
		case 'bool':
		case 'string':
			return true;
			
		default:
			return false;
		}
	}
	
	public function offsetGet(mixed $offset) : mixed
	{
		return $this->get($offset);
	}
	
	public function offsetSet(mixed $offset, mixed $value) : void
	{
		$this->set($offset, $value);
	}
	
	public function offsetExists(mixed $offset) : bool
	{
		return $this->exists($offset);
	}
	
	public function offsetUnset(mixed $offset) : void
	{
		$this->set($offset, null);
	}
	
	public function fillArray(array $values) : void
	{
		$fillable = Static::fillableFromArray($values);
		$fillable = Static::guardedFromArray($fillable);
		
		foreach($fillable as $key => $value)
			if(Static::validateType($key, $value))
				$this->set($key, $value);
	}
	
	protected static function validateType(string|int $key, mixed $value) : bool
	{
		$type = gettype($value);
		
		switch(Static::$attributes[$key])
		{
		case 'int':
			if($type == 'integer')
				return true;
			break;
			
		case 'string':
			if($type == 'string')
				return true;
			break;
			
		case 'float':
			if($type == 'double')
				return true;
			break;
			
		case 'bool':
			if($type == 'boolean')
				return true;
			break;
			
		default:
			break;
		}
		
		return false;
	}
	
	protected static function guardedFromArray(array $attributes) : array
	{
		if(count(Static::$attributesGuarded) > 0)
			return array_diff_key(Static::$attributesGuarded, $attributes);
		
		return $attributes;
	}
	
	protected static function fillableFromArray(array $attributes) : array
	{
		if(count(Static::$attributesFillable) > 0)
			return array_intersect_key(Static::$attributesFillable, $attributes);
		
		return $attributes;
	}
	
	public function toArray() : array
	{
		return $this->changed;
	}
	
	public function toJson() : string
	{
		return json_encode(array_diff_key($this->toArray(), Static::$attributesHidden));
	}

	public function toString() : string
	{
		return json_encode($this->toArray());
	}
}

?>