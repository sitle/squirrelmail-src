<?php


// Starts up the session
function StartSession()
{
    session_start();
}


// This loads all the standard templates, then loads other templates on top
// of the standard templates
function LoadTemplates()
{
    global $TemplateSet;
    
    // Only allow -_a-zA-Z0-9 in $TemplateSet
    $TemplateSet = ereg_replace("[^-_a-zA-Z0-9]", "", $TemplateSet);
    
    $Templates = LoadTemplatesWork('standard', array());
    if (is_dir("templates/$TemplateSet"))
        $Templates = LoadTemplatesWork($TemplateSet, $Templates);
	
    return $Templates;
}


// This does the work of registering the templates into the template engine
function LoadTemplatesWork($dir, $files_to_define)
{
    if (! is_dir("templates/$dir"))
        return $files_to_define;
	
    $dirhandle = opendir("templates/$dir");
    while (($file = readdir($dirhandle)) !== false)
    {
        if (eregi('\\.bak$', $file) || ereg('~$', $file))
	    continue;
        $newfile = ereg_replace("\\..*", "", $file);
        $files_to_define[$newfile] = "templates/$dir/$file";
    }
    closedir($dirhandle);

    return $files_to_define;
}


function ActionHandler()
{
    global $Page, $User, $Pass, $Action, $Error, $LoggedIN;

    if (! isset($User) && ! isset($Pass))
        return 'login';
	
    if (! isset($User) || $User == "")
    {
        $Error = 'Username is not entered.';
	return 'login';
    }
    
    if (! isset($Pass) || $Pass == "")
    {
        $Error = 'Password is not entered.';
	return 'login';
    }
    
    
    if ($LoggedIN != 1 && $Action != 'login')
        return 'login';
	
    if ($Action == 'login')
    {
        if ($User == 'demo' && $Pass == 'demo')
	{
	    session_register($User);
	    session_register($Pass);
	    session_register($LoggedIN);
	    $LoggedIN = 1;
	    return 'main';
	}
	$Error = 'Username or Password Incorrect';
        return 'login';
    }
    
    if (! is_set($Page))
    {
        return 'login';
    }
}


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
