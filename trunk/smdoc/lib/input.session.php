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

class input_session extends input_base
{
  var $base64;                              // should/is value be base64 encoded
    
  function input_session($name, 
                         $regex = NULL, 
                         $value = NULL,
                         $base64 = false) 
  {
    $this->base64 = $base64;
    parent::input_base($name, $regex, $value, SQ_SESSION);

    $this->base64 = $base64;
    
    $this->refresh();

    if ( $this->value == NULL && $value != NULL) 
      $this->set($value);
  }

  function refresh()
  {
    if ( !isset($_SESSION[$this->name]) )
        return;

    if ( $_SESSION[$this->name] == NULL || $_SESSION[$this->name] == ''  )
      $this->set(NULL, FALSE);

    elseif ( $this->base64 )
      $new_value = unserialize(base64_decode($_SESSION[$this->name]));
    else
      $new_value = $_SESSION[$this->name];

    $this->set($new_value, FALSE);
  }
    
  function set($value, $set_in_session = TRUE)
  { 
    if (!$this->verifyData($value) )
        return FALSE;

    if ( $set_in_session ) {
      if ( $this->base64 ) 
        $_SESSION[$this->name] = base64_encode(serialize($value));
      else
        $_SESSION[$this->name] = $value;
    }
        
    $this->value = $value;
    return TRUE;
  }

  function remove()
  {
    unset($_SESSION[$this->name]);
    $this->value = NULL;
  }
  
  function verifyData($value)
  {
    if ( $value == NULL || $this->regex == NULL )
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
