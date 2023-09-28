<?php

/*
 * Function:  checkArrayDim 
 * --------------------
 *  Goes through the array dimensions using internal iterator key
 *  and counts them
 *  It may not provide maximum dimensional size, due to use of
 *  randomly placed iterator key
 *  
 *  $array: array of varying dimensions : array
 *
 *  returns: number of probable dimensions, based on iterator key : int
 */

 // OK
function checkArrayDim(array $array) : int
{
    $i = 0;

    // Loop continuing until next nested array exists
    while(is_array($array))
    {
        $i++;
        // Empty array doesn't have keys so this is an exit condition
        if(empty($array))
            break;
        
        // Extract key and go to next dimension
        $key = key($array);
        $array = $array[$key];
    }

    return $i;
}

?>