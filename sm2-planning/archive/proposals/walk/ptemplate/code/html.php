<?php


function getURL()
{
    global $PHP_SELF;
    
    // For non-cookie enabled browsers, etc.
    // insert good code here.  :-)  Like $PHP_SESSIONID or something
    
    $args = func_get_args();
    
    $Extra = "";
    while ($args)
    {
        if ($Extra == "")
	    $Extra = "?";
	else
	    $Extra .= "&";
	    
	$Extra .= urlencode(array_shift($args)) . '=' . 
	    urlencode(array_shift($args));
    }
        
    return $PHP_SELF . $Extra;
}


?>
