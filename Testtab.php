<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Entity/Entity.php';

class Testtab extends Entity
{
	protected static $attributes = [
		'name' => 'string',
		'surname' => 'string',
		'age' => 'int'
	];
}

Testtab::init();

?>