<?php

require('config.php');             // include config and Foowd functions

// init Foowd
$foowd_parameters['debug']['debug_enabled'] = TRUE;
$foowd = new smdoc($foowd_parameters);

// get object details
$objectName = new input_querystring('object', REGEX_TITLE);
$objectid = new input_querystring('objectid', '/^[0-9-]*$/');
$version    = new input_querystring('version', '/^[0-9]*$/');
$className  = new input_querystring('class', REGEX_TITLE);
$classid = new input_querystring('classid', '/^[0-9-]*$/', NULL);
$method = new input_querystring('method', NULL, 'view');

// convert object name into id (if name given rather than id)
if (!isset($objectid->value) && isset($objectName->value)) {
	$objectid->set(crc32(strtolower($objectName->value)));
}

// convert class name into id (if name given rather than id)
if (!isset($classid->value) && isset($className->value)) {
    $className = $className->value;
    $classid->set(crc32(strtolower($className)));
} elseif (!isset($objectid->value)) {
    $objectid->set($foowd->config_settings['site']['default_objectid']);
}

$objectid = $objectid->value;
$classid = $classid->value;
$version = $version->value;
$method = $method->value;
$result = FALSE;

if (isset($objectid))  // fetch object and call object method
{
  $foowd->debug('msg', 'fetch and call object method');

  if ( !isset($method) )
    $method = $foowd->config_settings['site']['default_method'];
  $object = &$foowd->getObj(array(
                        'objectid' => $objectid,
                        'classid'  => $classid, 
                        'version'  => $version));
  if ( $object ) 
  {
    if (is_object($object)) 
      $className = getClassName($object->classid);
    else 
      $className = getClassName($classid);

    $result = $foowd->method($object, $method);
    $methodName = 'object_'.$method;
  }
  else 
  {
    trigger_error('Object not found: ' 
                  . (isset($objectName->value) ?$objectName->value : $objectid)
                  . ' (' . $className . ')', E_USER_ERROR);
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
