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
wtf.class.thing.php
Thing Class
*/

$HARDCLASS[1531718787] = 'thing';

class thing { // static base class

	var $classid;
	var $objectid, $title, $version = 1;
	var $workspaceid = 0;
    var $sectionid = 0;
	var $creatorid, $creatorName, $creatorHomeid, $creatorDatetime;
	var $updatorid, $updatorName, $updatorHomeid, $updatorDatetime;
	var $viewGroup = DEFAULTVIEWGROUP;
	var $editGroup = DEFAULTEDITGROUP;
	var $deleteGroup = DEFAULTDELETEGROUP;
	var $adminGroup = DEFAULTADMINGROUP;
	
	var $indexes;

/*** Constructor ***/

	function thing(
		&$user,
		$title = NULL,
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP,
		$adminGroup = DEFAULTADMINGROUP,
		$allowDuplicateTitle = ALLOWDUPLICATETITLES
	) {
		global $conn;
		track('thing::thing', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup, $allowDuplicateTitle);

// set indexes
		$this->indexes['objectid'] = 'INT NOT NULL';
		$this->indexes['version'] = 'INT UNSIGNED NOT NULL DEFAULT 1';
		$this->indexes['classid'] = 'INT NOT NULL DEFAULT 0';
		$this->indexes['workspaceid'] = 'INT NOT NULL DEFAULT 0';
		$this->indexes['title'] = 'VARCHAR('.MAXTITLELENGTH.') NOT NULL';
		$this->indexes['updatorDatetime'] = 'DATETIME NOT NULL';
        $this->indexes['sectionid'] = 'INT NOT NULL DEFAULT 0';

// set initial values
		if (
			strlen($title) > 0 &&
			strlen($title) < MAXTITLELENGTH &&
			preg_match('/'.TITLEMATCHREGEX.'/', $title)
		) {
			$this->classid = getIDFromName(get_class($this));
// get table
			$table = getTable($this->classid);
// set workspace
			$this->workspaceid = $user->workspaceid;
// set objectid, loop incrementing id until unique id is found (just in case, crc32 is not collision proof)
			$this->objectid = getIDFromName($title);
// check objectid
			while (DBSelect($conn, $table, NULL, array('objectid'), array(
				$table.'.objectid = '.$this->objectid,
				'AND',
				$table.'.classid = '.$this->classid,
				'AND',
				$table.'.workspaceid = '.$this->workspaceid
			), NULL, NULL, 1)) {
				if (!$allowDuplicateTitle) {
					$this->objectid = 0;
					return FALSE;
				}
				$this->objectid++;
			}
			$this->title = htmlspecialchars($title);
            $this->sectionid = 0;
			$this->version = 1;

			$this->viewGroup = htmlspecialchars($viewGroup);
			$this->editGroup = htmlspecialchars($editGroup);
			$this->deleteGroup = htmlspecialchars($deleteGroup);
			$this->adminGroup = htmlspecialchars($adminGroup);

			$this->creatorid = $user->objectid;
			$this->creatorName = $user->title;
			$this->creatorHomeid = $user->homeid;
			$this->creatorDatetime = date(DATABASEDATE);
			$this->updatorid = $user->objectid;
			$this->updatorName = $user->title;
			$this->updatorHomeid = $user->homeid;
			$this->updatorDatetime = date(DATABASEDATE);
		}
		track();
	}
	
/*** Member Functions ***/

	function update(&$user, $incrementVersion = TRUE) { // update object
		track('thing::update', $incrementVersion);
		$this->updatorid = $user->objectid;
		$this->updatorName = $user->title;
		$this->updatorHomeid = $user->homeid;
		$this->updatorDatetime = date(DATABASEDATE);
		if ($incrementVersion) { // create new version if told to
			$this->version++;
		}
		track();
	}

