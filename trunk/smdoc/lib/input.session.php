<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition to the Framework for Object Orientated Web Development (Foowd).
 *
 * It provides methods for managing groups and tracking permissions to 
 * consolidate operations using groups without using the groups class.
 *
 * $Id$
 */

class input_session 
{
  var $name;                                // session variable name
  var $value = NULL;                        // value of session variable
  var $regex = NULL;                        // regex value must match
    
  function input_session($name, 
                        $regex = NULL, 
                        $value = NULL) 
  {
    $this->name = $name;
    $this->regex = $regex;
    
    if ( isset($_SESSION[$name]) )
      $this->set($_SESSION[$name], FALSE);

    if ( !isset($this->value) && $value != NULL ) 
      $this->set($value);
  }

  function refresh()
  {
    if ( isset($_SESSION[$this->name]) )
      $this->set($_SESSION[$this->name], FALSE);
  }
    
  function set($value, $set_in_session = TRUE)
  {
    if ( $set_in_session ) {
      if ( $this->regex != NULL && $this->verifyData($value) == FALSE ) 
        return FALSE;
        
      $_SESSION[$this->name] = $value;
    }
        
    $this->value = $value;
    return TRUE;
  }
  
  function verifyData($value)
  {
    if ( $value == NULL )
      return TRUE;
      
    if ( !is_array($value) )
      return preg_match($this->regex, $value);
    
    if ( count($value) > 0 )
    {
      foreach( $value as $index => $val )
      {
        $ok = $this->verifyData($val);
        if ( !$ok )
          return FALSE;
      }
    }
    return TRUE;
  }
}

?>