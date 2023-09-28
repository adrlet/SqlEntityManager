<?php

/*
 * Interface:  Stringizable 
 * --------------------
 *  Provides interface function to cast an object into an string
 *  The string shall represent the object
 *
 */
interface Stringizable
{
	/*
	* Function:  toString	 
	* --------------------
	*  Cast an object into an string
	*
	*  returns: String representing an object : String
	*/
	public function toString() : string;
}

?>