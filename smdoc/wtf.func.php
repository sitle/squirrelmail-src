<?php
/*
	This file is part of the Wiki Type Framework (WTF).
	Copyright 2002, Paul James
	See README and COPYING for more information, or see http://wtf.peej.co.uk

	WTF is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	WTF is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WTF; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
wtf.func.php
WTF Functions
*/

/* display terminal error message */
function terminal_error($error_msg) {
 global $php_errormsg, $TRACK, $PHP_SELF;
 $track = '';
 for ($foo = 0; $foo < $TRACK[0]; $foo++) $track .= ' => '.$TRACK[$foo + 1];
 define('TERMINATEDOKAY', TRUE);
 if ($error_msg != '') {
  echo '<p><b>Terminal Program Error:</b> [', $PHP_SELF, ' ', $track, '] ', $error_msg, '</p>';
 } else {
  echo '<p><b>Terminal Program Error:</b> [', $PHP_SELF, ' ', $track, '] ', $php_errormsg, '</p>';
 }
 echo '<pre>', writeDebug(), '</pre>';
 die();
}

/* display error message */
function error($error_msg) {
 global $php_errormsg, $TRACK, $PHP_SELF;
 $track = '';
 for ($foo = 0; $foo < $TRACK[0]; $foo++) $track .= ' => '.$TRACK[$foo + 1];
 if ($error_msg != '') {
  return('<error>['.$PHP_SELF.' '.$track.'] '.$error_msg.'</error>');
 } else {
 	return('<error>['.$PHP_SELF.' '.$track.'] '.$php_errormsg.'</error>');
 }
}

/* program execution tracking */
$TRACK[0] = 0;
$DEBUGSTRING = '';

function track($level = NULL) {
	global $TRACK, $DEBUGSTRING, $startTime;
	if ($level == NULL) { // leaving section
		if (DEBUG && DEBUG_TRACE && $startTime) {
			for ($foo = 1; $foo < $TRACK[0]; $foo++) {
				$DEBUGSTRING .= '|';
			}
			$DEBUGSTRING .= '\\- ';
			if (DEBUG_TIME) {
				$microtime = explode(' ', microtime());
				$time = $microtime[0] + $microtime[1];
				$DEBUGSTRING .= substr(($time - $startTime), 0, 5);
			}
			$DEBUGSTRING .= '<br/>';
		}
		$TRACK[0]--;
	} else { // entering section
		$TRACK[0]++;
		$TRACK[$TRACK[0]] = $level;
		if (DEBUG && DEBUG_TRACE) {
			for ($foo = 1; $foo < $TRACK[0]; $foo++) {
				$DEBUGSTRING .= '|';
			}
			$DEBUGSTRING .= '/- ';
			if (DEBUG_TIME) {
				if ($startTime) {
					$microtime = explode(' ', microtime());
					$time = $microtime[0] + $microtime[1];
					$DEBUGSTRING .= substr(($time - $startTime), 0, 5).': ';
				} else {
					$DEBUGSTRING .= '0.000: ';
				}
			}
			if (func_num_args() > 1) {
				$args = func_get_args();
				array_shift($args);
				$parameters = '';
				foreach ($args as $key => $arg) {
					if ($arg == NULL) {
						$args[$key] = 'NULL';
					} elseif ($arg === TRUE) {
						$args[$key] = 'TRUE';
					} elseif ($arg === FALSE) {
						$args[$key] = 'FALSE';
					}
					$parameters .= $args[$key].', ';
				}
				$parameters = substr($parameters, 0, -2);
			} else {
				$parameters = '';
			}
			$DEBUGSTRING .= $level.'('.$parameters.')<br />';
		}
	}
}

/* database execution tracking */
$DBTRACKNUM = 0;

