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

/*
 * Modified by SquirrelMail Development
 * $Id$
 */

/*
env.foowd.php
Foowd environment class
*/

// check PHP version
if (version_compare(phpversion(), '4.2.0', '<')) trigger_error('You need PHP version 4.2.0 or greater to run FOOWD, please upgrade', E_USER_ERROR);

// include foowd lib
require_once(SM_DIR.'lib.php'); // FOOWD lib

// define regex constants

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
class foowd {

/* configuration vars */

    /**
     * Array of foowd configuration settings
     *
     * @var array
     * @access public
     */
    var $config_settings;

/* additional settings vars */

  /**
   * The version of this Foowd environment class
   *
   * @var str
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
   * Constructs a new environment object.
   *
   * @param array $settings Array of settings for this Foowd environment.
   */
  function foowd($settings = NULL) {
    trigger_error('Function provided by smdoc: foowd_db->constructor', E_USER_ERROR);
  }

  /**
   * Class destructor.
   *
   * Destroys the environment object outputting debugging information and
   * closing the database connection.
   */
  function destroy() { // destructor, must be called explicitly
    $this->track('foowd->destructor');
    if ($this->database) { // close DB and do database end execution stuff
      $this->database->destroy($this);
    }
    $this->track();
    if ($this->debug) { // display debug data
      $this->debug->display();
    }
    unset($this); // unset object
  }

  /**
   * Debug tracking wrapper.
   *
   * Wrapper function to <code>foowd_debug::track</code>
   *
   * @param str functionName Name of the function execution is
   * entering.
   */
  function track($functionName = NULL) { // function call track debug wrapper
    if ($this->debug) {
      if (func_num_args() > 1) {
        $args = func_get_args();
        array_shift($args);  // shift off $function
      } else {
        $args = NULL;
      }
      $this->debug->track($functionName, $args);
    }
  }

  /**
   * Debugging wrapper.
   *
   * Wrapper function to <code>foowd_debug::debug</code>
   *
   * @param str function Name of the debugging function in <code>foowd_debug</code> to call.
   */
  function debug($function) { // generic debug wrapper
    if ($this->debug) {
      if (func_num_args() > 1) {
        $args = func_get_args();
        array_shift($args);  // shift off $function
      } else {
        $args = NULL;
      }
      call_user_func_array(array(&$this->debug, $function), $args);
    }
  }

  /**
   * Get name of template for the given class and method. If a template does
   * not exist for the particular class and method, we look for a template for
   * the classes parent class until we either find a template or reach the base
   * class. In which case we use load default template "default.tpl".
   *
   * @param str className Name of the class.
   * @param str methodName Name of the method.
   */
  function getTemplateName($className, $methodName) {
    $templateFilename = $className.'.'.$methodName.'.tpl';
    while (!file_exists($this->template->template_dir.$templateFilename)) {
      if ($className == FALSE) {
        trigger_error('Could not load template "'.$this->template_dir.$templateFilename.'"', E_USER_ERROR);
        return;
      } elseif ($className == 'foowd_object') {
        $className = FALSE;
        $templateFilename = 'default.tpl';
      } else {
        $className = get_parent_class($className);
        $templateFilename = $className.'.'.$methodName.'.tpl';
      }
    }
    return $templateFilename;
  }

