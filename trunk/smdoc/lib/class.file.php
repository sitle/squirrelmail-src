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
class.file.php
File class
*/

if (!defined('FOOWD_FILE_MAX_FILESIZE')) define('FOOWD_FILE_MAX_FILESIZE', 1048576); // 1 meg
if (!defined('FOOWD_FILE_DIR')) define('FOOWD_FILE_DIR', substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/')).'/userfiles/');

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_FILE_OBJECT_CHECKIN')) define('PERMISSION_FOOWD_FILE_OBJECT_CHECKIN', 'Gods');
if (!defined('PERMISSION_FOOWD_FILE_OBJECT_CHECKOUT')) define('PERMISSION_FOOWD_FILE_OBJECT_CHECKOUT', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_407958236_CLASSNAME')) define('META_407958236_CLASSNAME', 'foowd_file');
if (!defined('META_407958236_DESCRIPTION')) define('META_407958236_DESCRIPTION', 'File');

/** CLASS DECLARATION **/
class foowd_file extends foowd_object {

	var $filename;
	var $type;
	var $lock = FALSE;
	
/*** CONSTRUCTOR ***/

	function foowd_file(
		&$foowd,
		$title = NULL,
		$filename = NULL,
		$type = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$checkoutGroup = NULL,
		$checkinGroup = NULL
	) {
		$foowd->track('foowd_file->constructor');

// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

		$this->filename = $filename;
		$this->type = $type;

/* set method permissions */
		$className = get_class($this);
		$this->permissions['checkout'] = getPermission($className, 'checkout', 'object'. $checkoutGroup);
		$this->permissions['checkin'] = getPermission($className, 'checkin', 'object'. $checkinGroup);

		$foowd->track();
	}
	
/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['filename'] = '/^.*$/';
		$this->foowd_vars_meta['type'] = '/^.*$/';
		$this->foowd_vars_meta['lock'] = '/^[0-1]?$/';
	}

/*** MEMBER FUNCTIONS ***/

/* save object */

	function save(&$foowd, $incrementVersion = TRUE, $doUpdate = TRUE, $tmp_name = NULL) { // write object to database
		$foowd->track('foowd_file->save');
		$oldname = $this->foowd_original_access_vars['objectid'].'_'.$this->foowd_original_access_vars['version'].'_'.$this->foowd_original_access_vars['workspaceid'];
		if (parent::save($foowd, $incrementVersion, $doUpdate)) {
			$newname = $this->objectid.'_'.$this->version.'_'.$this->workspaceid;
			if ($oldname != $newname) {
				if (isset($tmp_name) && move_uploaded_file($tmp_name, FOOWD_FILE_DIR.$newname)) {
				} elseif (file_exists(FOOWD_FILE_DIR.$oldname)) {
					copy(
						FOOWD_FILE_DIR.$oldname,
						FOOWD_FILE_DIR.$newname
					);
				}
			}
			$foowd->track(); return TRUE;
		}
		$foowd->track(); return FALSE;
	}

/* delete object */

	function delete(&$foowd) { // remove all versions of an object from the database
		$foowd->track('foowd_file->delete');
		foreach (glob(FOOWD_FILE_DIR.$this->objectid.'_*_'.$this->workspaceid) as $filename) {
			unlink($filename);
		}
		$foowd->track(); return parent::delete($foowd);
	}

	function isImage() {
		if (
			$this->type == 'image/pjpeg' ||
			$this->type == 'image/jpeg' ||
			$this->type == 'image/jpg' ||
			$this->type == 'image/png' ||
			$this->type == 'image/gif'
		) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
/* display funtions */

	function displayImage($uri) {
		echo '<img src="', $uri, '&amp;method=raw" alt="', $this->getTitle(), '" />';
	}
	
	function displayIFrame($uri) {
		echo '<iframe src="', $uri, '&amp;method=raw" width="100%" height="400"></iframe>';
	}

	function displayZip($uri) {
		if (extension_loaded('zip')) {
			$zip = zip_open(FOOWD_FILE_DIR.$this->objectid.'_'.$this->version.'_'.$this->workspaceid);
			if ($zip) {
				echo '<table><tr><th>Filename</th><th>Size</th><th>Compressed</th><th>Method</th></tr>';
				while ($zip_entry = zip_read($zip)) {
					echo '<tr>';
					echo '<td>', zip_entry_name($zip_entry), '</td>';
					echo '<td>', zip_entry_filesize($zip_entry), ' bytes</td>';
					echo '<td>', zip_entry_compressedsize($zip_entry), ' bytes</td>';
					echo '<td>', zip_entry_compressionmethod($zip_entry), '</td>';
					echo '</tr>';
				}
				echo '</table>';
				zip_close($zip);
			} else {
				trigger_error('Could not open zip file.');
			}
		} else {
			echo '<a href="', $uri, '&amp;method=raw">View file</a>';
		}
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_file->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new file</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title:');
		$createFile = new input_file('createFile', 'File:', 30, FOOWD_FILE_MAX_FILESIZE);
		if ($createForm->submitted() && $createTitle->value != '') {
			if ($createFile->isUploaded()) {
				$object = new $className(
					$foowd,
					$createTitle->value,
					$createFile->file['name'],
					$createFile->file['type']
				);
				if ($object->objectid != 0 && $object->save($foowd, FALSE, TRUE, $createFile->file['tmp_name'])) {
					echo '<p>File created and saved.</p>';
					echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
				} else {
					trigger_error('Could not create file object.');
				}
			} else {
				trigger_error('The file did not upload correctly. '.$createFile->getError());
			}
		} else {
			$createForm->addObject($createTitle);
			$createForm->addObject($createFile);
			$createForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_file->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Viewing version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		echo '<table>';
		$uri = getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version));
		echo '<tr><th>Filename:</th><td>', htmlspecialchars($this->filename), '</td></tr>';
		echo '<tr><th>File Type:</th><td>', $this->type, '</td></tr>';
		if ($this->lock) {
			echo '<tr><th>Status:</th><td>Checked out by ', htmlspecialchars($this->updatorName), ' (', date(DATETIME_FORMAT, $this->updated), ')</td></tr>';
			echo '<tr><th>Actions:</th><td><a href="', $uri, '&amp;method=get">Get version</a>';
			if ($this->updatorid == $foowd->user->objectid) {
				echo ' | <a href="', $uri, '&amp;method=checkin">Check in</a>';
			}
			echo '</td></tr>';
		} else {
			echo '<tr><th>Status:</th><td>Checked in</td></tr>';
			echo '<tr><th>Actions:</th><td><a href="', $uri, '&amp;method=get">Get version</a> | <a href="', $uri, '&amp;method=checkout">Check out</a></td></tr>';
		}
		echo '</table>';
		if ($this->isImage()) {
			$this->displayImage($uri);
		} elseif ($this->type == 'application/x-zip-compressed') {
			$this->displayZip($uri);
		} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') && substr($this->type, 0, 11) == 'application') { // embed MS applicaton into IE
			$this->displayIFrame($uri);
		} elseif ($this->type == 'text/html' || $this->type == 'text/plain') {
			$this->displayIFrame($uri);
		} else {
			echo '<a href="', $uri, '&amp;method=raw">View file</a>';
		}
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_get(&$foowd) {
		$foowd->debug = FALSE;
		header('Content-type: '.$this->type);
		header('Content-Disposition: attachment; filename='.$this->filename);
		readfile(FOOWD_FILE_DIR.$this->objectid.'_'.$this->version.'_'.$this->workspaceid);
	}
	
	function method_raw(&$foowd) {
		$foowd->debug = FALSE;
		header('Content-type: '.$this->type);
		readfile(FOOWD_FILE_DIR.$this->objectid.'_'.$this->version.'_'.$this->workspaceid);
	}
	
/* check in/out */
	
	function method_checkout(&$foowd) {
		$foowd->track('foowd_file->method_checkout');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Checking out version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		if ($this->lock) {
			echo '<p>You can not check this file out as it is already checked out by ', htmlspecialchars($this->updatorName), ' (', date(DATETIME_FORMAT, $this->updated), ').</p>';
		} else {
			$this->lock = TRUE;
			if ($this->save($foowd, FALSE)) {
				echo '<p>File checked out. <a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'get')), '">Click here to download the file</a>.</p>';
			} else {
				trigger_error('Could not save object, file not checked out.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

	function method_checkin(&$foowd) {
		$foowd->track('foowd_file->method_checkin');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Checking in version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		if (!$this->lock) {
			echo '<p>This file is already checked in.</p>';
		} elseif ($this->updatorid != $foowd->user->objectid) {
			echo '<p>You can not check this file in as it is checked out by ', htmlspecialchars($this->updatorName), ' (', date(DATETIME_FORMAT, $this->updated), ').</p>';
		} else {
			$checkinForm = new input_form('checkinForm', NULL, 'POST', 'Check in', NULL, 'Don\'t Upload New Version');
			$checkinFile = new input_file('checkinFile', 'File:', 30, FOOWD_FILE_MAX_FILESIZE);
			if ($checkinForm->submitted()) {
				if ($checkinFile->isUploaded()) {
					$this->lock = FALSE;
					$this->save($foowd, FALSE);
					$this->filename = $checkinFile->file['name'];
					$this->type = $checkinFile->file['type'];
					if ($this->save($foowd, TRUE, TRUE, $checkinFile->file['tmp_name'])) {
						echo '<p>File checked in and saved.</p>';
						echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid)), '">Click here to view it now</a>.</p>';
					} else {
						trigger_error('Could not save file object.');
					}
				}
			} elseif ($checkinForm->previewed()) {
				$this->lock = FALSE;
				if ($this->save($foowd, FALSE)) {
					echo '<p>File checked in.</p>';
				} else {
					trigger_error('Could not save file object, file not checked in.');
				}
			} else {
				$checkinForm->addobject($checkinFile);
				$checkinForm->display();
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* clone object */

	function method_clone(&$foowd) {
		$foowd->track('foowd_file->method_clone');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Clone Version #', $this->version, ' Of "', $this->getTitle(), '"</h1>';
		$cloneForm = new input_form('cloneForm', NULL, 'POST', 'Clone Object', NULL);
		$cloneTitle = new input_textbox('cloneTitle', REGEX_TITLE, $this->getTitle(), 'Clone Title');
		$workspaces = $foowd->retrieveObjects(array('classid = -679419151'), array('title'));
		$workspaceArray = array(0 => 'Outside');
		if ($workspaces) {
			while ($workspace = $foowd->retrieveObject($workspaces)) {
				if ($foowd->user->inGroup($workspace->permissions['enter'])) {
					$workspaceArray[$workspace->objectid] = htmlspecialchars($workspace->title);
				}
			}
		}
		$cloneWorkspace = new input_dropdown('workspaceDropdown', NULL, $workspaceArray, 'Workspace: ');
		if ($cloneForm->submitted()) {
			$this->title = $cloneTitle->value;
			$this->objectid = crc32(strtolower($this->title));
			$this->workspaceid = $cloneWorkspace->value;
			$oldname = $this->foowd_original_access_vars['objectid'].'_'.$this->foowd_original_access_vars['version'].'_'.$this->foowd_original_access_vars['workspaceid'];
			 // adjust original workspace so as to create new object rather than overwrite old one.
			$this->foowd_original_access_vars['objectid'] = $this->objectid;
			$this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;
			if ($this->save($foowd, FALSE) && file_exists(FOOWD_FILE_DIR.$oldname)) {
				$newname = $this->objectid.'_'.$this->version.'_'.$this->workspaceid;
				copy(
					FOOWD_FILE_DIR.$oldname,
					FOOWD_FILE_DIR.$newname
				);
				echo '<p>File object cloned.</p>';
			} else {
				trigger_error('Could not clone file object.');
			}
		} else {
			$cloneForm->addObject($cloneTitle);
			$cloneForm->addObject($cloneWorkspace);
			$cloneForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>