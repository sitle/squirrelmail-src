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
lib.php
Foowd function library
*/

define('VERSION', '1');

function error($str) {
	die('<p>'.$str.'</p>');
}

function show($var) {
	echo '<pre>'; print_r($var); echo '</pre>';
}

/*** System functions ***/

function setVarConstOrDefault(&$value, $constant, $default) { // set var to value, or constant, or default
	if (isset($value) && $value != NULL) {
		return $value;
	} elseif (defined($constant)) {
		return constant($constant);
	} else {
		return $default;
	}
}

function setConstOrDefault($constant, $default) { // set var to constant, or default
	if (defined($constant)) {
		return constant($constant);
	} else {
		return $default;
	}
}

function getRegexLength($regex, $default) { // find the max length of string allowed by a regex
	if (preg_match('/\*/', $regex)) {
		return 0;
	} elseif (preg_match('/\+/', $regex)) {
		return 0;
	} elseif (preg_match('/\{[0-9,]*([0-9]+)\}/U', $regex, $results = array())) {
		return $results[1];
	} else {
		return $default;
	}
}

/*** Cache functions ***/

function getCacheName($objectid, $classid = '-', $method = '-') {
	global $foowd_cache;

	$objectid = (int)setVarConstOrDefault($objectid, 'DEFAULT_OBJECTID', -633383736);
	$classid = (int)setVarConstOrDefault($classid, 'DEFAULT_CLASSID', '');
	$method = setVarConstOrDefault($method, 'DEFAULT_METHOD', 'view');

	$timeOut = 0;
	$cacheName = FALSE;
	
	if (isset($foowd_cache[$objectid][$classid][$method])) {
		$timeOut = $foowd_cache[$objectid][$classid][$method];
	} elseif (isset($foowd_cache['*'][$classid][$method])) {
		$timeOut = $foowd_cache['*'][$classid][$method];
	} elseif (isset($foowd_cache[$objectid]['*'][$method])) {
		$timeOut = $foowd_cache[$objectid]['*'][$method];
	} elseif (isset($foowd_cache[$objectid][$classid]['*'])) {
		$timeOut = $foowd_cache[$objectid][$classid]['*'];
	} elseif (isset($foowd_cache['*']['*'][$method])) {
		$timeOut = $foowd_cache['*']['*'][$method];
	} elseif (isset($foowd_cache[$objectid]['*']['*'])) {
		$timeOut = $foowd_cache[$objectid]['*']['*'];
	} elseif (isset($foowd_cache['*'][$classid]['*'])) {
		$timeOut = $foowd_cache['*'][$classid]['*'];
	} elseif (isset($foowd_cache['*']['*']['*'])) {
		$timeOut = $foowd_cache['*']['*']['*'];
	}
	if ($timeOut > 0) {
		$cacheName = $objectid.'.'.$classid.'.'.$method.'.cache';
		return array('cacheName' => $cacheName, 'timeOut' => $timeOut);
	}
	return FALSE;
}

function readCache($cacheName) {
	global $foowd_cache;

	$cacheDir = setConstOrDefault('CACHE_DIR', stripslashes($_ENV['TMP']).'\\');
	if (file_exists($cacheDir.$cacheName['cacheName'])) {
		if (filemtime($cacheDir.$cacheName['cacheName']) > time() - $cacheName['timeOut']) {
			if (@readfile($cacheDir.$cacheName['cacheName'])) {
				if (defined('DEBUG') && DEBUG) echo '<p>Cache read from ', $cacheName['cacheName'], '</p>';
				return TRUE;
			}
		}
	}
	return FALSE;
}

function writeCache($cacheName, $text) {
	$cacheDir = setConstOrDefault('CACHE_DIR', stripslashes($_ENV['TMP']).'\\');
	if ($fp = fopen($cacheDir.$cacheName['cacheName'], 'w')) {
		fwrite($fp, $text);
		fclose($fp);
		if (defined('DEBUG') && DEBUG) echo '<p>Cache written to ', $cacheName['cacheName'], '</p>';
		return TRUE;
	} else {
		return FALSE;
	}
}

/*** URI functions ***/

function getURI($parameters) { // create a site URI given object details and a method
	if (defined('FILENAME')) {
		$uri = FILENAME.'?';
	} else {
		$uri = $_SERVER['PHP_SELF'].'?';
	}
	foreach ($parameters as $name => $value) {
		$uri .= $name.'='.$value.'&';
	}
	return substr($uri, 0, -1);
}

function splitPath() { // slice up path and return sections as an array
	if (isset($_SERVER['PATH_INFO'])) {
		return explode('/', substr($_SERVER['PATH_INFO'], 1));
	} else {
		return array();
	}
}

/*** Display functions ***/

function mungEmail($emailAddress) {
	switch (rand(1, 4)) {
	case 1:
		return str_replace('@', ' at ', str_replace('.', ' dot ', $emailAddress));
	case 2:
		return str_replace('@', '&amp;', str_replace('.', ',', $emailAddress));
	case 3:
		$pos = rand(1, strlen($emailAddress) - 3);
		return substr($emailAddress, 0, $pos).'[ ]'.substr($emailAddress, $pos + 3).' [\''.substr($emailAddress, $pos, 3).'\' in gap]';
	case 4:
		return str_replace('@', 'NO@SPAM', $emailAddress);
	}
}

?>
