<?php

trait Restorable
{
	protected $original = [];
	protected $changed = [];
	protected $state = 'invalid';
	protected $previousState = 'invalid';
	
	public function exists(int|string $key) : bool
	{
		return array_key_exists($key, $this->changed);
	}
	
	public function get(string|int $key) : mixed
	{
		return ($this->exists($key) ?
		$this->changed[$key] :
		null);
	}
	
	public function set(string|int $key, mixed $value) : void
	{
		if($this->exists($key))
			$this->changed[$key] = $value;
		else
			return;
		
		if($this->state == 'shared' || $this->state == 'exclusive')
		{
			$this->previousState = $this->state;
			$this->state = 'modified';
		}
	}
	
	public function save() : void
	{
		foreach($this->changed as $key => $value)
			$this->original[$key] = $value;
		
		$this->previousState = $this->state = 'exclusive';
	}
	
	public function revert() : void
	{
		if($this->state == 'modified')
		{
			foreach($this->original as $key => $value)
				$this->changed[$key] = $value;
				
			$this->state = $this->previousState;
		}
	}
	
	public function sharify() : void
	{
		$this->previousState = $this->state;
		$this->state = 'shared';
	}
	
	public function invalidate() : void
	{
		$this->state = 'invalid';
		foreach($this->original as $key => $value)
			$this->original[$key] = null;
	}
}

?>