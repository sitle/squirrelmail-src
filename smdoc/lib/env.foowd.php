<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Foowd/smdoc environment
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package Foowd
 */

/**
 * check PHP version
 */
if (version_compare(phpversion(), '4.2.0', '<')) 
  trigger_error('You need PHP version 4.2.0 or greater to run FOOWD, please upgrade', E_USER_ERROR);

/**
 * define regex constants
 */
setConst('REGEX_ID', '/^[0-9-]{1,11}$/');
setConst('REGEX_TITLE', '/^[a-zA-Z0-9-_ ]{1,32}$/');
setConst('REGEX_VERSION', '/^[0-9]*$/');
setConst('REGEX_PASSWORD', '/^[A-Za-z0-9]{4,32}$/');
setConst('REGEX_DATETIME', '/^[0-9-]{1,10}$/');
setConst('REGEX_EMAIL', '/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+\.[A-Za-z]{1,4}$/');
setConst('REGEX_GROUP', '/^[a-zA-Z0-9-]{0,32}$/');

/**
 * The Foowd environment class.
 *
 * Sets up the Foowd environment, including database connection, user group
 * management and user initialisation, and provides methods for accessing
 * objects within the system.
 *
 * @author Paul James
 * @package Foowd
 * @version 0.8.4
 */
class foowd 
{

/* additional settings vars */

  /**
   * The version of this Foowd environment class
   *
   * @var string
   * @access public
   */
  var $version = '0.8.4';

/* foowd object vars */

  /**
   * The database object used in this Foowd environment.
   *
   * @var object
   * @access private
   */
  var $database = FALSE;

  /**
   * The user object loaded for this execution cycle.
   *
   * @var object
   * @access private
   */
  var $user = FALSE;

  /**
   * The debug object loaded for this Foowd environment.
   *
   * @var object
   * @access private
   */
  var $debug = FALSE;

  /**
   * Template object
   *
   * @var object
   * @access private
   */
  var $template = FALSE;

  /**
   * Debug tracking wrapper.
   *
   * Wrapper function to <code>foowd_debug::track</code>
   *
   * @param string functionName Name of the function execution is
   * entering.
   */
  function track($functionName = NULL) 
  { 
    if ($this->debug) 
    {
      if (func_num_args() > 1) 
      {
        $args = func_get_args();
        array_shift($args);  // shift off $function
      } 
      else
        $args = NULL;
      $this->debug->track($functionName, $args);
    }
  }

  /**
   * Debugging wrapper.
   *
   * Wrapper function to <code>foowd_debug::debug</code>
   *
   * @param string function Name of the debugging function in <code>foowd_debug</code> to call.
   */
  function debug($function) 
  { 
    if ($this->debug) 
    {
      if (func_num_args() > 1) 
      {
        $args = func_get_args();
        array_shift($args);  // shift off $function
      }
      else
        $args = NULL;

      call_user_func_array(array(&$this->debug, $function), $args);
    }
  }

  /**
   * Get name of template for the given class and method. If a template does
   * not exist for the particular class and method, we look for a template for
   * the classes parent class until we either find a template or reach the base
   * class. In which case we use load default template "default.tpl".
   *
   * @param string className Name of the class.
   * @param string methodName Name of the method.
   */
  function getTemplateName($className, $methodName) 
  {
    $templateFilename = $className.'.'.$methodName.'.tpl';
    while (!file_exists($this->template->template_dir.$templateFilename)) 
    {
      if ($className == FALSE) 
      {
        trigger_error('Could not load template "'.$this->template->template_dir.$templateFilename.'"', E_USER_ERROR);
        return;
      } 
      elseif ($className == 'foowd_object') 
      {
        $className = FALSE;
        $templateFilename = 'default.tpl';
      } 
      else 
      {
        $className = get_parent_class($className);
        $templateFilename = $className.'.'.$methodName.'.tpl';
      }
    }
    return $templateFilename;
  }

  /**
   * Get all versions of an object.
   *
   * @param array indexes Array of indexes and values to match
   * @param string source Source to get object from
   * @return array The array of selected objects or NULL on failure.
   * @see foowd::getObjHistory
   */
  function &getObjHistory($indexes, $source = NULL) 
  {
    $this->track('foowd->getObjHistory', $indexes);

    $objects = &$this->database->getObjHistory($indexes, $source);

    if ( $objects == NULL &&
         isset($indexes['workspaceid']) && $indexes['workspaceid'] != 0)
    {
      $indexes['workspaceid'] = 0;
      $objects = $this->getObjHistory($indexes); // WARNING: recursion in action
    } 
      
    $this->track(); 
    return $objects;
  }

  /**
   * Call class/object method.
   *
   * Wrapper function for <code>foowd_object::method</code> and
   * <code>foowd_object::classMethod</code>. Checks if parameter is a class
   * name or an object
   *
   * @param mixed classNameOrObject The name of the class or object to call the method upon.
   * @param string methodName The method to call upon the object.
   * @return mixed The array results of the method call or an error string.
   */
  function method(&$classNameOrObject, $methodName = NULL) 
  {
    $this->track('foowd->method', $methodName);

    $result = FALSE;

    // If we have a className
    if (is_string($classNameOrObject)) 
    {
      // If class exists, drive class method
      if ( class_exists($classNameOrObject) ) 
      {
        $result = call_user_func(array($classNameOrObject, 'classMethod'), 
                                 &$this, 
                                 $classNameOrObject, 
                                 $methodName); // call method
      } 
      else 
        trigger_error('Class "'.$classNameOrObject.'" does not exist');
    } 
    // Or, If we have an object
    elseif (is_object($classNameOrObject)) 
      $result = $classNameOrObject->method($methodName);
    // Otherwise, we have something invalid to work with - yuck.
    else
      trigger_error('Invalid class/object specified: ' . $classNameOrObject);

    $this->track(); 
    return $result;
  }

  /**
   * Unserialise object.
   *
   * Unserialise a serialised object loading any classes that are required.
   *
   * @param string serializedObj Serialised object to unserialise.
   * @param int classid Classid of the object to be unserialised.
   * @return object The unserialised object.
   */
  function unserialize($serializedObj, $classid = NULL) 
  {
    return unserialize($serializedObj);
  }

  /**
   * Load default class.
   *
   * Create a child of foowd_object for the given class name so that objects of
   * that type can be loaded without their original class definition.
   *
   * @static
   * @param str className Name of the class to load.
   */
  function loadDefaultClass($className) 
  { 
    setClassMeta($className, _('Unknown Foowd Class'));
    eval('class '.$className.' extends foowd_object {}');
  }

}

// set unserialize callback function
ini_set('unserialize_callback_func', 'unserializeCallback');

/**
 * Object unserialise callback function.
 *
 * Called when an object is unserialised that does not have its class
 * definition loaded. foowd::unserialize() tries to load the required
 * class before unserialisation, but if that fails then we load a
 * default class definition based upon foowd_object.
 *
 * @param string className Name of the class trying to be unserialised
 */
function unserializeCallback($className) { foowd::loadDefaultClass($className); }

?>
