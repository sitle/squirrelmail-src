<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Manage values added to and retrieved from the session.
 * 
 * $Id$
 * 
 * @package smdoc
 * @subpackage input
 */

/** Include base input library functions and input base class */
require_once(INPUT_DIR . 'input.lib.php');

/**
 * The smdoc input_session class.
 *
 * Used to store/retrieve data from the session.
 *
 * @package smdoc
 * @subpackage input
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
   * @param string  name   The name of the textarea.
   * @param string  regex  The validation regular expression.
   * @param string  value  The initial contents value.
   * @param bool base64 Should the value be base64 encoded in the session.
   */    
  function input_session($name, 
                         $regex = NULL, 
                         $value = NULL,
                         $base64 = false) 
  {
    $this->base64 = $base64;
    $this->name = $name;
    $this->regex = $regex;
    $this->required = FALSE;
    $this->form = NULL;
    $this->default = $value;
    
    $this->refresh();

    if ( !$this->wasSet || !$this->wasValid )
      $this->value = $value;
  }

  /**
   * Refresh values from the session
   */
  function refresh()
  {
    $this->wasSet = sqGetGlobalVar($this->name, $new_value, SQ_SESSION);
    if ( !$this->wasSet )
      return;

    if ( $new_value == NULL || $new_value == ''  )
    {
      $this->wasValid = $this->set(NULL, FALSE);
      return;
    }

    if ( $this->base64 )
      $new_value = unserialize(base64_decode($new_value));

    $this->wasValid = $this->set($new_value, FALSE);
  }

  /**
   * Reset value to default, set default value into session
   */
  function reset()
  {
    $this->remove();    
    parent::reset();
  }
    
  /**
   * Set the value for this object,
   * also set value in session if set_in_session is true.
   * 
   * @param  string  value           The value to set.
   * @param  bool set_in_session  Should value also be set in session
   * @return bool TRUE on success.
   */
  function set($value, $set_in_session = TRUE)
  { 
    if (!$this->verifyData($value) )
        return FALSE;

    if ( $set_in_session ) 
    {
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
   * @param  string  value           The value to verify.
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
