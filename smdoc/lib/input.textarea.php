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
input.textarea.php
Textarea input object
*/

if (!defined('INPUT_TEXTAREA_WIDTH_MIN')) define('INPUT_TEXTAREA_WIDTH_MIN', 20);
if (!defined('INPUT_TEXTAREA_WIDTH_MAX')) define('INPUT_TEXTAREA_WIDTH_MAX', 80);
if (!defined('INPUT_TEXTAREA_HEIGHT_MIN')) define('INPUT_TEXTAREA_HEIGHT_MIN', 4);
if (!defined('INPUT_TEXTAREA_HEIGHT_MAX')) define('INPUT_TEXTAREA_HEIGHT_MAX', 20);

class input_textarea {
	
	var $name; // textbox name
	var $value; // value of textbox
	var $regex; // regex value must match
	var $caption; // caption to place next to textbox
	var $width, $height; // size of textarea
	var $maxlength; // maxlength of text allowed
	var $class; // css class
	
	function input_textarea($name, $regex = NULL, $value = NULL, $caption = NULL, $width = NULL, $height = NULL, $maxlength = NULL, $class = NULL) {
		$this->name = $name;
		$newValue = NULL;
		$this->regex = $regex;
		if (isset($_POST[$name])) {
			$this->set($_POST[$name]);
		} elseif (isset($_GET[$name])) {
			$this->set($_GET[$name]);
		}
		if (!isset($this->value)) {
			$this->value = $value;
		}

		$this->caption = $caption;
		if (isset($maxlength)) {
			$this->maxlength = $maxlength;
		} else {
			$this->maxlength = getRegexLength($this->regex, INPUT_TEXTAREA_WIDTH_MAX);
		}
		if ($this->maxlength == 0) $this->maxlength = '';
		if ($width) {
			$this->width = $width;
		} elseif ($this->maxlength == '') {
			$this->width = INPUT_TEXTAREA_WIDTH_MAX;
		} else {
			$this->width = (int)($this->maxlength / 2);
			if ($this->width < INPUT_TEXTAREA_WIDTH_MIN) $this->width = INPUT_TEXTAREA_WIDTH_MIN;
			if ($this->width > INPUT_TEXTAREA_WIDTH_MAX) $this->width = INPUT_TEXTAREA_WIDTH_MAX;
		}
		if ($height) {
			$this->height = $height;
		} elseif ($this->maxlength == '') {
			$this->height = INPUT_TEXTAREA_HEIGHT_MAX;
		} else {
			$this->height = (int)($this->maxlength / 10);
			if ($this->height < INPUT_TEXTAREA_HEIGHT_MIN) $this->height = INPUT_TEXTAREA_HEIGHT_MIN;
			if ($this->height > INPUT_TEXTAREA_HEIGHT_MAX) $this->height = INPUT_TEXTAREA_HEIGHT_MAX;
		}
		$this->class = $class;
	}
	
	function set($value) {
		if ($this->regex == NULL || preg_match($this->regex, $value)) {
			if (get_magic_quotes_gpc()) $value = stripslashes($value);
			$this->value = $value;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function display() {
		echo $this->caption, ' <textarea name="', $this->name, '" cols="', $this->width, '" rows="', $this->height, '" wrap="virtual" class="', $this->class, '">', htmlentities($this->value), '</textarea>';
	}

}

?>