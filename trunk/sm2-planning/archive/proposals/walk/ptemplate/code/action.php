<?php

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
	    Session_Register_Var('User');
	    Session_Register_Var('Pass');
	    Session_Register_Var('LoggedIN');
	    Session_Register_Var('TemplateSet');
	    $LoggedIN = 1;
	    return 'main';
	}
	$Error = 'Username or Password Incorrect';
        return 'login';
    }
    
    if (! isset($Page))
    {
        return 'login';
    }
    
    return $Page;
}


?>
