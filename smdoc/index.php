<?php
// prepend function
function foowd_prepend(&$foowd, &$object, $page_title = NULL) {
	include(PATH.'layout/header.php');
}

// append function
function foowd_append(&$foowd, &$object) {
	include(PATH.'layout/footer.php');
}

define('PATH', ''); // application path

require('site-config.php');    // include site-specific config addendums
require('config.default.php'); // include config and Foowd functions

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
$foowd = new foowd(
	array(),
	array('loadUser' => TRUE)
);

// fetch object / call methods
if (isset($className->value) && !isset($objectid->value) && !isset($objectName->value)) {
     // call class method
	$foowd->callClassMethod($className->value, $method->value);
} else { 
    // fetch object
	$cacheName = getCacheName($objectid->value, $classid->value, $method->value);

    // check cache settings
	if (!readCache($cacheName)) { 
        // if couldn't read cache go fetch object
		$object = $foowd->fetchObject(
			array(
				'objectid' => $objectid->value,
				'version' => $version->value,
				'classid' => $classid->value
			)
		);
		$foowd->callMethod($object, $method->value, $cacheName); // call object method
	}
}

// footer will be appended by called method.
// if we're debugging, include debug content before $foowd object is cleaned up
if ( DEBUG ) writeDebug($foowd);

// destroy Foowd
$foowd->destroy();
?>
</body>
</html>
