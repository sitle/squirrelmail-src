<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Generic entry-point for all of Foowd/smdoc
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 */

/** 
 * Initial configuration, start session
 * @see config.default.php
 */
require('smdoc_init.php');

/** Class to verify $_GET/querystring parameter data */
require_once(INPUT_DIR . 'input.querystring.php');

/* 
 * Initialize smdoc/FOOWD environment
 */
$foowd = new smdoc($foowd_parameters);

$objectMethod = TRUE;
$objectid = 0;
$classid = 0;
$objectOK = TRUE;

/*
 * Check for shorthand objectid using well-known object name:
 * e.g. object=sqmindex, object=privacy, etc.
 */
$objectName_q = new input_querystring('object', REGEX_TITLE);
if ( $objectName_q->wasSet && $objectName_q->wasValid )
{
  $lookup =& smdoc_name_lookup::getInstance($foowd);
  $result = $lookup->findObject($objectName_q->value,
                                $objectid, // defined
                                $classid); // defined
  if ( !$result )
  {
    $objectOK = FALSE;
    $_SESSION['error'] = OBJECT_NOT_FOUND;
  }
}
else
{
  $classid_q = new input_querystring('classid', REGEX_ID, NULL);
  $classid = $classid_q->value;

  $objectid_q = new input_querystring('objectid', REGEX_ID, NULL);
  $objectid = $objectid_q->value;
}

$version_q = new input_querystring('version', REGEX_VERSION);
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
elseif ( $objectid == NULL )
{
  $lookup =& smdoc_name_lookup::getInstance($foowd);
  $result = $lookup->findObject('home',
                                $objectid, // defined
                                $classid); // defined
  if ( !$result )
  {
    $objectOK = FALSE;
    $_SESSION['error'] = OBJECT_NOT_FOUND;
  }
}

/*
 * If form has been cancelled, redirect to view of that object
 */
if ( $objectOK && sqGetGlobalVar('form_cancel', $value, SQ_FORM) )
{
  unset($_SESSION['error']);
  $_SESSION['ok'] = OBJECT_UPDATE_CANCEL;

  if ( empty($objectid) )
    $uri_arr['object']='home';
  else
  {
    $uri_arr['objectid'] = $objectid;
    if ( !empty($classid) )
      $uri_arr['classid']  = $classid;
    if ( $version_q->wasSet )
      $uri_arr['version']  = $version_q->value;
  }

  $foowd->loc_forward(getURI($uri_arr, FALSE));
  exit;
}

$result = FALSE;

/*
 * Processing an object method.
 * URL might look like:
 * index.php?object=faq  (default method view)
 * index.php?object=faq&method=admin
 * index.php?objectid=3218321&classid=43872432&method=groups
 */
if ( !$objectOK )
{
  $foowd->debug('msg', 'Object Not Found');
  $query = empty($_SERVER['QUERY_STRING']) ? 'object=home' : $_SERVER['QUERY_STRING'];
  $object = new smdoc_error($foowd, ERROR_TITLE,
                            sprintf(_("Specified Object Not Found: %s"), $query));
  $result = $foowd->method($object, 'view');

  $className = 'smdoc_error';
  $methodName = 'object_view';
}
elseif ( $objectMethod )  // fetch object and call object method
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
    // Try finding object in a different workspace:
    $objects = &$foowd->getObjList(array('objectid','title','workspaceid','updated'),
                                  NULL,$where, NULL, NULL, FALSE, FALSE);
    if ( empty($objects) )
    {
      if ( empty($className) )
        $className = 'unknown';
      trigger_error('Object not found: ' 
                    . $objectid . ' (' . $className . ')', E_USER_ERROR);
    }
    else
    {
      $object = new smdoc_error($foowd, ERROR_TITLE);
      $foowd->template->assign_by_ref('objectList', $objects);
      $result = $foowd->method($object, 'bad_workspace');
      $methodName = 'object_bad_workspace';
      $className = 'smdoc_error';
    }
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

/*
 * Display results using appropriate template
 */
if ( $result === TRUE )
{
  $tplName = $foowd->getTemplateName($className, $methodName);
  $foowd->debug('msg', 'display result using template: ' . $tplName);
  $foowd->template->display($tplName);
}
else 
  trigger_error("Previous error, no defined result", E_USER_NOTICE);

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->__destruct();

?>
