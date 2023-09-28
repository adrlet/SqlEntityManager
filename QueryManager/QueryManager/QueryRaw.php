<?php

/*
	* Trait:  QueryRaw 
	* --------------------
	*  Allows to insert direct sql fragments at specified positions of assembled code
	*  The provided fragments are sent as provided
	*  Currently supported positions are method performed upon db,
	*  where section and having section
	*
	*  Attributes:
	*  		$methodRaw : String
	* 		$whereRaw : String
	*		$whereRaw : String
	* 
	*  Methods:
	*  		queryMethodRaw() : string
	*  		addMethodRaw(string $methodRaw) : void
	*		addWhereRaw(string $whereRaw) : void
	*		addHavingRaw(string $havingRaw) : void
	* 
	*/
trait QueryRaw
{
	// The raw and full method specification string, it overrides method
	protected $methodRaw = '';

	// The raw logical expressions for where statement, used alongside where constructor
	protected $whereRaw = '';

	// The raw logical expressions for having statement, used alongisde having constructor
	protected $havingRaw = '';

	/*
	* Function:  queryMethodRaw 
	* --------------------
	*  Strips method name from raw method in order to validate it
	*  Then returns whole raw method
	*
	*  returns: string of raw method
	*/
	protected function queryMethodRaw() : string
	{
		$methodPos = strpos($this->methodRaw, ' ');
		$this->method = strtolower(substr($this->methodRaw, 0, $methodPos));
		return $this->methodRaw;
	}

	/*
	* Function:  addMethodRaw 
	* --------------------
	*  Simply stores provided string as raw method body
	*
	*  methodRaw: Raw method in sql code : string
	*
	*  returns: void
	*/
	public function addMethodRaw(string $methodRaw) : QueryManager
	{
		$this->methodRaw = $methodRaw;
		
		return $this;
	}

	/*
	* Function:  addWhereRaw 
	* --------------------
	*  Simply stores provided string as raw where body
	*
	*  methodRaw: Raw sql code as logical expression : string
	*
	*  returns: void
	*/
	public function addWhereRaw(string $whereRaw) : QueryManager
	{
		$this->whereRaw = $whereRaw;

		return $this;
	}

	/*
	* Function:  addHavingRaw 
	* --------------------
	*  Simply stores provided string as raw having body
	*
	*  methodRaw: Raw code as logical expression : string
	*
	*  returns: void
	*/
	public function addHavingRaw(string $havingRaw) : QueryManager
	{
		$this->havingRaw = $havingRaw;

		return $this;
	}
}

?>