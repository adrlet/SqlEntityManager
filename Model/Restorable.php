<?php

	/*
	* Trait:  Restorable 
	* --------------------
	*  Trait for objects that performs upon dynamic list of attributes
	*  Supports state tracking and change reversions
	*  
	*  Attributes:
	*		$original : Array
	*  		$changed : Array
	*		$state : String
	*		$previousState : String
	* 
	*  Methods:
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
trait Restorable
{
	// Holds saved attributes with values
	protected $original = [];

	// Holds modified values of attributes
	protected $changed = [];

	// Holds current state of object, possible states are invalid, modified, exclusive, shared
	protected $state = 'invalid';

	// Holds previous state in case of reversion
	protected $previousState = 'invalid';

	/*
	* Method: getState 
	* --------------------
	*  Returns current state of object
	*
	*  returns: string
	*/
	public function getState() : string
	{
		return $this->state;
	}
	
	/*
	* Method: exists 
	* --------------------
	*  Returns whether object holds dynamic attribute by name
	*
	*  key: Possible name of attribute : int|string
	*
	*  returns: bool
	*/
	public function exists(int|string $key) : bool
	{
		return array_key_exists($key, $this->changed);
	}
	
	/*
	* Method: get 
	* --------------------
	*  Returns value of attribute if exists, otherwise returns null
	*
	*  key: Possible name of attribute : int|string
	*
	*  returns: mixed
	*/
	public function get(string|int $key) : mixed
	{
		return ($this->exists($key) ?
		$this->changed[$key] :
		null);
	}
	
	/*
	* Method: set 
	* --------------------
	*  Sets value of attributes if exists and modifies state
	*
	*  key: Possible name of attribute : int|string
	*
	*  returns: mixed
	*/
	public function set(string|int $key, mixed $value) : void
	{
		if($this->exists($key))
			$this->changed[$key] = $value;
		else
			return;
		
		// Invalid states aren't modified because they aren't saved
		if($this->state == 'shared' || $this->state == 'exclusive')
		{
			$this->previousState = $this->state;
			$this->state = 'modified';
		}
	}

	/*
	* Method: unset 
	* --------------------
	*  Removes attributes if its present
	*
	*  key: Possible name of attribute : int|string
	*
	*  returns: void
	*/
	public function unset(string|int $key) : void
	{
		if($this->exists($key))
			unset($this->changed[$key]);
	}
	
	/*
	* Method: save 
	* --------------------
	*  Updates original attributes using changed attributes
	*  and updates state to exclusive
	*  removes attributes not present in changed
	*
	*  returns: void
	*/
	public function save() : void
	{
		foreach(array_diff_key($this->original, $this->changed) as $key)
			unset($this->original[$key]);

		foreach($this->changed as $key => $value)
			$this->original[$key] = $value;
		
		$this->previousState = $this->state = 'exclusive';
	}
	
	/*
	* Method: revert 
	* --------------------
	*  Updates changed attributes using original attributes
	*  restores values and removes not present keys
	*  the reversal needs modified state, other state disallows restoration
	*
	*  returns: void
	*/
	public function revert() : void
	{
		if($this->state == 'modified')
		{
			foreach(array_diff_key($this->changed, $this->original) as $key)
				unset($this->changed[$key]);

			foreach($this->original as $key => $value)
				$this->changed[$key] = $value;
				
			$this->state = $this->previousState;
		}
	}
	
	/*
	* Method: sharify 
	* --------------------
	*  Marks object as shared, should be used when object is used in many places
	*
	*  returns: void
	*/
	public function sharify() : void
	{
		$this->previousState = $this->state;
		$this->state = 'shared';
	}
	
	/*
	* Method: invalidate 
	* --------------------
	*  Marks object as invalid and removes any attribute
	*  Should be used whenever object source is removed or object is not valid
	*
	*  returns: void
	*/
	public function invalidate() : void
	{
		$this->state = 'invalid';
		foreach($this->original as $key => $value)
			unset($this->original[$key]);
	}
}

?>