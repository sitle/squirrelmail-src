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
lib.php
Foowd function library
*/

/**
 * Display a variable in a formatted way.
 *
 * @package Foowd/Lib
 * @param mixed var Variable to output.
 */
function show($var) {
  echo '<pre>';
  if (is_object($var)) {
    unset($var->foowd);
    unset($var->foowd_vars_meta);
    unset($var->foowd_indexes);
    unset($var->foowd_original_access_vars);
    print_r($var);
  } elseif (is_array($var)) {
    print_r($var);
  } else {
    var_dump($var);
  }
  echo '</pre>';
}

/* System functions */

/**
 * Return the value of a constant or a default value.
 *
 * @package Foowd/Lib
 * @param str constant Name of a constant.
 * @param mixed default Default value.
 * @return mixed The value of the constant or the default value.
 */
function getConstOrDefault($constant, $default) {
  if (defined($constant)) {
    return constant($constant);
  } else {
    return $default;
  }
}

/**
 * Set a constant to a value if the constant is defined.
 *
 * @package Foowd/Lib
 * @param str constName Name of a constant.
 * @param mixed value The value to set.
 */
function setConst($constName, $value) {
  $constName = strtoupper($constName);
  if (!defined($constName)) {
    define($constName, $value);
  }
}

/**
 * Find the max length of string allowed by a regex.
 *
 * @package Foowd/Lib
 * @param str regex The regular expression to find the length of.
 * @param int default The default length to return if we can not find a length in the regular expression.
 * @return str The maximum length allowed by a regular expression.
 */
function getRegexLength($regex, $default) {
  if (preg_match('/\*/', $regex)) {
    return 0;
  } elseif (preg_match('/\+/', $regex)) {
    return 0;
  } elseif (preg_match('/\{[0-9,]*([0-9]+)\}/U', $regex, $results = array())) {
    return $results[1];
  } elseif (preg_match('/\?/', $regex)) {
    return 1;
  } else {
    return $default;
  }
}

/**
 * Get the user group permission of a object or class method.
 *
 * @package Foowd/Lib
 * @param str className Name of the class the method belongs to.
 * @param str methodName Name of the method.
 * @param str type Type of method, 'class' or 'object'.
 * @return str The user group string the permission is set to.
 */
function getPermission($className, $methodName, $type = '') {
  $type = strtoupper($type);
  if ( $type != 'CLASS' ) {
    $type = 'OBJECT';
  }

  if ( !isset($className) || !class_exists($className) )
    $className = 'foowd_object';

  $genericName = 'PERMISSION_'.$type.'_'.strtoupper($methodName);
  $constName = 'PERMISSION_'.strtoupper($className).'_'.$type.'_'.strtoupper($methodName);
  if (defined($constName)) {
    return constant($constName);
  } elseif (defined($genericName)) {
    return constant($genericName);
  } elseif ($className == 'foowd_object') { // none found
    return 'Everyone';
  } else { // look at parent
    return getPermission(get_parent_class($className), $methodName, $type);
  }
}

/**
 * Set the user group permission of a object or class method.
 *
 * @package Foowd/Lib
 * @param str className Name of the class the method belongs to.
 * @param str type Type of method, 'class' or 'object'.
 * @param str methodName Name of the method.
 * @param str value User group string to set the permission to.
 */
function setPermission($className, $type, $methodName, $value = 'Everyone') {
  $type = strtoupper($type);
  if ( $type != 'CLASS' ) {
    $type = 'OBJECT';
  }

  setConst('PERMISSION_'.strtoupper($className).'_'.$type.'_'.strtoupper($methodName), $value);
}

/**
 * Set class meta data.
 *
 * @package Foowd/Lib
 * @param str className Name of the class.
 * @param str description Text description of the class.
 */