function DBTrack($SQLString) {
	global $DBTRACKNUM, $DBTRACKSTRING, $DEBUGSTRING, $TRACK;
	$DBTRACKNUM++;
	if (DEBUG_SQL) {
		for ($foo = 1; $foo < $TRACK[0]; $foo++) {
			$DEBUGSTRING .= '|';
		}
		$DEBUGSTRING .= '|  '.htmlspecialchars($SQLString).'<br />';
	}
}

function writeDebug() {
	global $wtf, $DEBUGSTRING, $DBTRACKNUM;
	track('writeDebug');
	if (DEBUG) {
		$output = '<debug>';

		if (DEBUG_SQL) {
			$output .= '*** DB executions ***<br />';
			$output .= $DBTRACKNUM.'<br />';
		}
		if ($DEBUGSTRING != '') {
			$output .= '*** Execution history ***<br />'.$DEBUGSTRING;
		}
		if (DEBUG_VAR) {
			ob_start();
			echo "*** Variables & Objects ***\n";
			if (HTTPAUTH) {
			echo "***** AUTH *****\n";
				echo '[username] => ', $_SERVER['PHP_AUTH_USER'], "\n";
				echo '[password] => ', $_SERVER['PHP_AUTH_PW'], "\n";
			}
			echo "***** REQUEST *****\n";
			print_r($_REQUEST);
			echo "***** WTF *****\n";
			print_r($wtf);
			$output .= htmlspecialchars(ob_get_contents());
			ob_end_clean();
		}
		$output .= '</debug>';
	} else {
		$output = '';
	}
	track();
	return $output;
}

/* execution time */
function exectime_start() {
	global $startTime;
	$microtime = explode(' ', microtime());
	$startTime = $microtime[0] + $microtime[1];
}

function exectime_finish() {
	global $startTime;
	$microtime = explode(' ', microtime());
	$endTime = $microtime[0] + $microtime[1];
	$execTime = $endTime - $startTime;
	if ($execTime > 1) $execTime--;
	if ($execTime < 0) $execTime++;
	return substr($execTime, 0, 5);
}

/* add XML entities to string */
function addentities($text) {
//	return htmlentities($text);
	return htmlspecialchars($text);
}

/* add amp entity to string */
function addamp($text) {
	return str_replace('&', '&amp;', $text);
}

/* add lt entity to string */
function addlt($text) {
	return str_replace('<', '&lt;', $text);
}

/* add gt entity to string */
function addgt($text) {
	return str_replace('>', '&gt;', $text);
}

/* replace new lines with new line string */
function replaceNewLines($text) {
	if (CONVERTNEWLINESTO) {
		return str_replace("\n", CONVERTNEWLINESTO, $text);
	} else {
		return $text;
	}
}

/* strip slashes */
function safeStripSlashes($text) {
  if (get_magic_quotes_gpc()) {
    return stripslashes($text);
  } else {
    return $text;
  }
}

/* get ID from title */
function getIDFromName($name) {
	return crc32(strtolower($name));
}

/* get the database table for an object type */
function getTable($classid) {
	global $CLASSTABLE;
	if (isset($CLASSTABLE[$classid])) {
		return $CLASSTABLE[$classid];
	} else {
		return OBJECTTABLE;
	}
}

/* get parameters for PI from PI data string */
function getPIParameters($data) {
	if (preg_match_all('/([a-zA-Z]*)="(.*)"/U', $data, $matchArray)) {
		$paraArray = array();
		foreach($matchArray[1] as $key => $keyArray) {
			$paraArray[$keyArray] = $matchArray[2][$key];
		}
		return $paraArray;
	} else {
		return FALSE;
	}
}

/* turn an e-mail address into something unscapeable */
function encodeEmail($email) {
	return preg_replace('/@/', ' at ', $email);
}

function encodeIP($ip) {
	$ipPos = strrpos($ip, '.');
	return substr($ip, 0, $ipPos).'.***';
}

