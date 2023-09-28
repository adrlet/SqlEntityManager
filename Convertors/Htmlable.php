<?php

/*
 * Interface:  Htmlable 
 * --------------------
 *  Provides interface function to cast an object into an html code
 *  The returned html code shall represent the object in any way
 *  The provider wants to
 *
 */
interface Htmlable
{
	/*
	* Function:  toHtml 
	* --------------------
	*  Cast an object into an html entity
	*
	*  returns: Some form consisting of html code : String|Object of class
	*/
	public function toHtml() : string;
}

?>