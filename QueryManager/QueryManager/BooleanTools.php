<?php

	/*
	* Function: processBoolean 
	* --------------------
	*  This methods walks recursively through array
	*  Constructing complete where/having statement
	*  recursive jumps are required to provide brackets and create priorities
	*  for logical expressions
    *  Let x be array of form ['and' => [ 'ton' => [y,..], 'not' => [y,...] ], 'or' => [ 'ton' => [y,...], 'not' => [y,...] ]]
	*  then y must be array of form [value1, comparator, value2] or x
    *
	*  whereArray: Array with defined format x : array
	*
	*  returns: String
	*/
	function processBoolean(array $whereArray) : string
	{
		$ands = [];
		$ors = [];

		// Walk ands and ands not expressions
		// The array is constructed as ['and' => ['ton' => [], 'not' => []]]
		if(key_exists('and', $whereArray))
            foreach($whereArray['and'] as $dir => $exprArr)
            {
                // Walks specific statements [expr1, ...]
                foreach($exprArr as $expr)
                {
                    // array of three elements represents classic logical expression attribute comparator value
                    if(count($expr) == 3)
                        $ands[] = ($dir == 'not' ? 'not ' : '').$expr[0].' '.$expr[1].' '.$expr[2].' ';
                    // otherwise we handle nested where from subquery
                    else
                        $ands[] = '('.processBoolean($expr).')';
                }
            }

		// The same happens for or
		if(key_exists('or', $whereArray))
		foreach($whereArray['or'] as $dir => $exprArr)
		{
			foreach($exprArr as $expr)
			{
				if(count($expr) == 3)
					$ors[] = ($dir == 'not' ? 'not ' : '').$expr[0].' '.$expr[1].' '.$expr[2].' ';
				else
					$ors[] = '('.processBoolean($expr).')';
			}
		}

		// Joins the statements with and priority
		$query = '';
		if(empty($ands) == false)
		{
			$query = implode('and ', $ands);
			if(empty($ors) == false)
				$query .= 'and ('.implode('or ', $ors).')';
		}
		elseif(empty($ors) == false)
			$query = implode('or ', $ors);

		return $query.' ';
	}

	/*
	* Function: whereString 
	* --------------------
	* Interprets whether provided value is string
	* If passess then delimits with apostrophe
	*
	*  comparator: Operator of comparison : String
	*  value: Presented value to compare againt : String
	*
	*  returns: void
	*/
	function whereString($comparator, $value) : mixed
	{
		$comparator = strtoupper($comparator);

		switch($comparator)
		{
		case '=':
		case '<>':
		case 'LIKE':
			if(is_numeric($value))
				return '\''.$value.'\'';
			break;
		}

		return $value;
	}