  /**
   * Get the current user from the database.
   *
   * Given the array of user details, fetch the corrisponding user object from
   * the database, unserialise and return it.
   *
   * @access private
   * @param str class The name of the user class being used.
   * @param str username The name of the user to fetch.
   * @param str password The password of the user to fetch
   * @return mixed The selected user object or FALSE on failure.
   */
  function fetchUser($class = NULL, $username = NULL, $password = NULL) { // fetches a user into $this->user, should only be used by Foowd constructor, fetch users as objects using fetchObject() if required
    $this->track('foowd->fetchUser', $class, $username, $password);

    if (isset($username) && $username != NULL) {
      $userid = crc32(strtolower($username)); // generate user ID from given username
      $classid = crc32(strtolower($class)); // generate class ID of user class
    }

    if (isset($userid) && isset($classid)) { // load user from DB
      if ($user = &$this->getObj(array('objectid' => $userid, 'classid' => $classid))) {
        if (isset($password) && $password != NULL && $user->passwordCheck($password) && $user->hostmaskCheck()) { // password and hostmask match, user is valid
          $this->user = &$user;
          if ($user->updated < time() - $this->session_length) { // session start
            $this->debug('msg', 'Starting new user session');
            if (function_exists('foowd_session_start')) { // call session start
              foowd_session_start($this);
            }
            if (method_exists($this->user, 'session_start')) { // call user session start
              $this->user->session_start();
            }
            $this->user->update(); // update user
          } else {
            $this->debug('msg', $this->session_length - (time() - $user->updated).' seconds left in current user session');
          }
          $this->track(); return TRUE;
        } else {
          $this->debug('msg', 'Password incorrect for user');
        }
      } else {
        $this->debug('msg', 'Could not find user in database');
      }
    } else { // create anonymous user object
      if (class_exists($this->anonuser_class)) {
        $this->user = &new $this->anonuser_class($this);
      } else {
        trigger_error('Could not find anonymous user class "'.$this->anonuser_class.'".', E_USER_ERROR);
      }
    }
    $this->track(); return FALSE;
  }

  /**
   * Fetch one version of an object.
   *
   * @param array indexes Array of indexes and values to match
   * @param str source Source to get object from
   * @return object The selected object or NULL on failure.
   * @see foowd_db::getObj
   */
  function &getObj($indexes, $joins = NULL, $source = NULL) {
    $this->track('foowd->getObj', $indexes);

    if ($object = &$this->database->getObj($indexes, $joins, $source)) { // get object
      $this->track(); return $object;

    } elseif (isset($indexes['workspaceid']) && $indexes['workspaceid'] != 0) { // if not already looking in main workspace
      $indexes['workspaceid'] = 0;
      $this->track(); return $this->getObj($indexes); // WARNING: recursion in action
		
    } else {
      $this->track(); return NULL;
    }
  }

  /**
   * Get all versions of an object.
   *
   * @param array indexes Array of indexes and values to match
   * @param str source Source to get object from
   * @return array The array of selected objects or NULL on failure.
   * @see foowd::getObjHistory
   */
  function &getObjHistory($indexes, $source = NULL) {
    $this->track('foowd->getObjHistory', $indexes);

    if ($objects = &$this->database->getObjHistory($indexes, $source)) { // get object history
      $this->track(); return $objects;

    } elseif (isset($indexes['workspaceid']) && $indexes['workspaceid'] != 0) { // if not already looking in main workspace
      $indexes['workspaceid'] = 0;
      $this->track(); return $this->getObjHistory($indexes); // WARNING: recursion in action

    } else {
      $this->track(); return NULL;
    }
  }

  /**
	 * Get a list of objects.
	 *
	 * @param array indexes Array of indexes and values to match
	 * @param str source Source to get object from
	 * @param str order The index to sort the list on
	 * @param bool reverse Display the list in reverse order
	 * @param int offset Offset the list by this many items
	 * @param int number The length of the list to return
	 * @param bool returnObjects Return the actual objects not just the object meta data
	 * @return array The array of selected objects or NULL on failure.
	 * @see foowd::getObjList
	 */
//	function &getObjList($indexes) {
//          trigger_error('Function provided by smdoc: foowd->getObjList', E_USER_ERROR);
//	}

	/**
   * Call class/object method.
   *
   * Wrapper function for <code>foowd_object::method</code> and
   * <code>foowd_object::classMethod</code>. Checks if parameter is a class
   * name or an object
   *
   * @param mixed classNameOrObject The name of the class or object to call the method upon.
   * @param str methodName The method to call upon the object.
   * @return mixed The array results of the method call or an error string.
   */
  function method(&$classNameOrObject, $methodName = NULL) {
    $this->track('foowd->method', $methodName);
    if (is_string($classNameOrObject)) {
      if (class_exists($classNameOrObject) || $this->loadClass(crc32(strtolower($classNameOrObject)))) { // check class exists (if it doesn't, try to load it from DB)
        $this->track();
        return call_user_func(array($classNameOrObject, 'classMethod'), &$this, $classNameOrObject, $methodName); // call method
      } else {
        trigger_error('Class "'.$classNameOrObject.'" does not exist');
        $this->track(); return FALSE;
      }
    } elseif (is_object($classNameOrObject)) {
      $this->track(); return $classNameOrObject->method($methodName);
    } else {
      trigger_error('Class/object not specified');
      $this->track(); return FALSE;
    }
  }

