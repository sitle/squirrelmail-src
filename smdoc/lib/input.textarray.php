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
input.textarray.php
Textarray input object
*/

define('INPUT_TEXTARRAY_SIZE_MIN', 4);
define('INPUT_TEXTARRAY_SIZE_MAX', 20);

class input_textarray {
	
	var $name; // textarray name
	var $items; // items in textarray
	var $regex; // regex value must match
	var $caption; // caption to place next to textarray
	var $size; // size of textarray
	var $maxlength; // maxlength of text allowed
	
	function input_textarray($name, $regex = NULL, $items = NULL, $caption = NULL, $size = NULL, $maxlength = NULL) {
		$this->name = $name;
		$this->regex = $regex;
		$index = 0;
		while (isset($_POST[$name.'_key_'.$index]) || isset($_GET[$name.'_key_'.$index])) {
			if (isset($_POST[$name.'_key_'.$index])) {
				$this->set($_POST[$name.'_key_'.$index], $_POST[$name.'_value_'.$index]);
			} elseif (isset($_GET[$name.'_key_'.$index])) {
				$this->set($_GET[$name.'_key_'.$index], $_GET[$name.'_value_'.$index]);
			}
			$index++;
		}
		if (!isset($this->items)) {
			$this->items = $items;
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
			$this->size = INPUT_TEXTARRAY_SIZE_MAX;
		} else {
			$this->size = $this->maxlength * 1.5;
			if ($this->size < INPUT_TEXTARRAY_SIZE_MIN) $this->size = INPUT_TEXTARRAY_SIZE_MIN;
			if ($this->size > INPUT_TEXTARRAY_SIZE_MAX) $this->size = INPUT_TEXTARRAY_SIZE_MAX;
		}

	}
	
	function set($key, $value) {
		if ($this->regex == NULL || preg_match($this->regex, $value)) {
			if (get_magic_quotes_gpc()) $value = stripslashes($value);
			$this->items[$key] = $value;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function display() {
		echo '<table>';
		echo '<tr><td>', $this->caption, '</td>';
		$foo = 0;
		foreach ($this->items as $key => $value) {
			if ($foo > 0) echo '<tr><td></td>';
			echo '<td><input name="', $this->name.'_key_'.$foo, '" type="text" value="', htmlentities($key), '" size="', $this->size, '" /></td>';
			echo '<td><input name="', $this->name.'_value_'.$foo, '" type="text" value="', htmlentities($value), '" size="', $this->size, '" maxlength="', $this->maxlength, '" /></td>';
			echo '</tr>';
			$foo++;
		}
		if ($foo > 0) echo '<tr><td></td>';
		echo '<td><input name="', $this->name.'_key_'.$foo, '" type="text" value="" size="', $this->size, '" /></td>';
		echo '<td><input name="', $this->name.'_value_'.$foo, '" type="text" value="" size="', $this->size, '" maxlength="', $this->maxlength, '" /></td>';
		echo '</tr>';
		echo '</table>';
	}

}
