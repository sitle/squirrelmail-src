<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 *
 */

/**
 * Library for management of user form input.
 * Ensures incoming variables are cleaned up, provides base
 * input class used by other elements for value manipulation.
 * 
 * $Id$
 * 
 * @package smdoc
 * @subpackage input
 */

/*
 * Clean up input
 * -------------------------------------------------------------
 * strip any tags added to the url from PHP_SELF.
 * This fixes hand crafted url XXS expoits for any
 * page that uses PHP_SELF as the FORM action 
 *
 * Also, remove slashes from $_GET and $_POST vars if added 
 */
$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

if (get_magic_quotes_gpc()) {
  sqstripslashes($_GET);
  sqstripslashes($_POST);
}

/**
 * base input element class.
 * Used to over-ride FOOWD elements.
 *
 * @package smdoc
 * @subpackage input
 */
class input_base 
{
  /**
   * The name of the element.
   *
   * @var string
   */
  var $name;

  /**
   * The value of the element.
   *
   * @var string
   */
  var $value;

  /**
   * The regular expression used to validate the objects contents.
   *
   * @var string
   */
  var $regex;

  /**
   * Whether the value was set by form submission
   *
   * @var bool
   */
  var $wasSet = FALSE;

  /**
   * Whether the value set by form submission was correct
   *
   * @var bool
   */
  var $wasValid = FALSE;

  /**
   * Whether or not element is required
   * 
   * @var bool
   */
  var $required;

  /** 
   * Reference to form input object is part of
   * @var input_form
   */
  var $form;

  /**
   * Constructs a new base object.
   *
   * @param string name The name of the form element.
   * @param string regex The validation regular expression.
   * @param string value The initial contents value.
   * @param bool  required is item required
   * @param constant method SQ_GET, SQ_POST, SQ_SESSION, etc.
   */
  function input_base($name, $regex=NULL, $value=NULL, $required=FALSE, $method=SQ_FORM)
  {
    $this->name = $name;
    $this->regex = $regex;
    $this->required = $required;
    $this->form = NULL;
    
    if ( sqGetGlobalVar($name, $new_value, $method) )
    {
      $this->wasSet = TRUE;
      $this->wasValid = $this->set($new_value);
    }
    
    if ( !$this->wasSet || !$this->wasValid )
      $this->value = $value;
  }

  /**
   * Sets the value of the object.
   *
   * @param string value The value to set.
   * @return bool TRUE on success.
   */
  function set($value)
  {
    if ( ($value == NULL && !$this->required) || 
         $this->regex == NULL || 
         preg_match($this->regex, $value) ) 
    {
      $this->value = $value;
      return TRUE;
    }
    return FALSE;
  }
} // end input_base


/**
 * recursively strip slashes from the values of an array 
 * From SquirrelMail 1.4.1 functions/global.php
 * 
 */
function sqstripslashes(&$array) 
{
  if(count($array) > 0) 
  {
    foreach ($array as $index=>$value) 
    {
      if (is_array($array[$index])) 
        sqstripslashes($array[$index]);
      else 
        $array[$index] = stripslashes($value);
    }
  }
}

/**
 * Constants for retrieving user input from 
 * Form, Session, Cookie, or Server global vars.
 */
define('SQ_INORDER',0);
define('SQ_GET',1);
define('SQ_POST',2);
define('SQ_SESSION',3);
define('SQ_COOKIE',4);
define('SQ_SERVER',5);
define('SQ_FORM',6);

/**
 * Search for the var $name in $_SESSION, $_POST, $_GET,
 * $_COOKIE, or $_SERVER and set it in provided var. 
 * From SquirrelMail 1.4.1 functions/global.php
 *
 * If $search is not provided,  or == SQ_INORDER, it will search
 * $_SESSION, then $_POST, then $_GET. Otherwise,
 * use one of the defined constants to look for 
 * a var in one place specifically.
 *
 * Note: $search is an int value equal to one of the 
 * constants defined above.
 *
 * example:
 *    sqgetGlobalVar('username',$username,SQ_SESSION);
 *  -- no quotes around last param!
 *
 * Returns FALSE if variable is not found.
 * Returns TRUE if it is.
 */
function sqGetGlobalVar($name, &$value, $search = SQ_INORDER) 
{
  switch ($search) 
  {
    /* we want the default case to be first here,
     * so that if a valid value isn't specified, 
     * all three arrays will be searched. 
     */
    default:
    case SQ_INORDER: // check session, post, get
    case SQ_SESSION:
      if( isset($_SESSION[$name]) ) {
          $value = $_SESSION[$name];
          return TRUE;
      } elseif ( $search == SQ_SESSION ) {
          break;
      }
    case SQ_FORM:   // check post, get
    case SQ_POST:
      if( isset($_POST[$name]) ) {
        $value = $_POST[$name];
        return TRUE;
      } elseif ( $search == SQ_POST ) {
        break;
      }
    case SQ_GET:
      if ( isset($_GET[$name]) ) {
        $value = $_GET[$name];
        return TRUE;
      } 
      /* NO IF HERE. FOR SQ_INORDER CASE, EXIT after GET */
      break;
    case SQ_COOKIE:
      if ( isset($_COOKIE[$name]) ) {
        $value = $_COOKIE[$name];
        return TRUE; 
      }
      break;
    case SQ_SERVER:
      if ( isset($_SERVER[$name]) ) {
        $value = $_SERVER[$name];
        return TRUE;
      }
      break;
  }
  return FALSE;
}
