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

require('config.php');             // include config and Foowd functions

/* 
 * Initialize SMDoc/FOOWD environment
 */
$foowd_parameters['debug']['debug_enabled'] = TRUE;
$foowd = new smdoc($foowd_parameters);

$objectMethod = TRUE;
$objectid = 0;
$classid = 0;

/*
 * Check for shorthand objectid using well-known object name:
 * e.g. object=sqmindex, object=faq, object=privacy, etc.
 */
$objectName_q = new input_querystring('object', REGEX_TITLE);
if ( $objectName_q->wasValid )
{
  $lookup =& smdoc_name_lookup::getInstance($foowd);
  $result = $lookup->findObject($objectName_q->value,
                                $objectid, // defined
                                $classid); // defined
  if ( !$result )
    $_SESSION['error'] = OBJECT_NOT_FOUND;
}
else
{
  $classid_q = new input_querystring('classid', '/^[0-9-]*$/', NULL);
  $classid = $classid_q->value;

  $objectid_q = new input_querystring('objectid', '/^[0-9-]*$/', NULL);
  $objectid = $objectid_q->value;
}

if ( $objectid == NULL )
  $objectid = $foowd->config_settings['site']['default_objectid'];

$version_q = new input_querystring('version', '/^[0-9]*$/');
$method_q = new input_querystring('method', NULL);
$method = $method_q->value;

$className_q  = new input_querystring('class', REGEX_TITLE, NULL);
if ( $className_q->wasValid )
{
  $className = $className_q->value;
  $objectMethod = FALSE;
}
elseif ( $classid != NULL )
  $className = getClassName($classid);

/*
 * If form has been cancelled, redirect to view of that object
 */
if ( sqGetGlobalVar('form_cancel', $value, SQ_FORM) )
{
  unset($_SESSION['error']);
  $_SESSION['ok'] = OBJECT_UPDATE_CANCEL;

  $uri_arr['objectid'] = $objectid;
  if ( !empty($classid) )
    $uri_arr['classid']  = $classid;
  if ( $version_q->wasSet )
    $uri_arr['version']  = $version_q->value;
  $foowd->loc_forward( getURI($uri_arr, FALSE));
  exit;
}

$result = FALSE;

/**
 * Processing an object method.
 * URL might look like:
 * index.php?object=faq  (default method view)
 * index.php?object=faq&method=admin
 * index.php?objectid=3218321&classid=43872432&method=groups
 */
if ( $objectMethod )  // fetch object and call object method
{
  $foowd->debug('msg', 'fetch and call object method');

  if ( !isset($method) )
    $method = $foowd->config_settings['site']['default_method'];

  $where['objectid'] = $objectid;
  if ( !empty($classid) )
    $where['classid']  = $classid;
  if ( $version_q->wasSet )
    $where['version']  = $version_q->value;

  $object = &$foowd->getObj($where);

  if ( is_object($object) ) 
  {
    $classid = $object->classid;
    $className = getClassName($classid);
    $result = $foowd->method($object, $method);
    $methodName = 'object_'.$method;
  }
  else 
  {
    if ( empty($className) )
      $className = 'unknown';

    trigger_error('Object not found: ' 
                  . $objectid . ' (' . $className . ')', E_USER_ERROR);
  }
} 
else  // call class method
{
  $foowd->debug('msg', 'fetch and call class method');

  if ( !isset($className) )
    $className = getClassName($classid);

  if ( !isset($method) )
    $method = $foowd->config_settings['site']['default_class_method'];

  $result = $foowd->method($className, $method);
  $methodName = 'class_'.$method;
}

if ( $result === TRUE )
{
  $tplName = $foowd->getTemplateName($className, $methodName);
  $foowd->debug('msg', 'display result using template: ' . $tplName);
  $foowd->template->display($tplName);
}
else 
  trigger_error("Previous error, no defined result", E_USER_NOTICE);

// destroy Foowd
$foowd->destroy();

?>
