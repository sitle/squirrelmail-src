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
 * Basic functions commonly used throughout the framework.
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package Foowd
 */

/**
 * Display a variable in a formatted way.
 *
 * @param mixed var Variable to output.
 */
function show($var, $comment = NULL) 
{
  echo '<pre>';
  if ( $comment )
    echo $comment . '<br />';
  if (is_object($var)) 
  {
    if ( isset($var->config_settings) ) 
      unset($var->config_settings);
    unset($var->foowd);
    unset($var->foowd_vars_meta);
    unset($var->foowd_indexes);
    unset($var->foowd_original_access_vars);
    var_dump($var);
  }
  elseif ( is_array($var) )
  {
    unset($var['foowd']);
    print_r($var);
  } 
  else 
    var_dump($var);

  echo '</pre>';
}

/* System functions */

/**
 * Return the value of a constant or a default value.
 *
 * @param string constant Name of a constant.
 * @param mixed default Default value.
 * @return mixed The value of the constant or the default value.
 */
function getConstOrDefault($constant, $default) 
{
  if (defined($constant))
    return constant($constant);

  return $default;
}

/**
 * Set a constant to a value if the constant is defined.
 *
 * @param string constName Name of a constant.
 * @param mixed value The value to set.
 */
function setConst($constName, $value) 
{
  $constName = strtoupper($constName);
  if (!defined($constName))
    define($constName, $value);
}

/**
 * Find the max length of string allowed by a regex.
 *
 * @param string regex The regular expression to find the length of.
 * @param int default The default length to return if we can not find a length in the regular expression.
 * @return string The maximum length allowed by a regular expression.
 */
function getRegexLength($regex, $default) 
{
  if (preg_match('/\*/', $regex))
    return 0;
  if (preg_match('/\+/', $regex))
    return 0;
  if (preg_match('/\{[0-9,]*([0-9]+)\}/U', $regex, $results = array()))
    return $results[1];
  if (preg_match('/\?/', $regex))
    return 1;

  return $default;
}

/**
 * Get the user group permission of a object or class method.
 *
 * @param string className Name of the class the method belongs to.
 * @param string methodName Name of the method.
 * @param string type Type of method, 'class' or 'object'.
 * @return string The user group string the permission is set to.
 */
function getPermission($className, $methodName, $type = '') 
{
  $type = strtoupper($type);
  if ( $type != 'CLASS' )
    $type = 'OBJECT';

  if ( !isset($className) || !class_exists($className) )
    $className = 'foowd_object';

  $constName = 'PERMISSION_'.strtoupper($className).'_'.$type.'_'.strtoupper($methodName);
  if (defined($constName))
    return constant($constName);
  if ($className == 'foowd_object') // none found
    return 'Everyone';

  // Recurse to look at permissions of parent    
  return getPermission(get_parent_class($className), $methodName, $type);
}

/**
 * Set the user group permission of a object or class method.
 *
 * @param string className Name of the class the method belongs to.
 * @param string type Type of method, 'class' or 'object'.
 * @param string methodName Name of the method.
 * @param string value User group string to set the permission to.
 */
function setPermission($className, $type, $methodName, $value = 'Everyone') 
{
  $type = strtoupper($type);
  if ( $type != 'CLASS' )
    $type = 'OBJECT';

  setConst('PERMISSION_'.strtoupper($className).'_'.$type.'_'.strtoupper($methodName), $value);
}

/**
 * Set class meta data.
 *
 * @param string className Name of the class.
 * @param string description Text description of the class.
 */
function setClassMeta($className, $description) 
{
  $classid = crc32(strtolower($className));
  setConst('META_'.$classid.'_CLASSNAME', $className);
  setConst('META_'.$classid.'_DESCRIPTION', $description);
  setConst('META_'.strtoupper($className).'_CLASS_ID', $classid);
}

/**
 * Get class name given the classid.
 *
 * @param int classid The id of the class.
 * @return string The class name.
 */
function getClassName($classid) 
{
  if (defined('META_'.$classid.'_CLASSNAME'))
    return constant('META_'.$classid.'_CLASSNAME');

  trigger_error('Could not find class name from class ID '.$classid);
}

/**
 * Get class description given the classid.
 *
 * @param int classid The id of the class.
 * @return string The class description.
 */
function getClassDescription($classid) 
{
  if (defined('META_'.$classid.'_DESCRIPTION'))
    return constant('META_'.$classid.'_DESCRIPTION');

  return 'Unknown';
}

/**
 * Whether a class has been loaded. Uses the class meta data to see if the given classid has been loaded into the system.
 *
 * @param int classid The id of the class.
 * @return bool TRUE if the class is loaded.
 */
function classLoaded($classid = NULL) 
{
  if ($classid == NULL || defined('META_'.$classid.'_CLASSNAME'))
    return TRUE;

  return FALSE;
}

/**
 * Whether a class is a Foowd object.
 *
 * @param string className The name of the class.
 * @return bool TRUE if the class is a child of foowd_object.
 */
function isFoowdObject($className) 
{
  if ($className == 'foowd_object')
    return TRUE;

  $parentName = get_parent_class($className);
  if ($parentName == 'foowd_object')
    return TRUE;
  if ($parentName != '')
    return isFoowdObject($parentName);

  return FALSE;
}

