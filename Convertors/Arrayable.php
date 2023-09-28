<?php

/*
 * Interface:  Arrayable 
 * --------------------
 *  Provides interface function to cast an object into an array
 *
 */
interface Arrayable
{
	/*
	* Function:  toArray
	* --------------------
	*  Cast an object into an array
	*
	*  returns: Array representing attribute values of object : array
	*/
	public function toArray() : array;
}

?>