function setClassMeta($className, $description) {
  $classid = crc32(strtolower($className));
  setConst('META_'.$classid.'_CLASSNAME', $className);
  setConst('META_'.$classid.'_DESCRIPTION', $description);
  setConst('META_'.strtoupper($className).'_CLASS_ID', $classid);
}

/**
 * Get class name given the classid.
 *
 * @package Foowd/Lib
 * @param int classid The id of the class.
 * @return str The class name.
 */
function getClassName($classid) {
  if (defined('META_'.$classid.'_CLASSNAME')) {
    return constant('META_'.$classid.'_CLASSNAME');
  } else {
    trigger_error('Could not find class name from class ID '.$classid);
  }
}

/**
 * Get class description given the classid.
 *
 * @package Foowd/Lib
 * @param int classid The id of the class.
 * @return str The class description.
 */
function getClassDescription($classid) {
  if (defined('META_'.$classid.'_DESCRIPTION')) {
    return constant('META_'.$classid.'_DESCRIPTION');
  } else {
    return 'Unknown';
  }
}

/**
 * Whether a class has been loaded. Uses the class meta data to see if the given classid has been loaded into the system.
 *
 * @package Foowd/Lib
 * @param int classid The id of the class.
 * @return bool TRUE if the class is loaded.
 */
function classLoaded($classid = NULL) {
  if ($classid == NULL || defined('META_'.$classid.'_CLASSNAME')) {
    return TRUE;
  } else {
    return FALSE;
  }
}

/**
 * Whether a class is a Foowd object.
 *
 * @package Foowd/Lib
 * @param str className The name of the class.
 * @return bool TRUE if the class is a child of foowd_object.
 */
function isFoowdObject($className) {
  if ($className == 'foowd_object') {
    return TRUE;
  }
  $parentName = get_parent_class($className);
  if ($parentName == 'foowd_object') {
    return TRUE;
  } elseif ($parentName != '') {
    return isFoowdObject($parentName);
  } else {
    return FALSE;
  }
}

/**
 * Get the names of all Foowd classes loaded.
 *
 * @package Foowd/Lib
 * @return array An array of class names.
 */
function getFoowdClassNames() {
  foreach (get_declared_classes() as $className) {
    if (isFoowdObject($className)) {
      $items[strval(crc32(strtolower($className)))] = $className;
    }
  }
  return $items;
}

/**
 * Get system temporary directory.
 *
 * @package Foowd/Lib
 * @return str The system temporary directory.
 */
function getTempDir() {
  if (isset($_ENV['TMP'])) {
    return $_ENV['TMP'].'\\';
  } elseif (isset($_ENV['TEMP'])) {
    return $_ENV['TEMP'].'\\';
  } else {
    return substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/')).'/temp/';
  }
}

/* URI functions */

/**
 * Generate a URI given certain parameters.
 *
 * @package Foowd/Lib
 * @param array parameters An array of querystring value pairs.
 * @return str A URI.
 */
function getURI($parameters = NULL, $relative = TRUE) { 

  $uri = $relative ? '' : 
         (defined('BASE_URL') ? BASE_URL : 'http://'.$_SERVER['HTTP_HOST']);

  $uri .= defined('FILENAME') ? FILENAME : $_SERVER['PHP_SELF'] . '?';

  if (is_array($parameters)) {
    foreach ($parameters as $name => $value) {
      $uri .= $name.'='.$value.'&';
    }
  }
  return substr($uri, 0, -1);
}

/* Display functions */

/**
 * Anti-spam an e-mail address.
 *
 * @package Foowd/Lib
 * @param str emailAddress E-mail address to mangle.
 * @return str Munged e-mail address.
 */
function mungEmail($emailAddress) {
  switch (rand(1, 4)) {
  case 1:
    return str_replace('@', ' at ', str_replace('.', ' dot ', $emailAddress));
  case 2:
    return str_replace('@', '&', str_replace('.', ',', $emailAddress));
  case 3:
    $pos = rand(1, strlen($emailAddress) - 3);
    return substr($emailAddress, 0, $pos).'[ ]'.substr($emailAddress, $pos + 3).' [\''.substr($emailAddress, $pos, 3).'\' in gap]';
  case 4:
    return str_replace('@', 'NO@SPAM', $emailAddress);
  }
}

