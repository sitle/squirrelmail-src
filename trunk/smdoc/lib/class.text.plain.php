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
 * Modified by SquirrelMail Development Team
 *
 * $Id$
 */

define('TEXT_PLAIN_CLASS_ID', -518019526);

/** CLASS DESCRIPTOR **/
$foowd_class_meta[TEXT_PLAIN_CLASS_ID]['className'] = 'foowd_text_plain';
$foowd_class_meta[TEXT_PLAIN_CLASS_ID]['description'] = 'Plain Text Object';

/** CLASS METHOD PERMISSIONS **/
define('FOOWD_TEXT_PLAIN_CREATE_PERMISSION', 'Gods');

/** CLASS METHOD PASSTHRU FUNCTION **/
function foowd_text_plain_classmethod(&$foowd, $methodName) { 
    foowd_text_plain::$methodName($foowd, 'foowd_text_plain'); 
}

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

// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->body = $body;

/* set method permissions */
		$this->permissions['edit'] = setVarConstOrDefault($editGroup, 'DEFAULT_EDIT_GROUP', 'Everyone');

	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['body'] = '';
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new text object</h1>';
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, NULL, 'Object Title:');
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
			if ($object->save($foowd, FALSE)) {
				echo '<p>Object created and saved.</p>';
			} else {
				echo '<p>Could not create object.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		$body = $this->body;
		$body = htmlspecialchars($body);
		$body = str_replace("\n", "<br />\n", $body);
		echo $body;
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/* edit object */
	function method_edit(&$foowd) {
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
					echo '<p>Object updated and saved.</p>';
				} else {
					echo '<p>Could not save object.</p>';
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
	}

/* calculate diff */
	function method_diff(&$foowd) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		if (defined('DIFF_COMMAND')) {

			$object = $foowd->fetchObject(array('objectid' => $this->objectid, 'classid' => $this->classid, 'workspaceid' => $this->workspaceid));

			echo '<h1>Diff "', $this->getTitle(), '"</h1>';
			echo '<p>Differences between versions ', $this->version, ' and ', $object->version, ' of "', $this->getTitle(), '".</p>';

			$fileid = time();
            $path = setConstOrDefault('DIFF_TMPDIR',$_ENV['TEMP']);
			$oldFile = $path.'/foowd_diff_'.$fileid.'-1';
			$newFile = $path.'/foowd_diff_'.$fileid.'-2';
					
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
					echo '<p>Error occured running diff engine "', DIFF_COMMAND, '".</p>';
				} elseif ($diffResult == FALSE) {
					echo '<p>Versions are identical.</p>';
				} else { // parse output to be nice

					$diffResultArray = explode("\n", $diffResult);

					echo '<table>';
					foreach($diffResultArray as $diffLine) {
						if (preg_match(DIFF_ADD_REGEX, $diffLine, $lineResult)) {
							echo '<tr class="diff_add"><td class="diff_line">+</td><td>', htmlspecialchars($lineResult[1]), '</td></tr>';
						} elseif (preg_match(DIFF_MINUS_REGEX, $diffLine, $lineResult)) {
							echo '<tr class="diff_minus"><td class="diff_line">-</td><td>', htmlspecialchars($lineResult[1]), '</td></tr>';
						}
					}
					echo '</table>';

				}
			}

			unlink($oldFile);
			unlink($newFile);
		
			ignore_user_abort(FALSE); // all done, it's ok to abort now

		} else {
			echo '<p>Diffs have been disabled.</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

}

?>