/* return complete tag from name and attrs array */
function createTag($name, $attrs) {
	track('createTag', $name);
	
	$output = '<'.$name;
	foreach($attrs as $key => $value) {
		$output .= ' '.$key.'="'.htmlspecialchars($attrs[$key]).'"';
	}
	$output .= '>';
	
	track();
	return $output;
}

/* replace quick formatting */
function processQuickFormat($data, $thingid, $tagName = NULL) {
	global $QUICKFORMAT, $NOQUICKFORMATTAG, $NOQUICKFORMATTHINGID;
	track('processQuickFormat');

	if (QUICKFORMATS) {
		if (!(isset($NOQUICKFORMATTHINGID) && in_array($thingid, $NOQUICKFORMATTHINGID)) && !(isset($tagName) && isset($NOQUICKFORMATTAG) && in_array($tagName, $NOQUICKFORMATTAG))) {
			foreach ($QUICKFORMAT as $quicklink) {
				$data = preg_replace($quicklink['regex'], $quicklink['replace'], $data);
			}
		}
	}
	
	if (SPLITLONGWORDS) {
		$data = sliceLongWords($data, MAXCONTENTWIDTH);
	}
	
	track();
	return $data;
}

/* highlight tags in string */
function syntaxHightlight($text) {
	track('syntaxHighlight');
	
	$newText = '';
	$foo = 0;
	foreach(explode("\n", $text) as $line) {
		$foo++;
		$newText .= '<syntax_errorline linenumber="'.$foo.'"><![CDATA['.$line.']]></syntax_errorline>';
	}
	$text = $newText;
	$text = preg_replace("/&amp;lt;(.*?)&amp;gt;/", "<syntax_tag>&amp;lt;\\1&amp;gt;</syntax_tag>", $text);
	
	track();
	return $text;
}

/* split long non-breaking words in two */
function sliceLongWords($text, $maxlength) {
	track('sliceLongWords');
/*	
	$textlen = strlen($text);
	$lastspace = 0;
	$intag = FALSE;
	$inentity = FALSE;
	
	for ($counter = 0; $counter < $textlen; $counter++) {
		if (substr($text, $counter, 1) == '<') $intag = TRUE;
		if (substr($text, $counter, 1) == '&') $inentity = TRUE;
		if (!$intag && !$inentity) $lastspace++;
		if (substr($text, $counter, 1) == '>') $intag = FALSE;
		if (substr($text, $counter, 1) == ';') $inentity = FALSE;
		if (((substr($text, $counter, 1) == ' ' || substr($text, $counter, 1) == chr(11) || substr($text, $counter, 1) == "\n")) && !$intag && !$inentity) {
			$lastspace = 0;
		}
		if ($lastspace > $maxlength && !$intag && !$inentity) {
			$text = substr($text, 0, $counter).' '.substr($text, $counter, $textlen - $counter);
			$lastspace = 0;
			$textlen++;
		}
	}
//	$text = preg_replace("/([^[:space:]]{".$maxlength.",}?)/", "\\1 ", $text);
*/
	$text = wordwrap($text , $maxlength, ' ', 1);
	track();
	return $text;
}

/* build an select option from the supplied values */
function drawOption($optionTag, $selectedTag, $name, $value, $selected) {
	if ($value == $selected) {
		return '<'.$selectedTag.' value="'.$value.'">'.$name.'</'.$selectedTag.'>';
	} else {
		return '<'.$optionTag.' value="'.$value.'">'.$name.'</'.$optionTag.'>';
	}
}

/* find out if a class is a relative of another class */
function isRelative($child, $relative) {
	if ($child == $relative) { // relative is child, so true
		return TRUE;
	}
	$parent = get_parent_class($child);
	if (!$parent) { // no parent, return false
		return FALSE;
	} elseif ($parent == $relative) { // found relative
    return TRUE;
	} else { // do recursive lookup on parent
		return isRelative($parent, $relative); /*** WARNING: recursion in action ***/
	}
}

