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
input.textbox.php
Textbox input object
*/

if (!defined('INPUT_TEXTBOX_SIZE_MIN')) define('INPUT_TEXTBOX_SIZE_MIN', 4);
if (!defined('INPUT_TEXTBOX_SIZE_MAX')) define('INPUT_TEXTBOX_SIZE_MAX', 50);

class input_textbox {
	
	var $name; // textbox name
	var $value; // value of textbox
	var $regex; // regex value must match
	var $caption; // caption to place next to textbox
	var $size; // size of textbox
	var $maxlength; // maxlength of text allowed
	var $class; // css class
	
	function input_textbox($name, $regex = NULL, $value = NULL, $caption = NULL, $size = NULL, $maxlength = NULL, $class = NULL) {
		$this->name = $name;
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
			$this->maxlength = getRegexLength($this->regex, 16);
		}
		if ($this->maxlength == 0) $this->maxlength = '';
		if ($size) {
			$this->size = $size;
		} elseif ($this->maxlength == '') {
			$this->size = INPUT_TEXTBOX_SIZE_MAX;
		} else {
			$this->size = $this->maxlength * 1.5;
			if ($this->size < INPUT_TEXTBOX_SIZE_MIN) $this->size = INPUT_TEXTBOX_SIZE_MIN;
			if ($this->size > INPUT_TEXTBOX_SIZE_MAX) $this->size = INPUT_TEXTBOX_SIZE_MAX;
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
		echo $this->caption, ' <input name="', $this->name, '" type="text" value="', htmlentities($this->value), '" size="', $this->size, '" maxlength="', $this->maxlength, '" class="', $this->class, '" />';
	}

}

class input_passwordbox extends input_textbox {

	function display() {
		echo $this->caption, ' <input name="', $this->name, '" type="password" value="', htmlentities($this->value), '" size="', (int)($this->size / 1.5), '" maxlength="', $this->maxlength, '" class="', $this->class, '" />';
	}

}

class input_hiddenbox extends input_textbox {

	function display() {
		echo '<input name="', $this->name, '" type="hidden" value="', htmlentities($this->value), '" maxlength="', $this->maxlength, '" />';
	}

}

?>