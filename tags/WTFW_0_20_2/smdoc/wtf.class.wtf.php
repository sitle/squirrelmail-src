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
wtf.class.wtf.php
WTF Class
*/

$FORMAT = array(); // initiate format array
$NOPARSETAG[] = 'debug'; // stop parsing debug data from quick formatting
$IGNORECLASSARRAY = get_declared_classes(); // get defined classes that are not WTF classes
$ANYVERSIONMETHODS = array('view', 'admin', 'clone', 'revert', 'diff'); // methods that can be called on any version of an object (all other methods will automagically operate on the latest version of an object).

class wtf {

	var $output; // output contents
		
	var $user; // user
	var $thing; // thing
	
	var $thingid; // thingid
	var $thingtitle; // title of thing
	var $version; // version of thing
	var $class; // class of thing
	
	var $op; // oepration to perform on thing

/*** Initialise WTF ***/	
	function wtf($username = NULL, $password = NULL) {
		global $ANYVERSIONMETHODS;
		track('wtf::wtf', $username, $password);
		exectime_start(); // set execution start time
		ob_start(); // initiate output buffer
		// get thing identifiers
		$this->thingid = getValue(THINGID, NULL, NULL, NULL);
		if (!$this->thingid) {
			$this->thingtitle = htmlspecialchars(getValue(THING, NULL, NULL, NULL));
			if ($this->thingtitle != NULL) {
				$this->thingid = getIDFromName($this->thingtitle);
			} else {
				$this->thingtitle = DEFAULTPAGENAME;
				$this->thingid = getIDFromName(DEFAULTPAGENAME);
			}
		}
		$this->class = getValue('class', DEFAULTCLASS);
		$this->op = getValue('op', 'view');
		if (in_array($this->op, $ANYVERSIONMETHODS)) {
			$this->version = getValue('version', 0);
		} else {
			$this->version = 0;
		}
		$this->user = &wtf::loadUser($username, $password); // get users details
		track();
	}

/*** Load thing into WTF object ***/
	function loadThing() {
		track('wtf::loadThing');
		if (!$this->thing = &wtf::loadObject($this->thingid, $this->version, $this->class)) {
			$this->thing = &wtf::loadObject(NOTHINGFOUNDID, 0, 'content');
		}
		if (!$this->thing) {
			terminal_error('Could not load thing #'.$this->thingid.' and failed to load Nothing Found thing, aborting.');
		}
		track();
	}
	
/*** Do operation on thing ***/
	function doOp() {
		$op = 'method_'.$this->op;
		if (method_exists($this->thing, $op)) {
			$this->thing->{$op}();
		} else {
			echo '<error>Unknown operation "'.$this->op.'" for thing #', $this->thingid, ' (', $this->class, ').</error>';
		}
	}
	
/*** Finalise WTF ***/
	function display() {
		global $EXECTIME;
		track('wtf::display');
		$this->output = ob_get_contents(); // get output buffer
		ob_end_clean(); // empty output buffer
		if (!isset($this->user) || $this->user == FALSE) {
			terminal_error('Can not display, no user loaded.');
		} elseif (!isset($this->thing) || $this->thing == FALSE) {
			terminal_error('Can not display, no thing loaded.');
		} else {
			if ($this->user->objectid == ANONYMOUSUSERID) { // if anon user set username back
				$this->user->title = ANONYMOUSUSERNAME;
			}
			if (dbdate2unixtime($this->user->updatorDatetime) < time() - USERTIMEOUT) { // user passed time out, refresh update time
				$this->user->update($this->user, FALSE);
				$this->user->save();
			}
			$EXECTIME = exectime_finish(); // calculate execution time
			render($this); // transform output and display
		}
		track();
	}

/*** Load an object ***/
	function loadObject($objectid, $version, $classes = 'thing', $mainWorkspace = FALSE) { // load an object from db into memory
		global $conn, $wtf, $HARDTHING;
		track('wtf::loadObject', $objectid, $version, $classes, $mainWorkspace);

		if (is_numeric($objectid) && is_numeric($version)) {
		
			$objectid = intval($objectid);

			if ($mainWorkspace) {
				$workspaceid = 0;
			} elseif (isset($wtf->user->workspaceid) && is_numeric($wtf->user->workspaceid)) {
				$workspaceid = $wtf->user->workspaceid;
			} else {
				$workspaceid = 0;
			}

			if (isset($HARDTHING[$objectid]) && $classes = 'content') { // load function from db
				$thing = new hardthing($wtf->user, $objectid);
				track(); return $thing;
				
			} else { // retrieve serialized object from db

// find out which thing types of object to return
				if (!is_array($classes)) {
					$classes = array($classes);
				}
				$table = getTable(getIDFromName($classes[0]));
				$where = getWhere($objectid, $classes, $workspaceid);

// get thing(s)
				if ($version == 0) { // get latest version
					$query = DBSelect($conn, $table, NULL,
					array($table.'.object'),
					$where,
					NULL,
					array($table.'.version DESC'),
					1);
				} else { // get specified version
					$where[] = 'AND';
					$where[] = $table.'.version = '.$version;
					$query = DBSelect($conn, $table, NULL,
					array($table.'.object'),
					$where,
					NULL,
					NULL,
					NULL);
				}
 
 // if retrieve successful, unserialize it
				if (getAffectedRows() > 0) {
					$record = getRecord($query);
					$serializedObj = $record['object'];
					$obj = unserialize($serializedObj);
					track(); return $obj;
				} elseif ($workspaceid != 0) { // if not already looking in main workspace
					$obj = &wtf::loadObject($objectid, $version, $classes, TRUE); /*** WARNING: recursion in action ***/
					track(); return $obj;
//				} elseif ($objectid != NOTHINGFOUNDID && isRelative($classes, 'content')) { // protect from not finding nothing found object and recursing forever
//					$obj = &wtf::loadObject(NOTHINGFOUNDID, 0, 'content', TRUE); /*** WARNING: recursion in action ***/
//					track(); return $obj;
				} else {
					track(); return FALSE;
				}
			}

		} else {
			track(); return FALSE;
		}
	}

/*** Load a class ***/
	function loadClass($title, $version) { // load a class from db and execute it
		global $conn, $wtf;
		track('wtf::loadClass', $title, $version);
// check if class is already loaded
		if (!class_exists($title)) {
// calculate objectid
			$objectid = getIDFromName($title);
// calculate classid
			$classid = getIDFromName('definition');
// get table name
			$table = getTable($classid);
// set where clause
			$where = array(
				$table.'.objectid = '.$objectid,
				'AND',
				$table.'.classid = '.$classid,
				'AND'
			);
			if ($wtf->user->workspaceid == 0) {
				$where[] = 'workspaceid = 0';
			} else {
				$where[] = '(workspaceid = '.$wtf->user->workspaceid;
				$where[] = 'OR';
				$where[] = 'workspaceid = 0)';
			}

// retrieve serialized class from db
			if ($version == 0) { // get latest version
				$query = DBSelect($conn, $table, NULL,
				array($table.'.object'),
				$where,
				NULL,
				array($table.'.version DESC'),
				1);
			} else { // get specified version
				$where[] = 'AND';
				$where[] = $table.'.version = '.$version;
				$query = DBSelect($conn, $table, NULL,
				array($table.'.object'),
				$where,
				NULL,
				NULL,
				NULL);
			}
// if retrieve successful, unserialize it
			if (getAffectedRows() > 0) {
				$record = getRecord($query);
				$serializedClass = $record['object'];
				$class = unserialize($serializedClass);
// if it inherits from another class, find out and load it now
				if (preg_match_all('|class ([-_a-zA-Z0-9]*) extends ([-_a-zA-Z0-9]*) ?{|', $class->class, $pregMatches)) { // i'd rather do this with catching errors on the eval, but that can't be done
					if ($pregMatches[1] != $pregMatches[2]) {
						foreach($pregMatches[2] as $className) {
							if ($className != $class->title && !class_exists($className)) { /*** WARNING: recursion in action ***/
								wtf::loadClass($className, 0);
							}
						}
					}
				}
// execute class definition
				$classExecutionResult = eval($class->class);
				if ($classExecutionResult === FALSE) {
					track(); return FALSE;
				} else {
					track(); return TRUE;
				}
			} else {
				track(); return FALSE;
			}
		} else {
			track(); return FALSE;
		}
	}
	
/*** Load a user ***/
	function loadUser($username = NULL, $password = NULL, $workspaceid = 0) { // get a users details and load their user object
		global $conn;
		track('wtf::loadUser', $username, $password);
		
		if (HTTPAUTH && $username == NULL && $password == NULL && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { // user auth data
			$username = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];
		}
		$loggingInUser = ($username != NULL) && ($password != NULL); // user supplied data
		if (USECOOKIE) { // user cookie data
			$cookieUser = isset($_COOKIE['userid']) && isset($_COOKIE['password']);
		} else {
			$cookieUser = FALSE;
		}

		if ($loggingInUser || $cookieUser) {
			$table = getTable(USERCLASSID);
			if ($loggingInUser) { // text string username and password
				$where = array();
				$where[] = $table.'.title = "'.$username.'"';
				$where[] = 'AND (';
				$classAndChildren = getChildren('user');
				foreach ($classAndChildren as $className) {
					$classid = getIDFromName($className);
					$table = getTable($classid);
					$where[] = $table.'.classid = '.$classid;
					$where[] = 'OR';
				}
				array_pop($where);
				$where[] = ')';
				if ($workspaceid != 0) {
					$where[] = 'AND';
					$where[] = 'workspaceid = '.$workspaceid;
				}
				$query = DBSelect($conn, OBJECTTABLE, NULL,
					array($table.'.object'),
					$where,
					NULL,
					array($table.'.version DESC'),
					1
				);
				if (getAffectedRows() > 0) {
					$record = getRecord($query);
					$serializedObj = $record['object'];
					$obj = unserialize($serializedObj);
					if ($obj->password == md5(PASSWORDSALT.$password)) {
// create cookie
						if (USECOOKIE) {
							setcookie('userid', $obj->objectid, time() + COOKIELIFE, COOKIEPATH, COOKIEDOMAIN);
							setcookie('password', $obj->password, time() + COOKIELIFE, COOKIEPATH, COOKIEDOMAIN);
						}
						track(); return $obj;
					} else {
						unset($obj);
					}
				}
			} elseif ($cookieUser) { // cookie values
				$userid = $_COOKIE['userid'];
				$password = $_COOKIE['password'];
				$where = array();
				$where[] = $table.'.objectid = '.$userid;
				$where[] = 'AND (';
				$classAndChildren = getChildren('user');
				foreach ($classAndChildren as $className) {
					$classid = getIDFromName($className);
					$table = getTable($classid);
					$where[] = $table.'.classid = '.$classid;
					$where[] = 'OR';
				}
				array_pop($where);
				$where[] = ')';
				if ($workspaceid != 0) {
					$where[] = 'AND';
					$where[] = 'workspaceid = '.$workspaceid;
				}
				$query = DBSelect($conn, $table, NULL,
					array($table.'.object'),
					$where,
					NULL,
					array($table.'.version DESC'),
					1
				);
				if (getAffectedRows() > 0) {
					$record = getRecord($query);
					$serializedObj = $record['object'];
					$obj = unserialize($serializedObj);
					if ($obj->password == $password) {
						track(); return $obj;
					} else {
						unset($obj);
					}
				}
			}
		}
// not returned with user, so return anonymous user object
		$obj = &wtf::loadObject(ANONYMOUSUSERID, 0, 'user');
		if (isset($obj)) {
			if (isset($_SERVER['REMOTE_ADDR'])) {
				if (USEHOSTNAMEFORANONYMOUSUSER) {
					$obj->title = gethostbyaddr($_SERVER['REMOTE_ADDR']);
				} elseif (USEHOSTIPFORANONYMOUSUSER) {
					$obj->title = $_SERVER['REMOTE_ADDR'];
				} else {
					$obj->title = ANONYMOUSUSERNAME;
				}
			} else {
				$obj->title = ANONYMOUSUSERNAME;
			}
// kill cookie
			if (USECOOKIE) {
				setcookie('userid', '', time() - 3600, COOKIEPATH, COOKIEDOMAIN);
				setcookie('password', '', time() - 3600, COOKIEPATH, COOKIEDOMAIN);
			}
		}
		track(); return $obj;
	}

}

/*** Callback function for init class error ***/
ini_set('unserialize_callback_func', 'loadClassCallback');

function loadClassCallback($className) {
	track('loadClassCallback', $className);
	if (wtf::loadClass($className, 0)) {
		track(); return TRUE;
	} else {
		terminal_error('Could not load class "'.$className.'" from database, class does not exist!');
	}
}

?>
