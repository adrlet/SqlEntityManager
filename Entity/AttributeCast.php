<?php

trait AttributeCast
{
	static protected $attributesCasts = [];
	static protected $datesFormat = 'Y-m-d H:i:s';
	
	protected function convertToAttributeType(int|string $key, mixed $value) : mixed
	{
		switch(Static::$attributesCasts[$key])
		{
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