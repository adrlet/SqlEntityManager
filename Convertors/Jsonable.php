<?php

/*
 * Interface:  Jsonable 
 * --------------------
 *  Provides interface function to cast an object into an Json
 *  Usually it should include values of attributes
 *
 */
interface Jsonable
{
	/*
	* Function:  toJson	 
	* --------------------
	*  Cast an object into an Json
	*
	*  returns: Json describing attributes of object : String
	*/
	public function toJson() : string;
}

?>