/**
 * Return the time since a certain time in the past.
 *
 * @package Foowd/Lib
 * @param int time The time in the past as a Unix timestamp.
 * @return str A string representation of roughly how long has passed.
 */
function timeSince($time) {
  $time = time() - $time;
  if ($num = round($time / (3600 * 24 * 365), 1) AND $num > 1) {
    return $num.' years';
  } elseif ($num = round($time / (3600 * 24 * 30), 1) AND $num > 1) {
    return $num.' months';
  } elseif ($num = round($time / (3600 * 24 * 7), 1) AND $num > 1) {
    return $num.' weeks';
  } elseif ($num = round($time / (3600 * 24), 1) AND $num > 1) {
    return $num.' days';
  } elseif ($num = round($time / (3600), 1) AND $num > 1) {
    return $num.' hours';
  } elseif ($num = floor($time / 60) AND $num > 1) {
    return $num.' minutes';
  } else {
    return $time.' seconds';
  }
}

/* E-mail function */

/**
 * Send an e-mail. This function is a wrapper to the PHP mail function that
 * includes writing debugging data to the debug stream.
 *
 * @package Foowd/Lib
 * @param object foowd The foowd environment object.
 * @param str to The e-mail address to send the e-mail to.
 * @param str subject The subject of the e-mail.
 * @param str message The message to send.
 * @param str headers Additional e-mail headers.
 * @param str para Additional e-mail parameters.
 * @return bool TRUE on success.
 */
function email(&$foowd, $to, $subject, $message, $headers = NULL, $para = NULL) {
  if ($foowd->debug) {
    $foowd->debug('msg', 'Sending e-mail:');
    $foowd->debug('msg', 'To: '.$to);
    $foowd->debug('msg', 'Subject: '.$subject);
    $foowd->debug('msg', $headers);
    $foowd->debug('msg', $message);
  }
  //return @mail($to, $subject, $message, $headers, $para);
  return TRUE;
}

/* Cookie functions */

/**
 * Test for cookies on client.
 *
 * This function sends a test cookie to the client which can be used by
 * {@link cookieTest} to check that the client has cookie support availble.
 *
 * @package Foowd/Lib
 * @param object foowd The foowd environment object.
 */
function sendTestCookie(&$foowd) {
  if ($foowd->cookie_test) {
    include_once(INPUT_DIR.'input.cookie.php');
    $cookieTest = new input_cookie('test', '/^[12]$/', 2);
    if ($cookieTest->value == 2) {
      $cookieTest->set(1);
    }
  }
}

/**
 * Look for test cookie.
 *
 * This function looks for the test cookie sent by {@link sendTestCookie} to
 * check that the client has cookie support availble.
 *
 * @package Foowd/Lib
 * @param object foowd The foowd environment object.
 * @return bool TRUE if cookie support was detected.
 */
function cookieTest(&$foowd) {
  if ($foowd->cookie_test && $foowd->user_authType == 'cookie') {
    include_once(INPUT_DIR.'input.cookie.php');
    $cookieTest = new input_cookie('test', '/^[12]$/', 2);
    if ($cookieTest->value == 2) {
      return FALSE;
    }
  }
  return TRUE;
}

/* Locale functions */

if (!function_exists('_')) { // no gettext support, so define our own "_" function
  /**
   * Fake Getext function. If Gettext support is not availble, this function
   * acts as a passthru function in its place.
   *
   * @package Foowd/Lib
   * @param str text Text to return
   * @return str The returned text.
   */
  function _($text) {
    global $LANG;
    if (isset($LANG[$text])) {
      return $LANG[$text];
    } else {
      return $text;
    }
  }
}

?>
