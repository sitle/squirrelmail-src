<?php

require('config.foowd.php');             // include config and Foowd functions

// init Foowd
$foowd = new smdoc(NULL, NULL, $DEFAULT_GROUPS);

// get object details
include_once($foowd->path.'/input.querystring.php');

$objectName = new input_querystring('object', REGEX_TITLE);
$objectid = new input_querystring('objectid', '/^[0-9-]*$/');
$version    = new input_querystring('version', '/^[0-9]*$/');
$className  = new input_querystring('class', REGEX_TITLE);
$classid = new input_querystring('classid', '/^[0-9-]*$/', NULL);
$method = new input_querystring('method', NULL);

// convert object name into id (if name given rather than id)
if (isset($objectName->value)) {
	$objectid->set(crc32(strtolower($objectName->value)));
}

// convert class name into id (if name given rather than id)
if (isset($className->value)) {
    $className = $className->value;
	$classid->set(crc32(strtolower($className)));
} elseif (!isset($objectid->value)) {
	$objectid->set(DEFAULT_OBJECTID);
}

$objectid = $objectid->value;
$classid = $classid->value;
$version = $version->value;
$method = $method->value;

if (isset($objectid)) { // fetch object and call object method
    $foowd->debug('msg', 'fetch and call object method');

    if ( !isset($method) )
        $method = getConstOrDefault('DEFAULT_METHOD','view');

	if ($object = $foowd->fetchObject($objectid, $classid, $version, $method)) {
		if (is_object($object)) {
			$className = getClassName($object->classid);
		} else {
			$className = getClassName($classid);
		}
		$t = $foowd->method($object, $method);
        $methodName = 'object_'.$method;
	} else {
		trigger_error('Object not found: ' . 
                 (isset($objectName->value) ? $objectName->value : $objectid) . 
                 ' (' . $className . ')', E_USER_NOTICE);
	}
} else { // call class method
    $foowd->debug('msg', 'fetch and call class method');
	if ( !isset($className) )
        $className = getClassName($classid);
    if ( !isset($method) )
        $method = getConstOrDefault('DEFAULT_CLASS_METHOD', 'create');

	$t = $foowd->method($className, $method);
    $methodName = 'class_'.$method;
}

$foowd->debug('msg', 'display result using template');
if (is_array($t)) {
    if ( isset($objectid) && $method != 'delete' )
        $t['showurl'] = true;
    else
        $t['showurl'] = false;
	include($foowd->getTemplateName($className, $methodName));
} else {
	trigger_error($t, E_USER_NOTICE);
}


// destroy Foowd
$foowd->destroy();

?>
