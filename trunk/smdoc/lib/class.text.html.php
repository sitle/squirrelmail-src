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

define('TEXT_HTML_CLASS_ID',1158898744); 

/** CLASS DESCRIPTOR **/
$foowd_class_meta[TEXT_HTML_CLASS_ID]['className'] = 'foowd_text_html';
$foowd_class_meta[TEXT_HTML_CLASS_ID]['description'] = 'HTML Text Object';

/** CLASS METHOD PERMISSIONS **/
define('FOOWD_TEXT_HTML_CREATE_PERMISSION', 'Gods');

/** CLASS METHOD PASSTHRU FUNCTION **/
function foowd_text_html_classmethod(&$foowd, $methodName) { foowd_text_html::$methodName($foowd, 'foowd_text_html'); }

/** CLASS DECLARATION **/
class foowd_text_html extends foowd_text_plain {

	var $evalCode = 0;
	var $processInclude = 0;

/*** MEMBER FUNCTIONS ***/

	function processPIs(&$foowd, $str) {
		static $includeTrack = array();
		$parts = preg_split('/(<\?|\?>)/Us', $str);
		$newStr = '';
		foreach ($parts as $part) {
			if ($this->evalCode && substr($part, 0, 3) == 'php') { // eval PHP code block
				ob_start();
				$newStr .= eval(substr($part, 3));
				$newStr .= ob_get_contents();
				ob_end_clean();
			} elseif ($this->processInclude && substr($part, 0, 7) == 'include') { // include another object
				$include = explode(' ', trim(substr($part, 7)));
				$includeObject = $include[0];
				$includeMethod = setVarConstOrDefault($include[1], 'DEFAULT_METHOD', 'view');
				if (in_array($include[0], $includeTrack) || $include[0] == $this->title) {
					$newStr .= '<p>Can not nest includes of the same object!</p>';
				} else {
					$includeTrack[] = $includeObject;
					$object = $foowd->fetchObject(array(
						'objectid' => crc32(strtolower($includeObject))
					));
					ob_start();
					$foowd->callMethod($object, $includeMethod); // call object method
					$newStr .= ob_get_contents();
					ob_end_clean();
					array_pop($includeTrack);
				}
			} else { // not a PI so just leave alone
				$newStr .= $part;
			}
		}
		return $newStr;
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['evalCode'] = '/[01]{1}/';
		$this->foowd_vars_meta['processInclude'] = '/[01]{1}/';
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		if ($this->evalCode || $this->processInclude) {
			echo $this->processPIs($foowd, $this->body);
		} else {
			echo $this->body;
		}
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
			if ($this->evalCode || $this->processInclude) {
				$body = $this->processPIs($foowd, $body);
			}
			echo '<p class="preview">', $body, '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}
	
}

?>
