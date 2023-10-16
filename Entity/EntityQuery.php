<?php

/*
	* Trait:  EntityQuery 
	* --------------------
	*  Interface methods to perform sql query upon class paired table
	*  or directly upon object paired record
	*  Defined methods go from basic to complex quuries
	* 
	*  Methods:
	*	Internal:
	*		protected convertToAttributeType(int|string $key, mixed $value) : mixed
	*
	*	Interface:
	*		public static builder() : MySqlEntityManager
	*		public static select(array $attributes = []) : MySqlEntityManager
	*		public static insert(array $attributes, array $values = []) : MySqlEntityManager
	*		public static update(array $attributes, array $values = []) : MySqlEntityManager
	*		public static delete() : MySqlEntityManager
	*		public static all() : array
	*		public static where(string|array $attribute, string $comparator = '', mixed $value = null) : array
	*		public static firstWhere(string|array $attribute, string $comparator = '', mixed $value = null) : static
	*		public static find(mixed $id) : static
	*		public refresh() : void
	*		public save() : void
	*		public remove() : void
	* 
	*/
trait EntityQuery
{	
	/*
	* Method: builder 
	* --------------------
	*  Returns builder for entity providing table name, object instance for calling instantiate
	*  and dbms connection used by class
	*
	*  values: database object connected to dbms containing corresponding table
	*
	*  returns: MySqlEntityManager
	*/
	public static function builder() : MySqlEntityManager
	{
		return new MySqlEntityManager(static::$tableName,  new static(), static::$database);
	}
	
	/*
	* Method: select 
	* --------------------
	*  Performs select upon table of object, if softDelete trait is present
	*  then excludes thrashed records
	*
	*  attributes: array of attributes to fetch
	*
	*  returns: MySqlEntityManager
	*/
	public static function select(array $attributes = []) : MySqlEntityManager
	{
		$queryManager = Static::builder()->select($attributes);
		if(Static::hasTrait('SoftDelete'))
			$queryManager = static::excludeThrash($queryManager);
		
		return $queryManager;
	}
	
	/*
	* Method: insert 
	* --------------------
	*  Insert record using specified attributes and array of values
	*  Adds create and update timestamps if timestamp trait is present
	*
	*  attributes: array of attributes to fill for insert
	*  values: array of array of values in order to elements
	*
	*  returns: MySqlEntityManager
	*/
	public static function insert(array $attributes, array $values = []) : MySqlEntityManager
	{
		if(empty($values))
		{
			$values = $attributes;
			$attributes = array_keys(Static::$attributes);
		}

		if(static::hasTrait('Timestamp'))
		{
			Static::createTime($attributes, $values);
			Static::updateTime($attributes, $values);
		}
		
		return static::builder()->insert($attributes, $values);
	}
	
	/*
	* Method: update 
	* --------------------
	*  Updates record with provided atrribute:value pairs
	*  Also updates timestamp if timestamp trait present
	*  Ignores thrashed elements if softDelete trait is present
	*
	*  attributes: array of attributes to change for update
	*  values: array of values to change
	*
	*  returns: MySqlEntityManager
	*/
	public static function update(array $attributes, array $values = []) : MySqlEntityManager
	{	
		if(static::hasTrait('Timestamp'))
			static::updateTime($attributes, $values);
		
		$queryManager = static::builder()->update($attributes, $values);
		if(static::hasTrait('SoftDelete'))
			$queryManager = static::excludeThrash($queryManager);
		
		return $queryManager;
	}
	
	/*
	* Method: delete 
	* --------------------
	*  Calls delete upon table of class
	*
	*  returns: MySqlEntityManager
	*/
	public static function delete() : MySqlEntityManager
	{
		return static::builder()->delete();
	}
	
	/*
	* Method: all 
	* --------------------
	*  Selects all records of table
	*
	*  returns: array
	*/
	public static function all() : array
	{	
		return static::select()->exec();
	}
	
	/*
	* Method: where 
	* --------------------
	*  Selects all records of table with where filter
	*
	*  attribute: attribute to filter upon
	*  comparator: method of comparison
	*  value: value to compare against
	*
	*  returns: array
	*/
	public static function where(string|array $attribute, string $comparator = '', mixed $value = null) : array
	{
		return static::select()->where($attribute, $comparator, $value)->exec();
	}
	
	/*
	* Method: firstWhere 
	* --------------------
	*  Selects first record of table with where filter
	*
	*  attribute: attribute to filter upon
	*  comparator: method of comparison
	*  value: value to compare against
	*
	*  returns: static
	*/
	public static function firstWhere(string|array $attribute, string $comparator = '', mixed $value = null) : static
	{
		return static::select()->where($attribute, $comparator, $value)->order([$attribute], true)->first();
	}
	
	/*
	* Method: find 
	* --------------------
	*  Selects first record by provided id
	*
	*  id: id of record
	*
	*  returns: static
	*/
	public static function find(mixed $id) : static
	{
		return static::firstWhere(Static::$primaryKeyName, '=', $id);
	}
	
	/*
	* Method: refresh 
	* --------------------
	*  Fetches record from table into object that calls method
	*
	*  returns: void
	*/
	public function refresh() : void
	{
		$this->fillArray(static::firstWhere(Static::$primaryKeyName, '=', $this->primaryKeyValue)->toArray());
		$this->sharify();
	}
	
	/*
	* Method: save 
	* --------------------
	*  Insert object as record into table when it isn't marked as loaded
	*  Otherwise update object with current attributes
	*
	*  returns: void
	*/
	public function save() : void
	{
		parent::save();
		
		if($this->loaded == false)
		{	
			// If record is autoincrement skip primary key, otherwise add it
			if(Static::$autoIncrement)
				static::insert(Static::$attributeKeys, [$this->changed])->exec();
			else
				static::insert(array_merge(Static::$attributeKeys, [Static::$primaryKeyName]), [array_merge($this->changed, [$this->primaryKeyValue])])->exec();
		}
		else
			static::update($this->attributeKeys, [$this->changed])->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
		
		$this->sharify();
	}
	
	/*
	* Method: remove 
	* --------------------
	*  Deletes coresponding record from table using calling object primary key
	*
	*  returns: void
	*/
	public function remove() : void
	{
		static::delete()->where(Static::$primaryKeyName, '=', $this->primaryKeyValue)->exec();
		$this->invalidate();
	}
}