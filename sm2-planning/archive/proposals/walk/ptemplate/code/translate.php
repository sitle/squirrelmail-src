<?php

$TranslateStrings['funky']['Options'] = 'Thingies';

function trans()
{
    global $TranslateLanguage;
    
    $args = func_get_args();
    $Str = $args[0];
    if (isset($TranslateStrings[$TranslateLanguage][$args[0]]))
        $Str = $TranslateStrings[$TranslateLanguage][$args[0]];
	
    // Change $1 through $9 into useable values
    $Parts = explode("$", $Str);
    $Str = array_shift($Parts);
    while ($Parts)
    {
        if ($Parts[0] == '')
	    $Str .= '$';
	elseif (ereg("^([1-9])(.*)$", $Parts[0], $Matches))
	{
	    $Str .= $args[$Matches[1]];
	    $Str .= $Matches[2];
	}
	else
	{
	    $Str .= '$' . $Parts[0];
	}
	array_shift($Parts);
    }
    
    return $Str;
}

?>
