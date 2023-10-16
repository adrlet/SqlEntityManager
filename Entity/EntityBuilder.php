<?php

/*
 * Interface:  EntityBuilder 
 * --------------------
 *  Requires class to provide method for creating new instation of it
 *  using current instance
 * 
 * Methods:
 *		public instatiateObject() : QueryManager;
 *
 */
interface EntityBuilder
{
	/*
	* Method: instatiateObject 
	* --------------------
	*  Creates new instance of object using calling object as pattern
	*
	*  attributes: Array of attributes to fill new object : Array
	*
	*  returns: QueryManager
	*/
	public function instatiateObject(array $attributes) : static;
}

?>