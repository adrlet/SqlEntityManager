<?php

interface EntityBuilder
{
	public function instatiateObject(array $attributes) : static;
}

?>