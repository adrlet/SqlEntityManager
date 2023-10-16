<?php

	/*
	* Trait:  AttributeCast 
	* --------------------
	*  Allows to convert in sequence array of attributes with values
	*  Using predefined attribute casts array
	*  Also provides default date format used by object
	*  
	*  Attributes:
	*		$attributesCasts : Array
	*		$datesFormat : String
	* 
	*  Methods:
	*	Internal:
	*		protected convertToAttributeType(int|string $key, mixed $value) : mixed
	* 
	*/
trait AttributeCast
{
	// Is array of attributes representing to what type should attribute be casted
	static protected $attributesCasts = [];

	// Provides common date format object
	static protected $datesFormat = 'Y-m-d H:i:s';
	
	/*
	* Method: convertToAttributeType 
	* --------------------
	*  Casts value using attribue casts array and key and returns it
	*
	*  key: attribute name specified in attributesCasts array
	*  value: value to be casted
	*
	*  returns: mixed
	*/
	protected function convertToAttributeType(int|string $key, mixed $value) : mixed
	{
		switch(Static::$attributesCasts[$key])
		{
		// PHP supports casting between built in types
		case 'int':
			$attribute = (int)$value;
			break;
			
		case 'bool':
			$attribute = (bool)$value;
			break;
			
		case 'float':
			$attribute = (float)$value;
			break;
			
		case 'string':
			$attribute = (string)$value;
			break;
			
		// Dates can be casted from either string or number
		case 'date':
			if(is_string($value))
				$value = strtotime($value);
			
			if(is_int($value) == false)
				$value = 0;
			
			$attribute = date(Static::$datesFormat, $value);
			break;
			
		default:
			break;
		}
		
		return $attribute;
	}
}

?>