	function save() {
		global $conn;
		track('thing::save');
// serialize object
		$serializedObj = serialize($this);
// create field array from object
		$fieldArray['object'] = $serializedObj;
		foreach ($this->indexes as $index => $definition) {
			if (isset($this->$index)) {
				if ($this->$index == FALSE) {
					$fieldArray[$index] = 0;
				} else {
					$fieldArray[$index] = $this->$index;
				}
			}
		}
// get table
		$table = getTable($this->classid);
// set conditions
		$conditionArray = array(
			$table.'.objectid = '.$this->objectid,
			'AND',
			$table.'.version = '.$this->version,
			'AND',
			$table.'.classid = '.$this->classid,
			'AND',
			$table.'.workspaceid = '.$this->workspaceid
		);

// try to update existing record
		if (DBUpdate($conn, $table, $fieldArray, $conditionArray)) {
			track(); return 1;
		} else {
// if fail, write new record
			if (DBInsert($conn, $table, $fieldArray)) {
				track(); return 2;
			} else {
// if fail, modify table to include indexes from class definition
				if ($query = DBSelect($conn, $table, NULL,
					array('*'),
					NULL,
					NULL,
					NULL,
					1)
				) {
					$record = getRecord($query);
					$missingFields = array();
					foreach ($fieldArray as $field => $value) {
						if (!isset($record[$field]) && $field != 'object') {
							$missingFields[] = array(
								'name' => $field,
								'type' => $this->indexes[$field],
								'index' => $field
							);
						}
					}
					if ($missingFields != NULL && DBAlterTable($conn, $table, $missingFields)) {
						if (DBUpdate($conn, $table, $fieldArray, $conditionArray)) {
							track(); return 3;
						} elseif (DBInsert($conn, $table, $fieldArray)) {
							track(); return 4;
						}
					}
				}
			}
		}
		track(); return FALSE;
	}

