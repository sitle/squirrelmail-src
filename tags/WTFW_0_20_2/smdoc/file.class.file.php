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
file.class.file.php
File Class
*/

$HARDCLASS[-1935722992] = 'file';

if (!defined('FILECREATE')) define('FILECREATE', CREATORS);

class file extends thing {

	var $filename;
	var $mimetype;
	var $size;

/*** Constructor ***/

	function file(
		&$user,
		$title = NULL,
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP,
		$adminGroup = DEFAULTADMINGROUP
	) {
		track('file::file', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::thing($user, $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		$this->filename = FALSE;
		$this->mimetype = FALSE;
		$this->size = 0;
		track();
	}

/*** Member Functions ***/

// upload a file to the thing
	function update($uploadName, $incrementVersion = TRUE) {
		global $wtf;
		track('file::addFile', $uploadName, $incrementVersion);

		if (!$incrementVersion && $this->filename) { // not archiving so remove old file
			@unlink(FILEUPLOADDIR.'/'.$this->objectid.'_'.$this->version.'_'.$this->filename);
			$this->filename = FALSE;
			$this->mimetype = FALSE;
			$this->size = 0;
		}

		if (isset($_FILES[$uploadName])) { // file exists in HTTP header
		
			if ($_FILES[$uploadName]['size'] > MAXFILEUPLOADSIZE) { // file too big
				$_FILES[$uploadName]['error'] = 1;
			}
		
			if (isset($_FILES[$uploadName]['error']) && $_FILES[$uploadName]['error'] > 0) { // upload error
				switch($_FILES[$uploadName]['error']) {
				case 1:
				case 2:
					$error = 'The uploaded file exceeds the maximum allowed file size.';
					break;
				case 3:
					$error = 'The uploaded file was only partially uploaded.';
					break;
				case 4:
					$error = 'No file was uploaded.';
					break;
				}		
				track(); return array('success' => FALSE, 'error' => $error);
			} else {
		
				if ($incrementVersion) {
					$version = $this->version + 1;
				} else {
					$version = $this->version;
				}
		
				if (move_uploaded_file($_FILES[$uploadName]['tmp_name'], FILEUPLOADDIR.'/'.$this->objectid.'_'.$version.'_'.$_FILES[$uploadName]['name'])) {

					$this->filename = $_FILES[$uploadName]['name'];
					$this->mimetype = $_FILES[$uploadName]['type'];
					$this->size = $_FILES[$uploadName]['size'];

					parent::update($wtf->user, $incrementVersion); // update thing

					track(); return array('success' => TRUE);			
				} else {
					track(); return array('success' => FALSE, 'error' => 'Could not uplaod file to "'.FILEUPLOADDIR.'".');
				}
			}
		} else {
			track(); return array('success' => FALSE, 'error' => 'File not uploaded.');
		}

	}

	function getContent() {
		$filename = FILEUPLOADDIR.'/'.$this->objectid.'_'.$this->version.'_'.$this->filename;
		switch($this->mimetype) {
		case 'text/plain':
		case 'text/css':
			$fd = @fopen($filename, "rb");
			if ($fd) {
				echo '<file_contents>', replaceNewLines(htmlspecialchars(fread($fd, filesize($filename)))), '</file_contents>';
				fclose($fd);
			} else {
				echo 'Could not find file "', $this->filename, '".';
			}
			break;
		case 'text/html':
		case 'text/xml':
		case 'text/sgml':
			$fd = @fopen($filename, "rb");
			if ($fd) {
				$fileContents = htmlspecialchars(fread($fd, filesize($filename)));
				$fileContents = preg_replace('/(&lt;!--.*--&gt;)/Us', '<file_comment>\\1</file_comment>', $fileContents);
				$fileContents = preg_replace('/(&lt;.*&gt;)/U', '<file_tag>\\1</file_tag>', $fileContents);
				echo '<file_contents>', replaceNewLines($fileContents), '</file_contents>';
				fclose($fd);
			} else {
				echo 'Could not find file "', $this->filename, '".';
			}
			break;
		case 'image/jpeg':
		case 'image/jpg':
		case 'image/pjpeg':
		case 'image/gif':
		case 'image/png':
		case 'image/x-png':
			echo '<file_image filename="', $this->filename, '" url="', FILEUPLOADWEBROOT.'/'.$this->objectid.'_'.$this->version.'_'.$this->filename, '"/>';
			break;
		default:
			echo '<file_link filename="', $this->filename, '" url="', FILEUPLOADWEBROOT.'/'.$this->objectid.'_'.$this->version.'_'.$this->filename, '"/>';
		}
	}
	
	function delete() {
		track('file::delete');
		if (parent::delete()) {
			@unlink(FILEUPLOADDIR.'/'.$this->objectid.'_'.$this->version.'_'.$this->filename);
			track(); return TRUE;
		}	else {
			track(); return FALSE;
		}
	}
	
	function revert() {
		global $conn, $wtf;
		track('file::revert');

		$table = getTable($this->classid);
		$where = getWhere($this->objectid, get_class($this), $wtf->user->workspaceid);
		$where[] = 'AND';
		$where[] = $table.'.version > '.$this->version;
		$query = DBSelect($conn, $table, NULL,
		array($table.'.object'),
		$where,
		NULL,
		array($table.'.version DESC'),
		1);
		if ($query) {
			$numberOfRecords = getAffectedRows();
			if ($numberOfRecords > 0) {
				for ($foo = 1; $foo <= $numberOfRecords; $foo++) {
					$record = getRecord($query);
					$serializedObj = $record['object'];
					$obj = unserialize($serializedObj);
					unlink(FILEUPLOADDIR.'/'.$obj->objectid.'_'.$obj->version.'_'.$obj->filename);
				}
			}
		}
		
		if (parent::revert()) {
			track(); return TRUE;
		}	else {
			track(); return FALSE;
		}
	}
	
	function drawForm($url, $title = NULL, $titleIsEditable = FALSE, $small = NULL, $loadtime = NULL) {
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
		if ($loadtime == NULL) $loadtime = time();
		echo '<file_form url="', $url, '" ';
		echo 'thingidfield="thingid" thingid="', $objectid, '" ';
		echo 'versionfield="version" version="', $version, '" ';
		echo 'loadtimefield="loadtime" loadtime="'.$loadtime.'" submit="submit" preview="preview">';
		if ($titleIsEditable) {
			echo '<file_titlebox name="title" maxlength="', MAXTITLELENGTH, '">', $title, '</file_titlebox>';
		} else {
			echo '<file_title title="', $title, '" version="', $version, '"/>';
		}
		echo '<file_filebox name="fileupload" maxlength="', MAXFILEUPLOADSIZE, '"/>';
		if ($wtf->user->objectid == $updatorid && $wtf->user->objectid != ANONYMOUSUSERID && isset($small)) {
			if ($small == 'on') {
				echo '<file_smallupdate checked="checked" name="small"/>';
			} else {
				echo '<file_smallupdate name="small"/>';
			}
		}
		echo '</file_form>';
	}
	
/*** Methods ***/
	
// view
	function method_view() {
		global $wtf;
		track('file::method::view');
		if (getValue('version', FALSE)) {
			echo '<thing_info version="'.$this->version.'" class="'.get_class($this).'"/>';
		}
		if (hasPermission($this, $wtf->user, 'viewGroup')) {
			echo $this->getContent();
		} else {
			echo '<thing_permissionerror method="view" title="'.$this->title.'"/>';
		}
		track();
	}

// create
	function method_create($thingName = NULL, $objectName = 'file') { // this is both a method and a static member
		global $conn, $wtf;
		track('file::method::create');

		if ($wtf->user->inGroup(FILECREATE)) { // check permission
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
				if ($thing && $thing->objectid != 0) { // action to do
					$result = $thing->update('fileupload', FALSE);
					if ($result['success']) {
						$thing->save();
						header('Location: '.THINGIDURI.$thing->objectid.'&class='.get_class($thing));
						exit;
					} else {
						echo '<file_create_failed message="', $result['error'], '"/>';
					}
					$title = $thing->title;
					file::drawForm($url, $title, TRUE, NULL);
				} else {
					echo '<file_create_failed message="Could not create object ', htmlspecialchars($title), '"/>';
				}
			} else { // display empty form
				file::drawForm($url, $title, TRUE, NULL);
			}
		} else {
			echo '<file_create_permission/>';
		}
		track();
	}

	function method_edit() { // edit thing
		global $conn, $wtf;
		track('file::method::edit');
		if (hasPermission($this, $wtf->user, 'editGroup')) {	// check permission
			$update = getValue('submit', FALSE);
			$small = getValue('small', FALSE);		
			$loadtime = getValue('loadtime', NULL);

			if ($update) { // update thing
				if ($small == 'on' && $wtf->user->objectid == $this->updatorid && $wtf->user->objectid != ANONYMOUSUSERID) {
					$incrementVersion = FALSE;
				} else {
					$incrementVersion = TRUE;
				}

// check it hasn't been updated since loading the form
				if (
					(!isset($loadtime) || !is_numeric($loadtime) || $loadtime < dbdate2unixtime($this->updatorDatetime))
					&&
					($this->updatorid == ANONYMOUSUSERID || $this->updatorid != $wtf->user->objectid)
				) {
					echo '<file_edit_locked/>';

// if we have content, update the object
				} else {
					$result = $this->update('fileupload', $incrementVersion);
					if ($result['success']) {
						$this->save(); // save thing to database
						$this->tidyArchive(); // tidy up archived versions of thing
						echo '<file_edit_updated/>';
					} else {
						echo '<file_edit_failed message="', $result['error'], '"/>';
					}
				}
			} else { // get thing contents for editing
				$this->drawForm(THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=edit', $this->title, FALSE, $small, $loadtime);
			}

		} else {
			echo '<thing_permissionerror method="edit" title="'.$this->title.'"/>';
		}
		track();
	}
	

// history
	function method_history() {
		global $conn, $wtf;
		track('file::method::history');

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
					$className = get_class($this);
					if ($foo == 1) {
						echo '<history thingid="'.$this->objectid.'" class="'.$className.'">';
						echo '<file_history_header>';
						echo '<history_creator homeid="'.$this->creatorHomeid.'">'.$this->creatorName.'</history_creator>';
						echo '<history_created>'.date(DATEFORMAT, dbdate2unixtime($this->creatorDatetime)).'</history_created>';
						echo '<history_class>'.$className.'</history_class>';
						echo '</file_history_header>';
					}
					echo '<history_item thingid="'.$obj->objectid.'" version="'.$obj->version.'">';
					echo '<history_title>'.$obj->title.'</history_title>';
					echo '<history_version thingid="'.$obj->objectid.'" class="'.$className.'" version="'.$obj->version.'"/>';
					echo '<file_history_filename>', $obj->filename, '</file_history_filename>';
					echo '<file_history_mimetype>', $obj->mimetype, '</file_history_mimetype>';
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
	
}

$NOPARSETAG[] = 'file_contents';

// formatting
$FORMAT = array_merge($FORMAT, array(

// form
	'file_form' => '<form method="post" enctype="multipart/form-data" action="{url}"><input type="hidden" name="{thingidfield}" value="{thingid}" /><input type="hidden" name="{versionfield}" value="{version}" /><input type="hidden" name="{loadtimefield}" value="{loadtime}" />',
	'/file_form' => '<p><input type="submit" name="{submit}" value="Upload" /></p></form>',
	'file_title' => '<p>Editing thing "<a href="'.THINGURI.'{title}">{title}</a>" (version {version})</p>',
	'/file_title' => '',
	'file_titlebox' => '<p>Title: <input type="text" name="{name}" size="50" maxlength="{maxlength}" value="',
	'/file_titlebox' => '"/></p>',
	'file_filebox' => '<p>Filename:
<input type="hidden" name="MAX_FILE_SIZE" value="{maxlength}">
<input name="{name}" type="file">',
	'/file_filebox' => '</p>',
	'file_smallupdate' => '<p><input type="checkbox" name="{name}" id={name} /> <label for="{name}">This is a small update so don\'t archive the previous version.</label></p>',
	'file_smallupdate.checked' => '<p><input type="checkbox" name="{name}" id={name} checked="checked" /> <label for="{name}">This is a small update so don\'t archive the previous version.</label></p>',
	'/file_smallupdate' => '',
// edit	
	'file_edit_updated' => '<h2 class="success">Edit Successful</h2><p>Page updated successfully.</p>',
	'/file_edit_updated' => '',
	'file_edit_failed' => '<h2 class="fail">Edit Failed</h2><p>{message}</p>',
	'/file_edit_failed' => '',
	'file_edit_locked' => '<p class="error">This page is currently being updated by someone else, please review their changes before making your own.</p>',
	'/file_edit_locked' => '',
// create	
	'file_create_updated' => '<h2 class="success">Create Successful</h2><p>Page created successfully.</p>',
	'/file_create_updated' => '',
	'file_create_failed' => '<h2 class="fail">Create Failed</h2><p>{message}</p>',
	'/file_create_failed' => '',
	'file_create_preview' => '<p>Preview:</p><table width="100%" class="preview"><tr><td>',
	'/file_create_preview' => '</td></tr></table>',
	'file_create_permission' => '<p class="error">You do not have permission to create upload a file.</p>',
	'/file_create_permission' => '',

// view
	'file_link' => '<a href="{url}">{filename}</a><br />',
	'/file_link' => '',
	'file_image' => '<img src="{url}" alt="{filename}" />',
	'/file_image' => '',
	'file_tag' => '<span class="syntaxtag">',
	'/file_tag' => '</span>',
	'file_comment' => '<span class="syntaxquickformat">',
	'/file_comment' => '</span>',
	
// history
	'file_history_header' => '',
	'/file_history_header' => '<p>Currently archived versions.</p><table><tr><th>Title</th><th>Version</th><th>Filename</th><th>Mime Type</th><th>Author</th><th>Created</th><th>Workspace</th></tr>',
	'file_history_filename' => '<td align="center">',
	'/file_history_filename' => '</td>',
	'file_history_mimetype' => '<td align="center">',
	'/file_history_mimetype' => '</td>',

));

?>