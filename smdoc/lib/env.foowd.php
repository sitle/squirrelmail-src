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
   * @see smdoc_db
   * @access protected
   */
  var $database;

  /**
   * The user object loaded for this execution cycle.
   *
   * @var object
   * @see base_user
   * @access public
   */
  var $user;

  /**
   * Group Manager for this Foowd environment.
   *
   * @var object
   * @see smdoc_group
   * @access public
   */
   var $groups;

  /**
   * Instance of debug object loaded for this Foowd environment.
   * If left unset (FALSE), debugging is disabled.
   *
   * @var object
   * @see smdoc_debug
   * @access protected
   */
  var $debug = FALSE;

  /**
   * Template object
   *
   * @var object
   * @see foowd_template
   * @access public
   */
  var $template;

  /**
   * Debug tracking wrapper.
   *
   * Calls to this method happen in pairs - on entry to and exit from 
   * a method. 
   * 
   * The entry call should contain at least one parameter specifying
   * the name of the function being called:
   *   <code>$foowd->track('foowd_object::classMethod');</code>
   * 
   * The entry call can also have additional parameters which 
   * will be displayed after the method name:
   *   <code>$foowd->track('foowd_object::classMethod', $methodName);</code>
   *
   * On exit, track should be called with no parameters:
   *   <code>$foowd->track();</code>
   *
   * @param string $functionName Name of the function execution is entering.
   * @param mixed  $v,...        Unlimited number of additional variables to display with debug function.
   * @see foowd::$debug
   * @see smdoc_debug::track()
   */
  function track($functionName = NULL) 
  { 
    if ($this->debug) 
    {
      if ( func_num_args() > 2 || !empty($v) ) 
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
   * Wrapper for calling debug method.
   * Use this method to invoke a method on the foowd debug object.
   * 
   * Examples:
   *   <code>$foowd->debug('msg','Print this debug message');</code>
   *   <code>$foowd->debug('sql',$queryString);</code>
   *
   * @param string $function Name of the debugging function in debug class.
   * @param mixed  $v,...    Unlimited number of additional variables to display with debug function.
   * @see foowd::$debug
   * @see smdoc_debug
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
   * Get name of template for the given class and method. 
   * If a template does not exist for the particular class and method, 
   * look for a template for the class's parent until we either find 
   * a template or reach the base class. 
   * If an existing template is not found. use "default.tpl".
   * 
   * Template names are constructed by concatentating the class
   * and method names, for example:
   *    <code>foowd_object.object_view.tpl</code>
   *    <code>foowd_object.class_create.tpl</code>
   *
   * @param string $className  Name of the class.
   * @param string $methodName Name of the method.
   * @return string Template file name.
   */
  function getTemplateName($className, $methodName) 
  {
    $templateFilename = $className.'.'.$methodName.'.tpl';
    while (!file_exists($this->template->template_dir.$templateFilename)) 
    {
      if ($className == FALSE) 
        trigger_error('Could not load template "'.$this->template->template_dir.$templateFilename.'"', E_USER_ERROR);
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
   * @param array $indexes Array of indexes and values to match
   * @param string $source Source to get object from
   * @return array|NULL The array of selected objects or NULL on failure.
   * @see smdoc_db::getObjHistory()
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
   * Checks if parameter is a class name or an object, and
   * calls:
   *   <code>$className::classMethod($methodName)</code>
   *   <code>$object->method($methodName)</code>
   *
   * @param mixed $classNameOrObject The name of the class or object to call the method on.
   * @param string $methodName       The name of the method to call.
   * @return mixed|FALSE Results of the method call, or FALSE on failure.
   * @see foowd_object::classMethod()
   * @see foowd_object::method()
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
   * Unserialise a serialised object.
   *
   * @param string $serializedObj Serialised object.
   * @param int $classid Classid of the object.
   * @return object
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
   * @param string $className Name of the class to load.
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
 * @param string $className Name of the class trying to be unserialised
 */
function unserializeCallback($className) { foowd::loadDefaultClass($className); }

?>
