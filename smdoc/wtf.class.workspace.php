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
wtf.class.workspace.php
Workspace Class
*/

$HARDCLASS[-1919680487] = 'workspace';

if (!defined('WORKSPACECREATE')) define('WORKSPACECREATE', GODS);

class workspace extends thing {

/*** Constructor ***/

	function workspace(
		&$user,
		$title = NULL,
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP
	) {
		track('workspace::workspace', $title, $viewGroup, $editGroup, $deleteGroup);
		parent::thing($user, $title, $viewGroup, $editGroup, $deleteGroup);
		track();
	}

/*** Member Functions ***/

	function placeInWorkspace(&$obj) { // place a thing in this workspace
		global $wtf;
		track('workspace::placeInWorkspace');
		if ($wtf->user->inGroup($this->editGroup) && ($obj->objectid != -1672260811 || $obj->classid != USERCLASSID)) { // if has access and object is not anonymous user
			$obj->workspaceid = $this->objectid;
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}
	
	function getWorkspaces() { // static member to get an array of workspace id's and names
		global $conn;
		track('workspace::getWorkspaces');
		$workspaces = array(0 => 'Main');
		if ($query = DBSelect($conn, OBJECTTABLE, NULL, array('objectid', 'title'), array('classid = '.getIDFromName('workspace')), NULL, array('title'), NULL)) {
			$numberOfRecords = getAffectedRows($conn);
			if ($numberOfRecords > 0) {
				for ($foo = 1; $foo <= $numberOfRecords; $foo++) {
					$record = getRecord($query);
					$workspaces[intval($record['objectid'])] = $record['title'];
				}
			}
		}
		return $workspaces;
	}
	
	function drawForm($url, $title = NULL) {
		global $wtf;
		if (isset($this)) {
			$objectid = $this->objectid;
			$version = $this->version;
			$updatorid = $this->updatorid;
			if ($title == NULL) $title = $this->title;
		} else {
			$objectid = '';
			$version = 1;
			$updatorid = NULL;
			if ($title == NULL) $title = '';
		}
		echo '<workspace_form url="', $url, '" submit="submit">';
		echo '<workspace_title name="title" maxlength="', MAXTITLELENGTH, '">', $title, '</workspace_title>';
		echo '</workspace_form>';
	}
	
/*** Methods ***/

	function method_view() {
		global $wtf;
		track('workspace::method::view');
		if (getValue('version', FALSE)) {
			echo '<thing_info version="'.$this->version.'" class="'.get_class($this).'"/>';
		}
		if ($wtf->user->workspaceid == $this->objectid) {
			$wtf->user->delete(); // remove obj in old workspace from DB, will create a new DB entry upon save.
			$wtf->user->workspaceid = 0;
			$wtf->user->save();
			echo 'You are now back in the main workspace.';
		} elseif (hasPermission($this, $wtf->user, 'viewGroup')) { // check permission
			$wtf->user->delete(); // remove obj in old workspace from DB, will create a new DB entry upon save.
			$wtf->user->workspaceid = intval($this->objectid);
			$wtf->user->save();
			echo 'You are now in workspace "'.$this->title.'".';
		} else {
			echo 'You do not have permission to enter workspace "'.$this->title.'".';
		}
		track();
	}

// create
	function method_create($thingName = NULL, $objectName = 'workspace') { // this is both a method and a static member
		global $conn, $wtf;
		track('workspace::method::create');

		if ($wtf->user->inGroup(WORKSPACECREATE)) { // check permission

			if (isset($this)) {
				$url = THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=create';
				$objectName = get_class($this);
			} else {
				$url = THINGURI.$thingName.'&amp;class=hardclass';
			}

			$create = getValue('submit', FALSE);
			$title = getValue('title', FALSE);

			if ($create) { // is action to do

	// create object
				$thing = new $objectName(
					$wtf->user,
					$title
				);
				if ($thing && $thing->objectid != 0) { // create thing
					$thing->save();
					header('Location: '.THINGIDURI.$thing->objectid.'&class='.get_class($thing));
					exit;
				} else {
					echo '<create_failed message="Could not create object ', htmlspecialchars($title), '"/>';
				}
			} else { // display empty form
				workspace::drawForm($url, $title);
			}
		} else {
			echo '<create_permission/>';
		}
		track();
	}
}

// formatting
$FORMAT = array_merge($FORMAT, array(
	'workspace_form' => '<form method="post" action="{url}">',
	'/workspace_form' => '<p><input type="submit" name="{submit}" value="Create Workspace" /></p></form>',
	'workspace_title' => '<p>Title: <input type="text" name="{name}" size="50" maxlength="{maxlength}" value="',
	'/workspace_title' => '"/></p>',
));
?>