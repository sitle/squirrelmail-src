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
class.text.plain.php
Foowd plain text class
*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_TEXT_PLAIN_OBJECT_EDIT')) define('PERMISSION_FOOWD_TEXT_PLAIN_OBJECT_EDIT', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_-518019526_CLASSNAME')) define('META_-518019526_CLASSNAME', 'foowd_text_plain');
if (!defined('META_-518019526_DESCRIPTION')) define('META_-518019526_DESCRIPTION', 'Plain Text Document');

/** CLASS DECLARATION **/
class foowd_text_plain extends foowd_object {

	var $body;
	
/*** CONSTRUCTOR ***/

	function foowd_text_plain(
		&$foowd,
		$title = NULL,
		$body = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$editGroup = NULL
	) {
		$foowd->track('foowd_text_plain->constructor');
	
// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->body = $body;

/* set method permissions */
		$className = get_class($this);
		$this->permissions['edit'] = getPermission($className, 'edit', 'object'. $editGroup);

		$foowd->track();
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['body'] = '';
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_text_plain->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new text object</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Object Title:');
		$createBody = new input_textarea('createBody', '', NULL, NULL, 80, 20);
		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->addObject($createBody);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value,
				$createBody->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>Text object created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create text object.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_text_plain->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		$body = $this->body;
		$body = htmlspecialchars($body);
		$body = str_replace("\n", "<br />\n", $body);
		echo $body;
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* edit object */
	function method_edit(&$foowd) {
		$foowd->track('foowd_text_plain->method_edit');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Editing version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		$editForm = new input_form('editForm', NULL, 'POST', 'Save', NULL, 'Preview');
		$editCollision = new input_hiddenbox('editCollision', REGEX_DATETIME, time());
		if ($editCollision->value >= $this->updated && $editForm->submitted()) { // if we're going to update, reset collision detect
			$editCollision->set(time());		
		}
		$editForm->addObject($editCollision);
		$editArea = new input_textarea('editArea', NULL, $this->body, NULL, 80, 20);
		$editForm->addObject($editArea);
		if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
			$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
			$editForm->addObject($newVersion);
		}
		$editForm->display();

		if ($editForm->submitted()) {
			if ($editCollision->value >= $this->updated) { // has not been changed since form was loaded
				$this->body = $editArea->value;
				if (isset($newVersion)) {
					$createNewVersion = !$newVersion->checked;
				} else {
					$createNewVersion = TRUE;
				}
				if ($this->save($foowd, $createNewVersion)) {
					echo '<p>Text object updated and saved.</p>';
				} else {
					trigger_error('Could not save text object.');
				}
			} else { // edit collision!
				echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
			}
		} elseif ($editForm->previewed()) {
			echo '<h3>Preview</h3>';
			$body = $editArea->value;
			$body = htmlspecialchars($body);
			$body = str_replace("\n", "<br />\n", $body);
			echo '<p class="preview">', $body, '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* object history */

	function method_history(&$foowd) {
		$foowd->track('foowd_text_plain->method_history');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>History of Object "', $this->getTitle(), '"</h1>';
		$objArray = $foowd->getObject(array(
			'objectid' => $this->objectid,
			'classid' => $this->classid
		));
		
		echo '<h3>Object Details</h3>';
		echo '<p>';
		echo '<em>Title:</em> ', $this->getTitle(), '<br />';
		echo '<em>Created:</em> ', date(DATETIME_FORMAT, $this->created), '<br />';
		echo '<em>Author:</em> ', $this->creatorName, '<br />';
		echo '<em>Object Type:</em> ', getClassDescription($this->classid), '<br />';
		if ($this->workspaceid != 0) {
			echo '<em>Workspace:</em> ', $this->workspaceid, '<br />';
		}
		echo '</p>';
		
		echo '<h3>Archived Versions</h3>';
		echo '<table border="1">';
		echo '<tr>';
		echo '<th>Date</th>';
		echo '<th>Author</th>';
		echo '<th>Page Version</th>';
		echo '</tr>';
		$foo = FALSE;
		foreach ($objArray as $object) {
			echo '<tr>';
			echo '<td>', date(DATETIME_FORMAT, $object->updated), '</td>';
			echo '<td>', $object->updatorName, '</td>';
			echo '<td><a href="', getURI(array('method' => 'view', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">', $object->version, '</a></td>';
			if ($foo) {
				echo '<td><a href="', getURI(array('method' => 'diff', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">Diff</a></td>';
				echo '<td><a href="', getURI(array('method' => 'revert', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">Revert</a></td>';
			}
			echo '</tr>';
			$foo = TRUE;
		}
		echo '</table>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* calculate diff */
	function method_diff(&$foowd) {
		$foowd->track('foowd_text_plain->method_diff');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		if (defined('DIFF_COMMAND')) {
		
			$object = $foowd->fetchObject(array('objectid' => $this->objectid, 'classid' => $this->classid, 'workspaceid' => $this->workspaceid));

			echo '<h1>Diff of "', $this->getTitle(), '"</h1>';

			if ($this->version == $object->version) {
			
				echo '<p>You can not compare a version to itself.</p>';
				echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version, 'method' => 'history')), '">Click here to view the archived versions of this object</a>.</p>';
			
			} else {

				echo '<p>Differences between versions ', $this->version, ' and ', $object->version, ' of "', $this->getTitle(), '".</p>';

				$fileid = time();
				
				$temp_dir = getConst('DIFF_TMPDIR');
				
				$oldFile = $temp_dir.'/foowd_diff_'.$fileid.'-1';
				$newFile = $temp_dir.'/foowd_diff_'.$fileid.'-2';

				$oldPage = $this->body;
				$newPage = $object->body;

				ignore_user_abort(TRUE); // don't halt if aborted during diff

				if (!($fp1 = fopen($oldFile, 'w')) || !($fp2 = fopen($newFile, 'w'))) {
					echo '<p>Could not create temp files required for diff engine.</p>';
				} elseif (fwrite($fp1, $oldPage) < 0 || fwrite($fp2, $newPage) < 0) {
					echo '<p>Could not write to temp files required for diff engine.</p>';
				} else {

					fclose($fp1);
					fclose($fp2);

					$diffResult = shell_exec(DIFF_COMMAND.' '.$oldFile.' '.$newFile);

					if ($diffResult === FALSE) {
						trigger_error('Error occured running diff engine "', DIFF_COMMAND, '".');
					} elseif ($diffResult == FALSE) {
						echo '<p>Versions are identical.</p>';
					} else { // parse output to be nice

						$diffResultArray = explode("\n", $diffResult);
						
						$diffAddRegex = getConstOrDefault('DIFF_ADD_REGEX', '/^>(.*)$/');
						$diffMinusRegex = getConstOrDefault('DIFF_MINUS_REGEX', '/^<(.*)$/');

						echo '<table>';
						foreach($diffResultArray as $diffLine) {
							if (preg_match($diffAddRegex, $diffLine, $lineResult)) {
								echo '<tr class="diff_add"><td class="diff_line">+</td><td>', htmlspecialchars($lineResult[1]), '</td></tr>';
							} elseif (preg_match($diffMinusRegex, $diffLine, $lineResult)) {
								echo '<tr class="diff_minus"><td class="diff_line">-</td><td>', htmlspecialchars($lineResult[1]), '</td></tr>';
							}
						}
						echo '</table>';

					}
				}

				unlink($oldFile);
				unlink($newFile);

				ignore_user_abort(FALSE); // all done, it's ok to abort now
			
			}

		} else {
			echo '<p>Diffs have been disabled.</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>