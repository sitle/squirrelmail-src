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
class.text.ubb.php
UBB code text class
*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_TEXT_UBB_CLASS_CREATE')) define('PERMISSION_FOOWD_TEXT_UBB_CLASS_CREATE', '');
if (!defined('PERMISSION_FOOWD_TEXT_UBB_OBJECT_EDIT')) define('PERMISSION_FOOWD_TEXT_UBB_OBJECT_EDIT', 'Author');

/** CLASS DESCRIPTOR **/
if (!defined('META_1535878402_CLASSNAME')) define('META_1535878402_CLASSNAME', 'foowd_text_ubb');
if (!defined('META_1535878402_DESCRIPTION')) define('META_1535878402_DESCRIPTION', 'UBB Text Document');

/** CLASS DECLARATION **/
class foowd_text_ubb extends foowd_text_plain {

/*** MEMBER FUNCTIONS ***/

	function processCode($text) {
		$text = htmlspecialchars($text); // replace special HTML chars with HTML entities
		
		$text = preg_replace('!\[h([1-4])\](.*)\[/h\1\]\r?\n\r?\n!U', '<h$1>$2</h$1>', $text);
		$text = preg_replace('!\[h([1-4])\](.*)\[/h\1\]\r?\n!U', '<h$1>$2</h$1>', $text);
		$text = preg_replace('|\[h([1-4])\](.*)\[/h\1\]|U', '<h$1>$2</h$1>', $text);
		$text = preg_replace('|\[b\](.*)\[/b\]|U', '<b>$1</b>', $text);
		$text = preg_replace('|\[i\](.*)\[/i\]|U', '<i>$1</i>', $text);
		$text = preg_replace('|\[u\](.*)\[/u\]|U', '<u>$1</u>', $text);
		$text = preg_replace('/\[img\]((http|ftp):\/\/[a-zA-Z0-9._\/ -]+)\[\/img\]/U', '<img src="$1" alt="$1" />', $text);
		$text = preg_replace('/\[img\]([a-zA-Z0-9._\/ -]+)\[\/img\]/U', '<img src="http://$1" alt="$1" />', $text);
		$text = preg_replace('/\[((http|ftp|mailto):\/\/.+)\|([A-Za-z0-9 ]+)\]/U', '<a href="$1">$3</a>', $text);
		$text = preg_replace('/\[(ftp.[a-zA-Z0-9._\/ -]+)\|([A-Za-z0-9 ]+)\]/U', '<a href="ftp://$1">$2</a>', $text);
		$text = preg_replace('/\[(.+)\|([A-Za-z0-9 ]+)\]/U', '<a href="http://$1">$2</a>', $text);
		$text = preg_replace('/(\s|^)((http|ftp|mailto):\/\/.+)(\s|$)/U', '$1<a href="$2">$2</a>$4', $text);
		
		$text = str_replace("\n", "<br />\n", $text); // replace new lines with BR tags
		return $text;
	}

	function displayCode() {
		echo <<<END
<p>
[h1]heading 1[/h1] [h2]heading 2[/h2] [h3]heading 3[/h3] [h4]heading 4[/h4]<br />
[b]bold[/b] [i]italic[/i] [u]underline[/u]<br />
[http://www.example.com|hyperlink]<br />
[img]http://www.example.com/image.jpg[/img]<br />
</p>
END;
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_text_ubb->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new UBB page</h1>';
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL, 'Preview');
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, NULL, 'Title:');
		$createBody = new input_textarea('createBody', '', NULL, NULL, 80, 20);
		if ($createForm->submitted() && $createTitle->value != '') {
			$object = new $className(
				$foowd,
				$createTitle->value,
				$createBody->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>UBB page created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create UBB page.');
			}
		} elseif ($createForm->previewed() && $createTitle->value != '') {
			$createForm->addObject($createTitle);
			$createForm->addObject($createBody);
			$createForm->display();
			foowd_text_ubb::displayCode();
			echo '<h2>Preview</h2>';
			echo '<div class="preview">';
			echo foowd_text_ubb::processCode($createBody->value);
			echo '</div>';
		} else {
			$createForm->addObject($createTitle);
			$createForm->addObject($createBody);
			$createForm->display();
			foowd_text_ubb::displayCode();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_text_ubb->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo $this->processCode($this->body);
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* edit object */
	function method_edit(&$foowd) {
		$foowd->track('foowd_text_ubb->method_edit');
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
		foowd_text_ubb::displayCode();

		if ($editForm->submitted()) {
			if ($editCollision->value >= $this->updated) { // has not been changed since form was loaded
				$this->body = $editArea->value;
				if (isset($newVersion)) {
					$createNewVersion = !$newVersion->checked;
				} else {
					$createNewVersion = TRUE;
				}
				if ($this->save($foowd, $createNewVersion)) {
					echo '<p>UBB page updated and saved.</p>';
				} else {
					trigger_error('Could not save UBB page.');
				}
			} else { // edit collision!
				echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
			}
		} elseif ($editForm->previewed()) {
			echo '<h2>Preview</h2>';
			echo '<div class="preview">';
			echo $this->processCode($editArea->value);
			echo '</div>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}
}

?>