/**
 * Get the names of all Foowd classes loaded.
 *
 * @return array An array of class names.
 */
function getFoowdClassNames() 
{
  foreach (get_declared_classes() as $className) 
  {
    if (isFoowdObject($className))
      $items[strval(crc32(strtolower($className)))] = $className;
  }
  return $items;
}

/**
 * Get system temporary directory.
 *
 * @return string The system temporary directory.
 */
function getTempDir() 
{
  if (isset($_ENV['TMP'])) 
    return $_ENV['TMP'].'\\';
  if (isset($_ENV['TEMP']))
    return $_ENV['TEMP'].'\\';

  return substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/')).'/temp/';
}

/* URI functions */

/**
 * Generate a URI given certain parameters.
 *
 * @param array parameters An array of querystring value pairs.
 * @return string A URI.
 */
function getURI($parameters = NULL, $relative = TRUE) 
{ 
  $uri = $relative ? '' : 
         ( defined('BASE_URL') ? BASE_URL : 'http://'.$_SERVER['HTTP_HOST'].'/' );

  $uri .= defined('FILENAME') ? FILENAME : $_SERVER['PHP_SELF'] ;

  if (is_array($parameters) && count($parameters) > 0 ) 
  {
    $i = 0;
    $uri .= '?';
    foreach ($parameters as $name => $value) 
    {
      if ( $i++ > 0 )
        $uri .= '&';
      $uri .= $name.'='.$value;
    }
  }
  return $uri;
}

/* Display functions */

/**
 * Anti-spam an e-mail address.
 *
 * @param string emailAddress E-mail address to mangle.
 * @return string Munged e-mail address.
 */
function mungEmail($emailAddress) 
{
  switch (rand(1, 4)) 
  {
    case 1:
      return strtr($emailAddress, array('@' => ' at ', '.' => ' dot '));
    case 2:
      return strtr($emailAddress, array('@' => '#', '.' => ','));
    case 3:
      $pos = rand(1, strlen($emailAddress) - 3);
      return substr($emailAddress, 0, $pos).'[ ]'.substr($emailAddress, $pos + 3).' [\''.substr($emailAddress, $pos, 3).'\' in gap]';
    case 4:
      return str_replace('@', 'NO@SPAM', $emailAddress);
    case 5:
      return strrev($emailAddress);
  }
}

/**
 * Return the time since a certain time in the past.
 *
 * @param int time The time in the past as a Unix timestamp.
 * @return string A string representation of roughly how long has passed.
 */
function timeSince($time) 
{
  $time = time() - $time;
  if ($num = round($time / (3600 * 24 * 365), 1) AND $num > 1) 
    return $num.' years';
  if ($num = round($time / (3600 * 24 * 30), 1) AND $num > 1) 
    return $num.' months';
  if ($num = round($time / (3600 * 24 * 7), 1) AND $num > 1) 
    return $num.' weeks';
  if ($num = round($time / (3600 * 24), 1) AND $num > 1) 
    return $num.' days';
  if ($num = round($time / (3600), 1) AND $num > 1) 
    return $num.' hours';
  if ($num = floor($time / 60) AND $num > 1) 
    return $num.' minutes';

  return $time.' seconds';
}

/* E-mail function */

/**
 * Send an e-mail. This function is a wrapper to the PHP mail function that
 * includes writing debugging data to the debug stream.
 *
 * @param smdoc foowd Reference to the foowd environment object.
 * @param string to The e-mail address to send the e-mail to.
 * @param string subject The subject of the e-mail.
 * @param string message The message to send.
 * @param string headers Additional e-mail headers.
 * @param string para Additional e-mail parameters.
 * @return bool TRUE on success.
 */
function email(&$foowd, $to, $subject, $message, $headers = NULL, $para = NULL) 
{
  if ($foowd->debug) 
  {
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
 * @param smdoc foowd Reference to the foowd environment object.
 */
function sendTestCookie(&$foowd) 
{
  if ($foowd->cookie_test) 
  {
    include_once(INPUT_DIR.'input.cookie.php');
    $cookieTest = new input_cookie('test', '/^[12]$/', 2);
    if ($cookieTest->value == 2)
      $cookieTest->set(1);
  }
}

/**
 * Look for test cookie.
 *
 * This function looks for the test cookie sent by {@link sendTestCookie} to
 * check that the client has cookie support availble.
 *
 * @param smdoc foowd Reference to the foowd environment object.
 * @return bool TRUE if cookie support was detected.
 */
function cookieTest(&$foowd) 
{
  if ($foowd->cookie_test && $foowd->user_authType == 'cookie') 
  {
    include_once(INPUT_DIR.'input.cookie.php');
    $cookieTest = new input_cookie('test', '/^[12]$/', 2);
    if ($cookieTest->value == 2)
      return FALSE;
  }
  return TRUE;
}

/* Locale functions */

/** 
 * If locale support does not exist, 
 * define our own "_" function.
 */
if (!function_exists('_')) 
{
  /**
   * Fake Getext function. If Gettext support is not available, this function
   * acts as a passthru function in its place.
   *
   * @param string text Text to return
   * @return string The returned text.
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
