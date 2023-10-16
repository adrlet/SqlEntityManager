<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Database/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/QueryManager/MySqlQueryManager.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Stringizable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Arrayable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Jsonable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Model/Restorable.php';

	/*
	* Trait:  Model 
	* --------------------
	*  Abstract class of predefined object that allows to insert
	*  dynamically attributes, and access them like array
	*  Supports casting to string and array 
	*  
	*  Attributes:
	*		$attributes : Array
	*  		$attributesFillable : Array
	*		$attributesGuarded : Array
	*		$attributesHidden : Array
	* 
	*  Methods:
	*	Internal:
	*		
	*	Interface:
	*		public getState() : string
	*		public exists(int|string $key) : bool
	*		public get(string|int $key) : mixed
	*		public set(string|int $key, mixed $value) : void
	*		public save() : void
	*		public revert() : void
	*		public sharify() : void
	*		public invalidate() : void
	* 
	*/
abstract class Model implements ArrayAccess, Arrayable, Jsonable, Stringizable
{
	use Restorable;
	
	// Array of predefined attribute names, should be specified in inheriting class
	// The names are in key, values represents type of attributes
	protected static $attributes = [];

	// Names of attributes that can be updated with mass fill (fillArray method)
	protected static $attributesFillable = [];

	// Names of attributes that cannot be updated with mass fill
	protected static $attributesGuarded = [];

	// Name of attributes that won't be serialized to json
	protected static $attributesHidden = [];
	
	/*
	* Method: __construct 
	* --------------------
	*  Calls prepareAttributes method upon object creation
	*
	*  returns: void
	*/
	function __construct()
	{
		$this->prepareAttributes();
	}
	
	/*
	* Method: prepareAttributes 
	* --------------------
	*  Validates predefined attributes and construct dynamic
	*  attributes array for restorable trait
	*
	*  returns: void
	*/
	protected function prepareAttributes() : void
	{
		// Iterate over predefined attributes
		foreach(Static::$attributes as $key => $value)
		{
			// Check if predefined attribute has proper type for php 
			if(Static::validateAttributeType($value) == false)
				throw new Exception(get_class($this).':'.__FUNCTION__.':invalid attribute php type');
			
			// Instantiate attributes
			$this->original[$key] = null;
			$this->changed[$key] = null;
		}
	}
	
	/*
	* Method: validateAttributeType 
	* --------------------
	*  Checks whether provided type is valid php type and respected
	*  by this class
	*  Valid types are int, float, bool and string
	*
	*  type: type of attribute
	*
	*  returns: bool
	*/
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
	
	/*
	* Method: offsetGet 
	* --------------------
	*  Overloads array access attribute for retrieving value of key
	*
	*  offset: key
	*
	*  returns: mixed
	*/
	public function offsetGet(mixed $offset) : mixed
	{
		return $this->get($offset);
	}
	
	/*
	* Method: offsetSet 
	* --------------------
	*  Overloads array access attribute for setting value of key
	*
	*  offset: key
	*
	*  returns: void
	*/
	public function offsetSet(mixed $offset, mixed $value) : void
	{
		$this->set($offset, $value);
	}
	
	/*
	* Method: offsetSet 
	* --------------------
	*  Overloads array access attribute for method array has key
	*
	*  offset: key
	*
	*  returns: bool
	*/
	public function offsetExists(mixed $offset) : bool
	{
		return $this->exists($offset);
	}
	
	/*
	* Method: offsetSet 
	* --------------------
	*  Overloads array access attribute for removing key-value pair
	*
	*  offset: key
	*
	*  returns: void
	*/
	public function offsetUnset(mixed $offset) : void
	{
		$this->set($offset, null);
	}
	
	/*
	* Method: fillArray 
	* --------------------
	*  Allows for mass filling model attributes with values
	*  Performs filtering upon fillable and guarded arrays
	*
	*  values: array of pairs attribute (key) : value
	*
	*  returns: void
	*/
	public function fillArray(array $values) : void
	{
		$fillable = Static::fillableFromArray($values);
		$fillable = Static::guardedFromArray($fillable);
		
		foreach($fillable as $key => $value)
			if(Static::validateType($key, $value))
				$this->set($key, $value);
	}

	/*
	* Method: fillableFromArray 
	* --------------------
	*  Returns provided array of attributes with non fillabe attributes removed
	*  if fillable array is empty then do nothing
	*
	*  attributes: array of pairs attribute (key) : value
	*
	*  returns: array
	*/
	protected static function fillableFromArray(array $attributes) : array
	{
		if(count(Static::$attributesFillable) > 0)
			return array_intersect_key(Static::$attributesFillable, $attributes);
		
		return $attributes;
	}

	/*
	* Method: guardedFromArray 
	* --------------------
	*  Returns provided array of attributes with guarded attributes removed
	*  if guarded array is empty then do nothing
	*
	*  attributes: array of pairs attribute (key) : value
	*
	*  returns: array
	*/
	protected static function guardedFromArray(array $attributes) : array
	{
		if(count(Static::$attributesGuarded) > 0)
			return array_diff_key(Static::$attributesGuarded, $attributes);
		
		return $attributes;
	}
	
	/*
	* Method: validateType 
	* --------------------
	*  Performs validation upon assigning value to attribute
	*
	*  key: attribute name to be changed
	*  value: value to be provided
	*
	*  returns: boo
	*/
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
	
	/*
	* Method: toArray 
	* --------------------
	*  Casts object to array, works by returning restorable changed array
	*
	*  returns: array
	*/
	public function toArray() : array
	{
		return $this->changed;
	}
	
	/*
	* Method: toJson 
	* --------------------
	*  Casts object to string of key-value pairs generated from toArray method
	*  Excludes hidden attributes and their values
	*
	*  returns: string
	*/
	public function toJson() : string
	{
		return json_encode(array_diff_key($this->toArray(), Static::$attributesHidden));
	}

	/*
	* Method: toString 
	* --------------------
	*  Casts object to string of key-value pairs generated from toArray method
	*
	*  returns: string
	*/
	public function toString() : string
	{
		return json_encode($this->toArray());
	}
}

?>