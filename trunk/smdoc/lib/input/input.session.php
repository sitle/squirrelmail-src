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

require_once(INPUT_DIR . 'input.lib.php');

/**
 * The SMdoc input_session class.
 *
 * Used to store/retrieve data from the session.
 *
 * @package smdoc/input
 */
class input_session extends input_base
{
  /**
   * Should/Is value base64 encoded in the session
   *
   * @var bool
   * @access private
   */
  var $base64;


  /**
   * Constructs a new textarea object.
   *
   * @param str  name   The name of the textarea.
   * @param str  regex  The validation regular expression.
   * @param str  value  The initial contents value.
   * @param bool base64 Should the value be base64 encoded in the session.
   */    
  function input_session($name, 
                         $regex = NULL, 
                         $value = NULL,
                         $base64 = false) 
  {
    $this->base64 = $base64;
    parent::input_base($name, $regex, $value, FALSE, SQ_SESSION);

    $this->refresh();

    if ( $this->value == NULL && $value != NULL) 
      $this->set($value);
  }

  /**
   * Refresh values from the session
   */
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
    
  /**
   * Set the value for this object,
   * also set value in session if set_in_session is true.
   * 
   * @param  str  value           The value to set.
   * @param  bool set_in_session  Should value also be set in session
   * @return bool TRUE on success.
   */
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

  /**
   * Clear value from session.
   */
  function remove()
  {
    unset($_SESSION[$this->name]);
    $this->value = NULL;
  }
  
  /**
   * Verify value against regex. Will recursively verify array elements.
   * 
   * @access private
   * @param  str  value           The value to verify.
   * @return bool TRUE if value is valid.
   */
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
