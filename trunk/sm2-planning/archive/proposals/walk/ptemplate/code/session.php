<?php


// Starts up the session
// Use this, that way we can rewrite it to use whatever type of thing we
// want to.
function StartSession()
{
    session_start();
}


// Registers a function with the session
function Session_Register_Var($varname)
{
    session_register($varname);
}


?>
