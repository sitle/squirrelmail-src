<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
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
 */
class input_base 
{
  /**
   * The name of the element.
   *
   * @attribute str name
   * @class input_base
   */
  var $name;

  /**
   * The value of the element.
   *
   * @attribute str value
   * @class input_base
   */
  var $value;

  /**
   * The regular expression used to validate the objects contents.
   *
   * @attribute str regex
   * @class input_base
   */
  var $regex;

  /**
   * Whether the value was set by form submission
   *
   * @attribute bool wasSet
   * @class input_base
   */
  var $wasSet = FALSE;

  /**
   * Whether or not element is required
   * 
   * @attribute bool required
   * @class input_base
   */
  var $required;

  /**
   * Constructs a new base object.
   *
   * @constructor input_base
   * @param str name The name of the querystring object.
   * @param optional str regex The validation regular expression.
   * @param optional str value The initial contents value.
   * @param optional constant method SQ_GET, SQ_POST, SQ_SESSION, etc.
   * @param optional boolean  required is item required
   */
  function input_base($name, $regex = NULL, $value = NULL, $method = SQ_POST, $required = FALSE)
  {
    $this->name = $name;
    $this->regex = $regex;
    $this->required = $required;
    
    if ( sqGetGlobalVar($name, $new_value, $method) )
    {
      $this->wasSet = TRUE;
      $this->set($new_value);
    }
    else
      $this->value = $value;
  }

  /**
   * Sets the value of the object.
   *
   * @method set
   * @param str value The value to set.
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
    } else {
      return FALSE;
    }
  }
} // end input_base


/**
 * recursively strip slashes from the values of an array 
 * From SquirrelMail 1.4.1 functions/global.php
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