  /**
   * Get user groups.
   *
   * Return an array of all user groups the current user has access to.
   *
   * @param bool includeSpecialGroups Return special groups as well?
   * @return array An array of user groups.
   */
  function getUserGroups($includeSpecialGroups = TRUE) {
    $items = array();
    // add custom groups
    foreach ($this->groups as $group => $name) {
      if ($this->user->inGroup($group)) {
        $items[$group] = $name;
      }
    }
    // add foowd_group groups
    if (class_exists($this->group_class)) {
      $userGroups = $this->getObjList(
        array('classid' => crc32(strtolower($this->group_class))),
        NULL,
        array('title'),
        NULL,
        0,
        NULL,
        TRUE
      );
      if ($userGroups) {
        foreach ($userGroups as $userGroup) {
          if ($this->user->inGroup($userGroup->objectid)) {
            $items[$userGroup->objectid] = $userGroup->getTitle();
          }
        }
      }
    }
    if (!$includeSpecialGroups) { // remove special groups
      unset($items['Everyone']);
      unset($items['Nobody']);
      unset($items['Registered']);
    }
    ksort($items);
    return $items;
  }

  /**
   * Unserialise object.
   *
   * Unserialise a serialised object loading any classes that are required.
   *
   * @param str serializedObj Serialised object to unserialise.
   * @param int classid Classid of the object to be unserialised.
   * @return object The unserialised object.
   */
  function unserialize($serializedObj, $classid = NULL) {
    $this->track('foowd->unserialize', $classid);
    if ( $classid != NULL && !classLoaded($classid)) { // class definition not found, load
      $this->loadClass($classid);
    }
    $this->track();
    return unserialize($serializedObj);
  }

  /**
   * Load dynamic class.
   *
   * Load a class definition from an object within the database.
   *
   * @access private
   * @param int classid Classid of the class to be loaded.
   * @return bool Success or failure.
   */
  function loadClass($classid) {
    $this->track('foowd->loadClass', $classid);
    if ( isset($this->config_settings['site']['definition_class']) &&
             class_exists($this->config_settings['site']['definition_class']) ) {
      $class = $this->getObj(array('objectid' => $classid, 'classid' => crc32(strtolower($this->definition_class))));
      if (is_object($class)) {
  // if it inherits from another class, find out and load it now
        if (preg_match_all('|class ([-_a-zA-Z0-9]*) extends ([-_a-zA-Z0-9]*) ?{|', $class->body, $pregMatches)) { // i'd rather do this with catching errors on the eval, but that can't be done
          if ($pregMatches[1] != $pregMatches[2]) {
            foreach($pregMatches[2] as $className) {
              if ($className != $class->title && !class_exists($className)) {
                $this->loadClass(crc32(strtolower($className))); // WARNING: recursion in action
              }
            }
          }
        }
  // define class
        setClassMeta($class->title, $class->description);
        if (eval($class->body) === FALSE) {
          $this->track(); return FALSE;
        } else {
          $this->track(); return TRUE;
        }
      }
    } else {
      $this->debug('msg', 'Unknown object type found, dynamic classes not active, failing to load class definition for object');
    }
    $this->track();
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
  function loadDefaultClass($className) { // load an incomplete class, it is just a foowd_object clone to enable loading of objects whose class definitions can not be found.
    setClassMeta($className, _("Unknown Foowd Class"));
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
 * @param str className Name of the class trying to be unserialised
 */
function unserializeCallback($className) { foowd::loadDefaultClass($className); }

?>