/* get all children of a parent class */
function getChildren($parent) {
	global $IGNORECLASSARRAY;
	$arrayOfClasses = array_diff(get_declared_classes(), $IGNORECLASSARRAY);
	$arrayOfResults = array();
	foreach ($arrayOfClasses as $class) {
		if (isRelative($class, $parent)) {
			$arrayOfResults[] = $class;
		}
	}
	return $arrayOfResults;
}

/* get where clause for loading a thing */
function getWhere($thingid, $className, $workspaceid = NULL) {
	global $wtf;
	track('getWhere', $thingid, $className);
	$where = array('objectid = '.$thingid);

    if ($wtf->op == 'delete' && $wtf->class == 'user') {
        // If we are currently trying to delete the user (in whatever workspace),
        // don't add anything to the query
	} elseif (isset($workspaceid)) {
        // else, if we have a specific workspace in mind,
        // only look for docs in that workspace
        $where[] = 'AND';
		$where[] = 'workspaceid = '.$workspaceid;
	} elseif ($wtf->user->workspaceid == 0) {
        // else, if the user is in the main workspace,
        // only query elements that are also in the main workspace
        $where[] = 'AND';
		$where[] = 'workspaceid = 0';
	} else {
        // else look for things in the user's current workspace
        // and (if not in the current workspace) in the main workspace.
        $where[] = 'AND';
		$where[] = '(workspaceid = '.$wtf->user->workspaceid;
		$where[] = 'OR';
		$where[] = 'workspaceid = 0)';
	}
	$where[] = 'AND';

	$where[] = '(';							
	if (!isset($className)) {
		$className = array('content');
	} elseif (!is_array($className)) {
		$className = array($className);
	}
/*** REMOVED ***
Retrieving of object classes that are the children of the given class names has been removed due to the fact that you
could have an object and a child object with the same ID, looking for the parent you may actually retrieve the child and
not know about it.*/
	foreach ($className as $class) {
		if ($class) {
			$classAndChildren = getChildren($class);
		}
		foreach ($classAndChildren as $className) {
			$classid = getIDFromName($className);
			$table = getTable($classid);
			$where[] = $table.'.classid = '.$classid;
			$where[] = 'OR';
		}
	}
/*
	foreach ($className as $class) {
		$classid = getIDFromName($class);
		$table = getTable($classid);
		$where[] = $table.'.classid = '.$classid;
		$where[] = 'OR';
	}
/* end replace */

	array_pop($where);
	$where[] = ')';

	track(); return $where;
}

/* if user has permissions on an object */
function hasPermission(&$obj, &$user, $permissionType = 'editGroup') {
	if ($user->inGroup($obj->$permissionType) || ($obj->$permissionType == AUTHOR && ($user->objectid == $obj->creatorid || $user->inGroup(AUTHOR)))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/* get a value from GET, POST, attrs, or COOKIE */
function getValue($name, $default) {
	global $wtf;

	if (isset($wtf) && isset($wtf->thingtitle)) { // get thing title if wtf object has been created
		$thingName = strtolower($wtf->thingtitle).'_';
	}
// GET
	if (isset($thingName) && isset($_GET[$thingName.$name])) {
		return safeStripSlashes($_GET[$thingName.$name]);
	} elseif (isset($_GET[$name])) {
		return safeStripSlashes($_GET[$name]);
// POST
	} elseif (isset($thingName) && isset($_POST[$thingName.$name])) {
		return safeStripSlashes($_POST[$thingName.$name]);
	} elseif (isset($_POST[$name])) {
		return safeStripSlashes($_POST[$name]);
// COOKIE
	} elseif (isset($thingName) && isset($_COOKIE[$thingName.$name])) {
		return safeStripSlashes($_COOKIE[$thingName.$name]);
	} elseif (isset($_COOKIE[$name])) {
		return safeStripSlashes($_COOKIE[$name]);
// DEFAULT
	} else { // return default value
		return $default;
	}
}

?>
