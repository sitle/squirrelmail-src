<?php

define('PATH', ''); // application path

require('config.foowd.php'); // include config and Foowd functions

// get object details
$objectName = new input_querystring('object', REGEX_TITLE);
$objectid = new input_querystring('objectid', '/^[0-9-]*$/');
$version = new input_querystring('version', '/^[0-9]*$/');
$className = new input_querystring('class', REGEX_TITLE);
$classid = new input_querystring('classid', '/^[0-9-]*$/');
$method = new input_querystring('method', NULL, NULL);

// convert object name into id (if name given rather than id)
if (!isset($objectid->value) && isset($objectName->value)) {
	$objectid->set(crc32(strtolower($objectName->value)));
}
// convert class name into id (if name given rather than id)
if (!isset($classid->value) && isset($className->value)) {
	$classid->set(crc32(strtolower($className->value)));
}

// init Foowd
$foowd = new foowd(NULL, NULL, $DEFAULT_GROUPS);

// set callback environment, you need to set this to the environment object you want
// to use if you unserialize an instance of a dynamic class.
$FOOWD_LOADCLASSCALLBACK = &$foowd;

// fetch object / call methods
if (isset($className->value) && !isset($objectid->value) && !isset($objectName->value)) { // call class method
	if ($methodError = $foowd->callClassMethod($className->value, $method->value)) {
		trigger_error($methodError, E_USER_NOTICE);
	}
} else { // fetch object

	$cacheName = getCacheName($objectid->value, $classid->value, $method->value); // check cache settings
	if (!readCache($cacheName)) { // if couldn't read cache go fetch object
		if ($object = $foowd->fetchObject(
			array(
				'objectid' => $objectid->value,
				'version' => $version->value,
				'classid' => $classid->value
			)
		)) {
			if ($methodError = $foowd->callMethod($object, $method->value, $cacheName)) { // call object method
				trigger_error($methodError, E_USER_NOTICE);
			}
		} else {
			trigger_error('Object not found', E_USER_NOTICE);
		}
	}

}

// destroy Foowd
$foowd->destroy();

// prepend function
function foowd_prepend(&$foowd, &$object, $page_title = NULL) {
	include('header.php');
}

// append function
function foowd_append(&$foowd, &$object) {
	include('footer.php');
}

?>