	function delete() { // remove all versions of an object from the database
		global $conn;
		track('thing::delete');
		$table = getTable($this->classid);
		$conditionArray = array(
			$table.'.objectid = '.$this->objectid,
			'AND',
			$table.'.classid = '.$this->classid,
			'AND',
		);
		if ($this->workspaceid == 0) {
			$conditionArray[] = 'workspaceid = 0';
		} else {
			$conditionArray[] = '(workspaceid = '.$this->workspaceid;
			$conditionArray[] = 'OR';
			$conditionArray[] = 'workspaceid = 0)';
		}
		if (DBDelete($conn, $table, $conditionArray)) {
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}

	function revert() { // revert to previous version
		global $conn;
		track('thing::revert');
		$table = getTable($this->classid);
		$conditionArray = array(
			$table.'.objectid = '.$this->objectid,
			'AND',
			$table.'.classid = '.$this->classid,
			'AND',
			$table.'.version > '.$this->version,
			'AND'
		);
		if ($this->workspaceid == 0) {
			$conditionArray[] = 'workspaceid = 0';
		} else {
			$conditionArray[] = '(workspaceid = '.$this->workspaceid;
			$conditionArray[] = 'OR';
			$conditionArray[] = 'workspaceid = 0)';
		}
		if (DBDelete($conn, $table, $conditionArray)) {
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}
	
	function clone($title, $workspaceid) { // clone object
		track('thing::clone', $title, $workspaceid);
		$this->objectid = getIDFromName($title);
		$this->title = htmlspecialchars($title);
		$this->workspaceid = $workspaceid;
		if (thing::thingExists($this->objectid, $this->classid, $this->version, $this->workspaceid)) { // clone already exists
			track(); return FALSE;
		} else {
			track(); return TRUE;
		}
	}
	
	function tidyArchive() { // clean up archived versions	
		global $conn;
		track('thing::tidyArchive');
		$table = getTable($this->classid);
		$conditionArray = array(
			$table.'.objectid = '.$this->objectid,
			'AND',
			$table.'.version < '.($this->version - NUMBEROFARCHIVEVERSIONSTOKEEP),
			'AND',
			$table.'.classid = '.$this->classid,
			'AND',
			$table.'.workspaceid = '.$this->workspaceid,
			'AND',
			$table.'.updatorDatetime < "'.date(DATABASEDATE, strtotime(DESTROYOLDERTHAN)).'"'
		);
		if (DBDelete($conn, $table, $conditionArray)) {
			track(); return TRUE;
		} else {
			track(); return FALSE;
		}
	}

	function getCreator() {
		return array(
			'userid' => $this->creatorid,
			'username' => $this->creatorName,
			'homeid' => $this->creatorHomeid,
			'datetime' => $this->creatorDatetime
		);
	}

	function getUpdator() {
		if ($this->creatorDatetime == $this->updatorDatetime) {
			return NULL;
		} else {
			return array(
				'userid' => $this->updatorid,
				'username' => $this->updatorName,
				'homeid' => $this->updatorHomeid,
				'datetime' => $this->updatorDatetime
			);
		}
	}
	
	function thingExists($thingid, $classid, $version, $workspaceid = 0) { // static member to see if a thing exists
		global $conn;
		track('thing::thingExists', $thingid);
		$table = getTable($classid);
		$where = array('objectid = '.$thingid, 'AND', 'classid = '.$classid, 'AND', 'version = '.$version, 'AND', 'workspaceid = '.$workspaceid);
		$query = DBSelect($conn, $table, NULL,
			array($table.'.objectid'),
			$where,
			NULL,
			NULL,
			1
		);
		if ($query) {
			$numberOfRecords = getAffectedRows();
			if ($numberOfRecords > 0) {
				track(); return TRUE;
			}
		}
		track(); return FALSE;
	}

// admin
	function admin_update() {
		global $wtf, $conn;
	// updatorid
		$updatorid = getValue('updatorid', FALSE);
		if ($updatorid && is_numeric($updatorid) && $updatorid != $this->updatorid) {
			$user = &wtf::loadObject($this->updatorid, 0, 'user');
			if (is_object($user)) {
				$this->updatorid = $user->updatorid;
				$this->updatorName = $user->updatorName;
				$this->updatorHomeid = $user->updatorHomeid;
			}
		}
	// updatorname
		$updatorName = getValue('updatorname', FALSE);
		if ($updatorName && $updatorName != $this->updatorName) {
			$this->updatorName = $updatorName;
		}
		// updatorhomeid
			$updatorHomeid = getValue('updatorhomeid', FALSE);
			if ($updatorHomeid && is_numeric($updatorHomeid) && $updatorHomeid != $this->updatorHomeid) {
			$this->updatorHomeid = $updatorHomeid;
		}
	// updated
		$updatorDatetime = dbdate2unixtime(getValue('updated', FALSE));
		if ($updatorDatetime && $updatorDatetime != $this->updatorDatetime) {
			$this->updatorDatetime = date(DATABASEDATE, $updatorDatetime);
		}
	// groups
		$viewGroup = getValue('viewGroup', FALSE);
		if ($viewGroup && $viewGroup != $this->viewGroup && $wtf->user->inGroup($viewGroup)) {
			$this->viewGroup = htmlspecialchars($viewGroup);
		}
		$editGroup = getValue('editGroup', FALSE);
		if ($editGroup && $editGroup != $this->editGroup && $wtf->user->inGroup($editGroup)) {
			$this->editGroup = htmlspecialchars($editGroup);
		}
		$deleteGroup = getValue('deleteGroup', FALSE);
		if ($deleteGroup && $deleteGroup != $this->deleteGroup && $wtf->user->inGroup($deleteGroup)) {
			$this->deleteGroup = htmlspecialchars($deleteGroup);
		}
		$adminGroup = getValue('adminGroup', FALSE);
		if ($adminGroup && $adminGroup != $this->adminGroup && $wtf->user->inGroup($adminGroup)) {
			$this->adminGroup = htmlspecialchars($adminGroup);
		}
    // sectionid
        $sectionid = intval(getValue('sectionid', 0));
        if ($sectionid != $this->sectionid ) {
            $success = true;
            if ( $this->sectionid != 0 ) {
                $success = false;
                $section = &wtf::loadObject($this->sectionid, 0, 'section');
                if (is_object($section) && $wtf->user->inGroup($section->editGroup)) {
                    $success = $section->removeFromSection($this);
                }
            }
            // Don't try to update with the new section, if the 
            // old one could not be cleaned up.
            if ( $success ) {
                if ( $sectionid != 0 ) {
                    $section = &wtf::loadObject($sectionid, 0, 'section');
                    if (is_object($section) && $wtf->user->inGroup($section->editGroup)) {
                        $section->addToSection($this);
                    }
                } else {
                    $this->sectionid = 0;
                }
            }
        }
	// workspace
		$workspaceid = intval(getValue('workspaceid', 0));
		if ($workspaceid != $this->workspaceid) {
			if ($workspaceid == 0) {
				$this->workspaceid = 0;
			} else {
				$workspace = &wtf::loadObject($workspaceid, 0, 'workspace');
				if (is_object($workspace) && $wtf->user->inGroup($workspace->editGroup)) {
					$this->workspaceid = intval($workspaceid);
				}
			}
		}
	// attributes
		$attributesArray = array();
		for ($foo = 0; $attribute = getValue('attribute,'.$foo, FALSE), $attribute == TRUE; $foo++) {
			if (getValue('deleteattribute,'.$foo, FALSE) != 'on'){
				$attributesArray[$foo] = htmlspecialchars($attribute);
			}
		}
		$newattribute = getValue('newattribute', FALSE);
		if ($newattribute && $newattribute != '') {
			$attributesArray[] = htmlspecialchars($newattribute);
		}
		if (array_count_values($attributesArray) > 0) {
			$this->attributes = $attributesArray;
		}
	// update thing
		$this->delete(); // remove old version
		$this->save(); // create new one
	}
	
	function admin_fields($className, $workspaces) {
		echo '<admin_creator homeid="'.$this->creatorHomeid.'">'.$this->creatorName.'</admin_creator>';
		echo '<admin_created>'.date(DATEFORMAT, dbdate2unixtime($this->creatorDatetime)).'</admin_created>';
		echo '<admin_class>'.$className.'</admin_class>';

		echo '<admin_title name="title" title="'.$this->title.'" changename="changethingid" />';
		echo '<admin_updatorid name="updatorid" userid="'.$this->updatorid.'"/>';
		echo '<admin_updatorname name="updatorname" username="'.$this->updatorName.'"/>';
		echo '<admin_updatorhomeid name="updatorhomeid" homeid="'.$this->updatorHomeid.'"/>';
		echo '<admin_updated name="updated" date="'.date(DATABASEDATE, dbdate2unixtime($this->updatorDatetime)).'"/>';
		echo '<admin_view name="viewGroup" group="'.$this->viewGroup.'"/>';
		echo '<admin_edit name="editGroup" group="'.$this->editGroup.'"/>';
		echo '<admin_delete name="deleteGroup" group="'.$this->deleteGroup.'"/>';
		echo '<admin_admin name="adminGroup" group="'.$this->adminGroup.'"/>';
		echo '<admin_workspace name="workspaceid">';
		foreach ($workspaces as $workspaceid => $workspacename) {
			if ($this->workspaceid == $workspaceid) {
				echo '<admin_workspaceitem selected="true" workspaceid="'.$workspaceid.'" workspacename="'.$workspacename.'"/>';
			} else {
				echo '<admin_workspaceitem workspaceid="'.$workspaceid.'" workspacename="'.$workspacename.'"/>';
			}
		}
		echo '</admin_workspace>';

		if (isset($this->attributes)) {
			echo '<admin_attribute name="newattribute">';
			if (is_array($this->attributes) && $this->attributes != NULL) {
				foreach ($this->attributes as $key => $attribute) {
					echo '<admin_attributeitem name="attribute,'.$key.'" cbname="deleteattribute,'.$key.'" attribute="'.$attribute.'"/>';
				}
			}
			echo '</admin_attribute>';
		}
	}

	function admin_actions($className, $workspaces) {
		echo '<a href="'.THINGIDURI.$this->objectid.'&amp;version='.$this->version.'&amp;class='.$className.'&amp;op=view">View</a> the content of this version.<br/>';
		echo '<a href="'.THINGIDURI.$this->objectid.'&amp;version='.$this->version.'&amp;class='.$className.'&amp;op=revert">Revert</a> back to this version.<br/>';
		echo '<a href="'.THINGIDURI.$this->objectid.'&amp;version='.$this->version.'&amp;class='.$className.'&amp;op=clone">Clone</a> this version to a different title or workspace.<br/>';
	}

/*** Methods ***/

// view
	function method_view() {
		global $wtf;
		track('thing::method::view');
		if (getValue('version', FALSE)) {
			echo '<thing_info version="'.$this->version.'" class="'.get_class($this).'"/>';
		}
		echo 'This thing can not be displayed.';
		track();
	}

// create
	function method_create() { // create thing
		echo 'You can not make a thing of this type.';
	}

// delete
	function method_delete() {
		global $conn, $wtf;
		track('thing::method::delete');
		if (hasPermission($this, $wtf->user, 'deleteGroup')) { // check permission
			if (getValue('confirm', FALSE) == 'true') { // do delete
				if ($this->delete()) {
					echo '<delete_success title="', $this->title, '"/>';
				} else {
					echo '<delete_error title="', $this->title, '"/>';
				}
			} else { // prompt
				echo '<delete_verify url="'.THINGIDURI.$this->objectid.'&amp;class='.$wtf->class.'&amp;op=delete&amp;confirm=true" class="'.$wtf->class.'" thingid="'.$this->objectid.'" title="'.$this->title.'"/>';
			}
		} else {
			echo '<thing_permissionerror method="delete" title="'.$this->title.'"/>';
		}
		track();
	}

// revert
	function method_revert() {
		global $conn, $wtf;
		track('thing::method::revert');
		if (hasPermission($this, $wtf->user, 'adminGroup')) { // check permission
			if (getValue('confirm', FALSE) == 'true') { // do revert
				if ($this->revert()) {
					echo '<revert_success title="', $this->title, '" version="', $this->version, '" class="', get_class($this), '"/>';
				} else {
					echo '<revert_error title="', $this->title, '"/>';
				}
			} else { // prompt
				echo '<revert_verify url="', THINGIDURI, $this->objectid, '&amp;class=', get_class($this), '&amp;version=', $this->version, '&amp;op=revert&amp;confirm=true" thingid="', $this->objectid, '" class="', get_class($this), '" title="', $this->title, '" version="', $this->version, '"/>';
			}
		} else {
			echo '<thing_permissionerror method="revert" title="'.$this->title.'"/>';
		}
		track();
	}

// clone
	function method_clone() {
		global $conn, $wtf;
		track('thing::method::clone');
		$submit = getValue('submit', FALSE);
		$title = getValue('title', FALSE);
		$workspaceid = getValue('workspaceid', FALSE);
		$workspaces = workspace::getWorkspaces(); // get array of workspaces
		if ($submit) {
			if ($title && $workspaceid !== FALSE && is_numeric($workspaceid) && preg_match('/'.TITLEMATCHREGEX.'/', $title) && isset($workspaces[intval($workspaceid)])) {
				$workspaceid = intval($workspaceid);
				if ($workspaceid == 0) {
					$permission = TRUE;
				} else {
					$workspace = &wtf::loadObject($workspaceid, 0, 'workspace');
					if ($workspace && $wtf->user->inGroup($workspace->adminGroup)) {
						$permission = TRUE;
					} else {
						$permission = FALSE;
					}
				}
				if ($wtf->user->inGroup($this->adminGroup) && $permission) {	// check permission
					$clone = $this; // do clone
					if ($clone->clone($title, $workspaceid)) {
						$clone->save();
						echo '<clone_success title="', $this->title, '" newtitle="', htmlspecialchars($title), '" workspace="', $workspaces[$workspaceid], '"/>';
					} else {
						echo '<clone_error title="', $this->title, '"/>';
					}
				} else {
					echo '<thing_permissionerror method="clone" title="'.$this->title.'"/>';
				}
			} else {
				echo '<clone_error title="', $this->title, '"/>';
			}
		} else {
			echo '<clone titlename="title" title="'.$this->title.'" workspacename="workspaceid" url="', THINGIDURI, $this->objectid, '&amp;class=', get_class($this), '&amp;version=', $this->version, '&amp;op=clone" submit="submit">';
			foreach ($workspaces as $workspaceid => $workspace) {
				if ($workspaceid == $this->workspaceid) {
					echo '<clone_workspace selected="true" workspaceid="'.$workspaceid.'" workspace="'.$workspace.'" />';
				} else {
					echo '<clone_workspace workspaceid="'.$workspaceid.'" workspace="'.$workspace.'" />';
				}
			}
			echo '</clone>';
		}
		track();
	}

// history
	function method_history() {
		global $conn, $wtf;
		track('thing::method::history');

		$workspaces = workspace::getWorkspaces(); // get array of workspaces

		$where = getWhere($this->objectid, get_class($this), $wtf->user->workspaceid);

		$query = DBSelect($conn, OBJECTTABLE, NULL, array('object'), $where, NULL, array('version DESC'), NULL);
		if ($query) {
			$numberOfRecords = getAffectedRows();
			if ($numberOfRecords > 0) {
				for ($foo = 1; $foo <= $numberOfRecords; $foo++) {
					$record = getRecord($query);
					$serializedObj = $record['object'];
					$obj = unserialize($serializedObj);
					$className = get_class($obj);
					if ($foo == 1) {
						echo '<history thingid="'.$this->objectid.'" class="'.$className.'">';
						echo '<history_header>';
						echo '<history_creator homeid="'.$this->creatorHomeid.'">'.$this->creatorName.'</history_creator>';
						echo '<history_created>'.date(DATEFORMAT, dbdate2unixtime($this->creatorDatetime)).'</history_created>';
						echo '<history_class>'.$className.'</history_class>';
						echo '</history_header>';
					}
					echo '<history_item thingid="'.$obj->objectid.'" version="'.$obj->version.'">';
					echo '<history_title>'.$obj->title.'</history_title>';
					echo '<history_version thingid="'.$obj->objectid.'" class="'.$className.'" version="'.$obj->version.'"/>';
					echo '<history_updator homeid="'.$obj->updatorHomeid.'">'.$obj->updatorName.'</history_updator>';
					echo '<history_updated>'.date(DATEFORMAT, dbdate2unixtime($obj->updatorDatetime)).'</history_updated>';
					if (isset($workspaces[$obj->workspaceid])) {
						echo '<history_workspace>'.$workspaces[$obj->workspaceid].'</history_workspace>';
					} else {
						echo '<history_workspace>Unknown</history_workspace>';
					}
					echo '</history_item>';
				}
				echo '</history>';
			}
		} else {
			if ($this->workspaceid == 0) {
				echo '<history_nothinginworkspace workspaceid="'.$wtf->user->workspaceid.'"/>';	
			} else {
				echo '<history_nothinginworkspace workspaceid="'.$this->workspaceid.'"/>';
			}	
		}
		track();
	}
	
// admin
	function method_admin() {
		global $conn, $wtf;
		track('thing::method::admin');
		
		if (hasPermission($this, $wtf->user, 'adminGroup')) { // check permission

			$submit = getValue('submit', FALSE);
			if ($submit) { // if submitted form data, update thing
				$this->admin_update();
			}

			$workspaces = workspace::getWorkspaces(); // get array of workspaces
			$className = get_class($this);

			echo '<p>You are logged in as "<a href="', THINGIDURI.$wtf->user->homeid, '">', $wtf->user->title, '</a>" and are in the workspace "<b>', $workspaces[$wtf->user->workspaceid], '</b>".</p>';

			echo '<admin submit="submit" url="'.THINGIDURI.$this->objectid.'&amp;version='.$this->version.'&amp;class='.$className.'&amp;op=admin" title="'.$this->title.'" version="'.$this->version.'" thingid="'.$this->objectid.'">';

			$this->admin_fields($className, $workspaces);
			
			echo '<p>';
			$this->admin_actions($className, $workspaces);
			echo '</p>';
			
			echo '</admin>';
		} else {
			echo '<thing_permissionerror method="admin" title="'.$this->title.'"/>';
		}
		track();
	}

}

/*** Formatting ***/

$FORMAT = array_merge($FORMAT, array(
// thing
	'thing_info' => '<p class="success">You are viewing version {version} of this {class}.</p>',
	'/thing_info' => '',
	'thing_permissionerror' => '<p class="error">You do not have permission to {method} "{title}".</p>',
	'/thing_permissionerror' => '',

// delete
	'delete_success' => '<p>"{title}" deleted.</p>',
	'/delete_success' => '',
	'delete_error' => '<p class="error">Could not delete "{title}".</p>',
	'/delete_error' => '',
	'delete_permissionerror' => '<p class="error">You do not have permission to delete "{title}".</p>',
	'/delete_permissionerror' => '',
	'delete_verify' => '
<p>Are you sure you want to delete the {class} "{title}"?</p>
<p><a href="{url}">Yes</a> | <a href="'.THINGIDURI.'{thingid}&amp;class={class}">No</a></p>
',
	'/delete_verify' => '',
// revert
	'revert_success' => '<p>"<a href="'.THINGURI.'{title}&class={class}">{title}</a>" reverted to version #{version}.</p>',
	'/revert_success' => '',
	'revert_error' => '<p class="error">Could not revert thing "{title}".</p>',
	'/revert_error' => '',
	'revert_verify' => '
<p>Are you sure you want to revert the {class} "{title}" back to version #{version}?</p>
<p><a href="{url}">Yes</a> | <a href="'.THINGIDURI.'{thingid}&amp;class={class}">No</a></p>
',
	'/revert_verify' => '',
	
// clone
	'clone' => '<form method="post" action="{url}">Clone <input type="text" name="{titlename}" value="{title}" size="30"/> to workspace <select name="{workspacename}">',
	'/clone' => '</select> <input type="submit" name="{submit}" value="Clone" /></form>',
	'clone_workspace' => '<option value="{workspaceid}">{workspace}</option>',
	'clone_workspace.selected' => '<option value="{workspaceid}" selected="selected">{workspace}</option>',
	'/clone_workspace' => '',
	'clone_success' => '<p>"{title}" cloned to "{newtitle}" in workspace "{workspace}".</p>',
	'/clone_success' => '',
	'clone_error' => '<p class="error">Could not clone "{title}".</p>',
	'/clone_error' => '',

//history
	'history' => '<h2>History</h2>',
	'/history' => '</table>',
	'history_header' => '',
	'/history_header' => '<p>Currently archived versions.</p><table><tr><th>Title</th><th>Version</th><th>Author</th><th>Created</th><th>Workspace</th></tr>',
	'history_creator' => '<p>Created by: <a href="'.THINGIDURI.'{homeid}">',
	'/history_creator' => '</a><br />',
	'history_created' => 'Created on: ',
	'/history_created' => '<br />',
	'history_class' => 'Thing type: ',
	'/history_class' => '<br/></p>',
	'history_item' => '<tr>',
	'/history_item' => '</tr>',
	'history_title' => '<td align="center">',
	'/history_title' => '</td>',
	'history_version' => '<td align="center"><a href="'.THINGIDURI.'{thingid}&amp;class={class}&amp;version={version}">{version}',
	'/history_version' => '</a></td>',
	'history_updator' => '<td align="center"><a href="'.THINGIDURI.'{homeid}&amp;class=home">',
	'/history_updator' => '</a></td>',
	'history_updated' => '<td align="center">',
	'/history_updated' => '</td>',
	'history_workspace' => '<td align="center">',
	'/history_workspace' => '</td>',
	'history_nothinginworkspace' => '<p>No version of this thing found in this workspace, <a href="'.THINGIDURI.'{workspaceid}&amp;class=workspace">change workspace</a> to view this things history.</p>',
	'/history_nothinginworkspace' => '',
	
// admin
	'admin' => '<h2>Admin Version {version}</h2><form method="post" action="{url}">',
	'/admin' => '<input type="submit" value="Update" name="{admin.submit}" /></p></form>',
	'admin_creator' => '<p>Created by: <a href="'.THINGIDURI.'{homeid}">',
	'/admin_creator' => '</a><br />',
	'admin_created' => 'Created on: ',
	'/admin_created' => '<br/>',
	'admin_class' => 'Thing type: ',
	'/admin_class' => '<br/></p>',
	'admin_updatorid' => 'Creator ID: <input type="text" value="{userid}" name="{name}" size="10" /><br />',
	'/admin_updatorid' => '',
	'admin_updatorname' => 'Creator Name: <input type="text" value="{username}" name="{name}" size="10" /><br />',
	'/admin_updatorname' => '',
	'admin_updatorhomeid' => 'Creator Home ID: <input type="text" value="{homeid}" name="{name}" size="10" /><br />',
	'/admin_updatorhomeid' => '',
	'admin_updated' => 'Created Date: <input type="text" value="{date}" name="{name}" size="35" /><br />',
	'/admin_updated' => '',
	'admin_view' => 'View Group: <input type="text" value="{group}" name="{name}" size="10" /><br />',
	'/admin_view' => '',
	'admin_edit' => 'Edit Group: <input type="text" value="{group}" name="{name}" size="10" /><br />',
	'/admin_edit' => '',
	'admin_delete' => 'Delete Group: <input type="text" value="{group}" name="{name}" size="10" /><br />',
	'/admin_delete' => '',
	'admin_admin' => 'Admin Group: <input type="text" value="{group}" name="{name}" size="10" /><br />',
	'/admin_admin' => '',
	'admin_workspace' => 'Workspace: <select name="{name}">',
	'/admin_workspace' => '</select><br />',
	'admin_workspaceitem' => '<option value="{workspaceid}">{workspacename}</option>',
	'admin_workspaceitem.selected' => '<option value="{workspaceid}" selected="selected">{workspacename}</option>',
	'/admin_workspaceitem' => '',
    'admin_attribute' => 'Attributes: ',
	'/admin_attribute' => '<input type="text" value="" size="10" name="{name}" title="Create a new attribute" /><br />',
	'admin_attributeitem' => '<input type="checkbox" name="{cbname}" title="Check to delete attribute \'{attribute}\'" /><input type="text" value="{attribute}" name="{name}" size="10" /> ',
	'/admin_attributeitem' => '',
	
// create
	'create_failed' => '<h2 class="fail">Create Failed</h2><p>{message}</p>',
	'/create_failed' => '',
	'create_permission' => '<p class="error">You do not have permission to create a new object of this type.</p>',
	'/create_permission' => '',

// update
	'update_failed' => '<h2 class="fail">Update Failed</h2><p>{message}</p>',
	'/update_failed' => '',
	'update_permission' => '<p class="error">You do not have permission to update this object.</p>',
	'/update_permission' => '',

));

?>
