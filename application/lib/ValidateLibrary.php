<?php
class ValidateLibrary
{   
    public static function check_input($input)
    {           
		if (!empty($input)) {
			$pattern = "([<>;]+)";

			if(!eregi($pattern, $input)) 
			{
				return true;
			}
		}
		return false;
    } 
    
    
}