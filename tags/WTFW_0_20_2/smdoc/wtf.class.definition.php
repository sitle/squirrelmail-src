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
wtf.class.definition.php
Definition Class
*/

$HARDCLASS[1747988440] = 'definition';

if (!defined('DEFINITIONCREATE')) define('DEFINITIONCREATE', GODS);

class definition extends thing { // a dynamic class definition

	var $class;

/*** Constructor ***/

	function definition(
		&$user,
		$title = NULL, // class name
		$class = '', // class definition code
		$viewGroup = DEFAULTVIEWGROUP,
		$editGroup = DEFAULTEDITGROUP,
		$deleteGroup = DEFAULTDELETEGROUP,
		$adminGroup = DEFAULTADMINGROUP
	) {
		track('definition::definition', $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		parent::thing($user, $title, $viewGroup, $editGroup, $deleteGroup, $adminGroup);
		$this->class = $class;
		track();
	}

/*** Member Functions ***/

	function update($code, $incrementVersion = TRUE) { // update class
		global $wtf;
		track('definition::update');
		if (preg_match('/class '.$this->title.' extends /i', $classDefinition)) {
			if (!class_exists($this->title)) {
				ob_start();
				$result = eval($code); // evaluate code and get return results
				$output = ob_get_contents().$result; // capture code output and append return results
				ob_end_clean();
				if ($result === FALSE) {
					track(); return array('success' => FALSE, 'error' => htmlspecialchars(preg_replace('|^.*<b>Parse error</b>:  parse error, (.+) in <b>.+\([0-9]+\) : eval\(\)\'d code</b> on line <b>([0-9]+)</b>.*$|s', 'PHP parse error: \\1 on line \\2', $code)));
				} else {
					parent::update($wtf->user, $incrementVersion);
					$this->class = $code;
					track(); array('success' => TRUE);
				}
			} else {
				track(); return array('success' => FALSE, 'error' => 'Class &quot;'.$this->title.'&quot; already definted in the system.');
			}
		} else {
			track(); array('success' => FALSE, 'error' => 'Can not find class definition &quot;'.$this->title.'&quot;.');
		}
	}
	
	function validate($code) {
		track('definition::validate');
		if (preg_match('/class '.$this->title.' extends /i', $code)) {
			if (!class_exists($this->title)) {
				ob_start();
				$result = eval($code); // evaluate code and get return results
				$output = ob_get_contents().$result; // capture code output and append return results
				ob_end_clean();
				if ($result === FALSE) {
					track(); return array('success' => FALSE, 'error' => htmlspecialchars(preg_replace('|^.*<b>Parse error</b>:  parse error, (.+) in <b>.+\([0-9]+\) : eval\(\)\'d code</b> on line <b>([0-9]+)</b>.*$|s', 'PHP parse error: \\1 on line \\2', $output)));
				} else {
					track(); return array('success' => TRUE);
				}
			} else {
				track(); return array('success' => FALSE, 'error' => 'Class &quot;'.$this->title.'&quot; already definted in the system.');
			}
		} else {
			track(); return array('success' => FALSE, 'error' => 'Valid class definiton &quot;'.$this->title.'&quot; not found.');
		}
	}

	function drawForm($url, $title = NULL, $titleIsEditable = FALSE, $content = NULL, $small = NULL, $loadtime = NULL) {
		global $wtf;
		if (isset($this)) {
			$objectid = $this->objectid;
			$version = $this->version;
			$updatorid = $this->updatorid;
			if ($title == NULL) $title = $this->title;
			if ($content == NULL) $content = $this->content;
		} else {
			$objectid = '';
			$version = 1;
			$updatorid = NULL;
			if ($title == NULL) $title = '';
			if ($content == NULL) $content = '';
		}
		if ($loadtime == NULL) $loadtime = time();
		echo '<definition_form url="'.$url.'" ';
		echo 'thingidfield="thingid" thingid="', $objectid, '" ';
		echo 'versionfield="version" version="', $version, '" ';
		echo 'loadtimefield="loadtime" loadtime="'.$loadtime.'" submit="submit" validate="validate">';
		if ($titleIsEditable) {
			echo '<definition_titlebox name="title" maxlength="', MAXTITLELENGTH, '">', $title, '</definition_titlebox>';
		} else {
			echo '<definition_title title="', $title, '" version="', $version, '"/>';
		}
		echo '<definition_canvas name="content" maxlength="', MAXCONTENTLENGTH, '">', $content, '</definition_canvas>';
		if ($wtf->user->objectid == $updatorid && $wtf->user->objectid != ANONYMOUSUSERID && isset($small)) {
			if ($small == 'on') {
				echo '<content_smallupdate checked="checked" name="small"/>';
			} else {
				echo '<content_smallupdate name="small"/>';
			}
		}
		echo '</definition_form>';
	}

/*** Methods ***/

// create
	function method_create($thingName = NULL, $objectName = 'definition') {
		global $conn, $wtf;
		track('definition::method::create');

		if ($wtf->user->inGroup(DEFINITIONCREATE)) { // check permission

			if (isset($this)) {
				$url = THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=create';
				$objectName = get_class($this);
			} else {
				$url = THINGURI.$thingName.'&amp;class=hardclass';
			}

			$create = getValue('submit', FALSE);
			$validate = getValue('validate', FALSE);

			if ($create || $validate) { // is action to do

				$title = getValue('title', FALSE);
				$content = getValue('content', FALSE);
	// create object
				$thing = new $objectName(
					$wtf->user,
					$title,
					$content
				);
				if ($thing && $thing->objectid != 0) { // action to do
					$result = $thing->validate($content);
					if ($result['success']) {
						if ($create) { // create thing
							$thing->save();
							header('Location: '.THINGIDURI.$thing->objectid.'&amp;class='.get_class($thing));
							exit;
						} elseif ($validate) { // show preview
							echo '<definition_validate_success/>';
						}
					} else {
						echo '<definition_validate_fail message="', $result['error'], '"/>';
					}
					$content = htmlspecialchars($content);
		// display form
					definition::drawForm($url, $title, TRUE, $content);
				} else {
					echo '<definition_create_failed message="Could not create object ', htmlspecialchars($title), '"/>';
				}
			} else { // display empty form
				definition::drawForm($url, NULL, TRUE, NULL);
			}
		} else {
			echo '<definition_create_permission/>';
		}
		track();
	}

// edit
	function method_edit() { // edit thing
		global $conn, $wtf;
		track('definition::method::edit');
		if (hasPermission($this, $wtf->user, 'editGroup')) {	// check permission
			$update = getValue('submit', FALSE);
			$validate = getValue('validate', FALSE);
			$small = getValue('small', FALSE);		
			$content = getValue('content', FALSE);
			$loadtime = getValue('loadtime', NULL);

			if ($update) { // update thing
				if ($small == 'on' && $wtf->user->objectid == $this->updatorid && $wtf->user->objectid != ANONYMOUSUSERID) {
					$incrementVersion = FALSE;
				} else {
					$incrementVersion = TRUE;
				}

// check it hasn't been updated since loading the form
				if (!isset($loadtime) || !is_numeric($loadtime) || $loadtime < dbdate2unixtime($this->updatorDatetime)) {
					echo '<definition_edit_locked/>';
// if we have content, update the object
				} elseif ($content) {
					$result = $this->update($content, $incrementVersion);
					if ($result['success']) {
						$this->save(); // save thing to database
						$this->tidyArchive(); // tidy up archived versions of thing
						echo '<definition_edit_updated/>';
					} else {
						echo '<definition_edit_failed message="', $result['error'], '"/>';
					}
				}
				$title = $this->title;
				$content = htmlspecialchars($this->class);

			} elseif ($validate) { // show preview
				if ($content) {
					$result = $this->validate($content);
					if ($result['success']) {
						echo '<definition_validate_success/>';
					} else {
						echo '<definition_validate_fail message="', $result['error'], '"/>';
					}
				}
				$title = $this->title;
				$content = htmlspecialchars($content);

			} else { // get thing contents for editing
				$title = $this->title;
				$content = htmlspecialchars($this->class);
			}

	// display form
			$this->drawForm(THINGIDURI.$this->objectid.'&amp;class='.get_class($this).'&amp;op=edit', $title, FALSE, $content, $small, $loadtime);

		} else {
			echo '<thing_permissionerror method="edit" title="'.$this->title.'"/>';
		}
		track();
	}
	
}

$NOPARSETAG[] = 'definition_canvas';

// formatting
$FORMAT = array_merge($FORMAT, array(

// form
	'definition_form' => '<form method="post" action="{url}"><input type="hidden" name="{thingidfield}" value="{thingid}" /><input type="hidden" name="{versionfield}" value="{version}" /><input type="hidden" name="{loadtimefield}" value="{loadtime}" />',
	'/definition_form' => '<p><input type="submit" name="{submit}" value="Save" /> <input type="submit" name="{validate}" value="Syntax Check" /></p></form>',
	'definition_title' => '<p>Editing thing "<a href="'.THINGURI.'{title}">{title}</a>" (version {version})</p>',
	'/definition_title' => '',
	'definition_titlebox' => '<p>Title: <input type="text" name="{name}" size="50" maxlength="{maxlength}" value="',
	'/definition_titlebox' => '"/></p>',
	'definition_canvas' => '<p><textarea name="{name}" rows="22" cols="80" maxlength="{maxlength}" style="width: 100%;" wrap="virtual">',
	'/definition_canvas' => '</textarea></p>',
	'definition_smallupdate' => '<p><input type="checkbox" name="{name}" id={name} /> <label for="{name}">This is a small update so don\'t archive the previous version.</label></p>',
	'definition_smallupdate.checked' => '<p><input type="checkbox" name="{name}" id={name} checked="checked" /> <label for="{name}">This is a small update so don\'t archive the previous version.</label></p>',
	'/definition_smallupdate' => '',
	'definition_validate_success' => '<h2 class="success">Definition Validated</h2>',
	'/definition_validate_success' => '',
	'definition_validate_fail' => '<h2 class="fail">Validation Failed.</h2><p>{message}</p>',
	'/definition_validate_fail' => '',

// edit
	'definition_edit_updated' => '<h2 class="success">Edit Successful</h2><p>Page updated successfully.</p>',
	'/definition_edit_updated' => '',
	'definition_edit_failed' => '<h2 class="fail">Edit Failed</h2><p>{message}</p>',
	'/definition_edit_failed' => '',
	'definition_edit_locked' => '<p class="error">This page is currently being updated by someone else, please review their changes before making your own.</p>',
	'/definition_edit_locked' => '',
	
// create
	'definition_create_updated' => '<h2 class="success">Create Successful</h2><p>Page created successfully.</p>',
	'/definition_create_updated' => '',
	'definition_create_failed' => '<h2 class="fail">Create Failed</h2><p>{message}</p>',
	'/definition_create_failed' => '',
	'definition_create_permission' => '<p class="error">You do not have permission to create a new definition.</p>',
	'/definition_create_permission' => ''

));
?>