<?php

/*
 * Function:  getNow 
 * --------------------
 *  Retrieves current date-time on host and converts it to string using provided format
 *  
 *  $dateFormat: string representing date format : string
 *
 *  returns: current date-time string with specified format : string
 */

 // OK
function getNow(string $dateFormat = 'Y-m-d H:i:s') : string
{
	// Format current date-time using provided format
	return (new DateTime('now', new DateTimeZone('Europe/Warsaw')))->format($dateFormat);
}

?>