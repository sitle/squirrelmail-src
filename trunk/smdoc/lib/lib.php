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
/*** Debugging functions ***/

function show(&$var) {
    echo '<pre>'; print_r($var); echo '</pre>';
}

/*** System functions ***/

function getVarConstOrDefault(&$value, $constant, $default) { // set var to value, or constant, or default
    if (isset($value) && $value !== NULL && $value !== '') {
        return $value;
    } elseif (defined($constant)) {
        return constant($constant);
    } else {
        return $default;
    }
}

function getVarOrConst(&$value, $constant) { // set var to value, or constant, or throw error
    if (isset($value) && $value !== NULL && $value !== '') {
        return $value;
    } elseif (defined($constant)) {
        return constant($constant);
    } else {
        trigger_error('Constant "'.$constant.'" not defined.', E_USER_ERROR);
    }
}

function getConstOrDefault($constant, $default) { // set var to constant, or default
    if (defined($constant)) {
        return constant($constant);
    } else {
        return $default;
    }
}

function getVarOrDefault(&$value, $default) { // set var to value, or default value
    if (isset($value) && $value !== NULL && $value !== '') {
        return $value;
    } else {
        return $default;
    }
}

function getConst($constName) { // set to constant, or throw error
    if (defined($constName)) {
        return constant($constName);
    } else {
        trigger_error('Constant "'.$constName.'" not defined.', E_USER_ERROR);
    }
}

function setConst($constName, $value) { // set constant
    $constName = strtoupper($constName);
    if (!defined($constName)) {
        define($constName, $value);
    }
}

function getRegexLength($regex, $default) { // find the max length of string allowed by a regex
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

function getPermission($className, $methodName, $type = 'object', $default = NULL) {
    if (strtolower($type) == 'class') {
        $type = 'CLASS';
    } else {
        $type = 'OBJECT';
    }
    $constName = 'PERMISSION_'.strtoupper($className).'_'.$type.'_'.strtoupper($methodName);
    if (isset($default) && $default !== NULL && $default !== '') {
        return $default;
    } elseif (defined($constName)) {
        return constant($constName);
    } elseif ($className == 'foowd_object') { // none found
        return 'Everyone';
    } else { // look at parent
        return getPermission(get_parent_class($className), $methodName, $type);
    }
}

function setPermission($className, $type, $methodName, $value = 'Everyone') { // set method permission
    if (strtolower($type) == 'class') {
        $type = 'CLASS';
    } else {
        $type = 'OBJECT';
    }
    setConst('PERMISSION_'.strtoupper($className).'_'.$type.'_'.strtoupper($methodName), $value);
}

function setClassMeta($className, $description) { // set class meta data
    $classid = crc32(strtolower($className));
    setConst('META_'.$classid.'_CLASSNAME', $className);
    setConst('META_'.$classid.'_DESCRIPTION', $description);
    setConst('META_'.strtoupper($className).'_CLASS_ID', $classid);
}

function getClassName($classid) {
    if (defined('META_'.$classid.'_CLASSNAME')) {
        return constant('META_'.$classid.'_CLASSNAME');
    } else {
        trigger_error('Could not find class name from class ID '.$classid);
    }
}

function getClassDescription($classid) {
    if (defined('META_'.$classid.'_DESCRIPTION')) {
        return constant('META_'.$classid.'_DESCRIPTION');
    } else {
        return 'Unknown';
    }
}

/*** Cache functions ***/

function getCacheName($objectid, $classid = '-', $method = '-') {
    global $foowd_cache;

    $objectid = (int)getVarOrConst($objectid, 'DEFAULT_OBJECTID');
    $classid = (int)getVarConstOrDefault($classid, 'DEFAULT_CLASSID', '');
    $method = getVarConstOrDefault($method, 'DEFAULT_METHOD', 'view');

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

function getTempDir() {
    if (isset($_ENV['TMP'])) {
        return stripslashes($_ENV['TMP']).'\\';
    } elseif (isset($_ENV['TEMP'])) {
        return stripslashes($_ENV['TEMP']).'\\';
    } else {
        return substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/')).'/temp/';
    }
}

function readCache($cacheName) {
    global $foowd_cache;

    $cacheDir = getConstOrDefault('CACHE_DIR', getTempDir());
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
    $cacheDir = getConstOrDefault('CACHE_DIR', getTempDir());
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

function getURI($parameters = NULL) { // create a site URI given object details and a method
    if (defined('GETURI') && function_exists(GETURI)) {
        return call_user_func(GETURI, $parameters);
    } else { // create querystring uri
        if (defined('FILENAME')) {
            $uri = 'http://'.$_SERVER['HTTP_HOST'].FILENAME.'?';
        } else {
            $uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?';
        }
        if (is_array($parameters)) {
            foreach ($parameters as $name => $value) {
                $uri .= $name.'='.$value.'&';
            }
        }
        return substr($uri, 0, -1);
    }
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

/*** Locale functions ***/

if (!function_exists('_')) { // no gettext support, so define our own "_" function
    function _($text) {
        global $LANG;
        if (isset($LANG[$text])) {
            return $LANG[$text];
        } else {
            return $text;
        }
    }
}

/*** Settings ***/
define('DATABASE_DATE', 'Y-m-d H:i:s'); // date format for database
define('DATABASE_DATETIME_DATATYPE', 'DATETIME'); // name of database date time data type

/*** Datetime Functions ***/

/* turns a database datetime into a formatted date string */
function dbdate2string($datetime, $format = DATEFORMAT) 
{
    if ($datetime = dbdate2unixtime($datetime)) {
        return date($format, $datetime);
    } else {
        return FALSE;
    }
}

/* turns a database datetime into a unix timestamp */
function dbdate2unixtime($datetime) 
{
    if (!isemptydbdate($datetime)) {
        return mktime(substr($datetime, 11, 2), substr($datetime, 14, 2), substr($datetime, 17, 2), substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4));
    } else {
        return FALSE;
    }
}

/* turns a unix timestamp into a database datetime */
function unixtime2dbdate($datetime) 
{
    return date( 'Y-m-d H:i:s', $datetime);
}

/* returns if a database datetime is empty */
function isemptydbdate($datetime) 
{
    if ($datetime == 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}


?>
