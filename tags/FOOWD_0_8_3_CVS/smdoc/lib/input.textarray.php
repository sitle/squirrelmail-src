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

//*** NOTE: This input class only works with HTTP POST and not HTTP GET ***

if (!defined('INPUT_TEXTARRAY_DEFAULT_SIZE')) define('INPUT_TEXTARRAY_DEFAULT_SIZE', 50);
if (!defined('INPUT_TEXTARRAY_KEY_SIZE')) define('INPUT_TEXTARRAY_KEY_SIZE', 20);

class input_textarray {
	
	var $name; // textarray name
	var $items; // items in textarray
	var $regex; // regex value must match
	var $caption; // caption to place next to textarray
	var $class; // css class

	function input_textarray($name, $regex = NULL, $items = NULL, $caption = NULL, $class = NULL) {
		$this->name = $name;
		$this->caption = $caption;
		$this->class = $class;
		$this->regex = $regex;

		$index = 0;

		while (isset($_POST[$name.'_key_'.$index])) {
			if ($_POST[$name.'_key_'.$index] !== '' && $_POST[$name.'_key_'.$index] !== NULL) {
				if (isset($_POST[$name.'_value_'.$index])) {
					if ($_POST[$name.'_value_'.$index] !== '' && $_POST[$name.'_value_'.$index] !== NULL) {
						$value = $_POST[$name.'_value_'.$index];
						if ($this->regex == NULL || preg_match($this->regex, $value)) {
							if (get_magic_quotes_gpc()) $value = stripslashes($value);
							$this->items[$_POST[$name.'_key_'.$index]] = $value;
						}
					}
				} else {
					$this->updateSubarray($this->regex, $name.'_subarray_'.$index.'_', $this->items[$_POST[$name.'_key_'.$index]]);
				}
			} else {
				$this->items[$_POST[$name.'_key_'.$index]] = NULL;
			}
			$index++;
		}

		if (!isset($this->items)) {
			$this->items = $items;
		}
		if ($this->items == NULL) {
			$this->items = array();
		} elseif (!is_array($this->items)) {
			$this->items = array($this->items);
		}
		unset($this->items[NULL]); // remove null entries that sometimes get left behind
	}
	
	function display() {
		echo '<table>';
		echo '<tr><td valign="top">', $this->caption, '</td>';
		$foo = 0;

		foreach ($this->items as $key => $value) {
			if ($foo > 0) echo '<tr><td></td>';
			if ($key !== '') {
				if (is_array($value)) {
					echo '<td valign="top"><input name="', $this->name.'_key_'.$foo, '" type="text" value="', htmlentities($key), '" size="', INPUT_TEXTARRAY_KEY_SIZE, '" class="', $this->class, '" /></td>';
					echo '<td>';
					$this->displaySubarray($this->name.'_subarray_'.$foo, $key, $this->items, $this->regex);
					echo '</td>';
				} else {
					$maxlength = getRegexLength($this->regex, INPUT_TEXTARRAY_DEFAULT_SIZE);
					if ($maxlength == 0) {
						$maxlength = '';
						$size = INPUT_TEXTARRAY_DEFAULT_SIZE;
					} else {
						$size = intval($maxlength * 1.3);
						if ($size < INPUT_TEXTBOX_SIZE_MIN) $size = INPUT_TEXTBOX_SIZE_MIN;
						if ($size > INPUT_TEXTBOX_SIZE_MAX) $size = INPUT_TEXTBOX_SIZE_MAX;
					}
					echo '<td><input name="', $this->name.'_key_'.$foo, '" type="text" value="', htmlentities($key), '" size="', INPUT_TEXTARRAY_KEY_SIZE, '" class="', $this->class, '" /></td>';
					echo '<td><input name="', $this->name.'_value_'.$foo, '" type="text" value="', htmlentities($value), '" size="', $size, '" maxlength="', $maxlength, '" class="', $this->class, '" /></td>';
				}
				echo '</tr>';
				$foo++;
			}
		}
		if ($foo > 0) echo '<tr><td></td>';
		if (is_array($this->regex)) {
			echo '<td valign="top"><input name="', $this->name.'_key_'.$foo, '" type="text" value="" size="', INPUT_TEXTARRAY_KEY_SIZE, '" class="', $this->class, '" /></td>';
			echo '<td>';
			$this->displaySubarray($this->name.'_subarray_'.$foo, $this->name.'_key_'.$foo, NULL, $this->regex);
			echo '</td>';
		} else {
			$maxlength = getRegexLength($this->regex, INPUT_TEXTARRAY_DEFAULT_SIZE);
			if ($maxlength == 0) {
				$maxlength = '';
				$size = INPUT_TEXTARRAY_DEFAULT_SIZE;
			} else {
				$size = intval($maxlength * 1.3);
				if ($size < INPUT_TEXTBOX_SIZE_MIN) $size = INPUT_TEXTBOX_SIZE_MIN;
				if ($size > INPUT_TEXTBOX_SIZE_MAX) $size = INPUT_TEXTBOX_SIZE_MAX;
			}
			echo '<td><input name="', $this->name.'_key_'.$foo, '" type="text" value="', $foo, '" size="', INPUT_TEXTARRAY_KEY_SIZE, '" class="', $this->class, '" /></td>';
			echo '<td><input name="', $this->name.'_value_'.$foo, '" type="text" value="" size="', $size, '" maxlength="', $maxlength, '" class="', $this->class, '" /></td>';		}
		echo '</tr>';
		echo '</table>';
	}

	function updateSubarray($regex, $name, &$items) {
		foreach ($regex as $index => $reg) {
			if (is_array($reg)) {
				$this->updateSubarray($reg, $name.'value_'.$index.'_', $items[$index]);
			} else {
				if (is_numeric($index)) {
					while (isset($_POST[$name.'key_'.$index])) {
						if ($_POST[$name.'key_'.$index] !== '' && $_POST[$name.'key_'.$index] !== NULL && isset($_POST[$name.'value_'.$index])) {
							$items[$_POST[$name.'key_'.$index]] = $_POST[$name.'value_'.$index];
						}
						$index++;
					}
				} else {
					if (isset($_POST[$name.'value_'.$index])) {
						$value = $_POST[$name.'value_'.$index];
						if ($reg == NULL || preg_match($reg, $value)) {
							if (get_magic_quotes_gpc()) $value = stripslashes($value);
							if (isset($_POST[$name.'key_'.$index])) {
								$items[$_POST[$name.'key_'.$index]] = $value;
							} else {
								$items[$index] = $value;
							}
						}
					}
				}
			}
		}
	}

	function displaySubarray($subName, $subKey, $subItems, $subRegex) {
		echo '<table>';
		foreach ($subRegex as $index => $regex) {
			if (is_numeric($index) && !is_array($regex)) {
				$maxlength = getRegexLength($regex, INPUT_TEXTARRAY_DEFAULT_SIZE);
				if ($maxlength == 0) {
					$maxlength = '';
					$size = INPUT_TEXTARRAY_DEFAULT_SIZE;
				} else {
					$size = intval($maxlength * 1.3);
					if ($size < INPUT_TEXTBOX_SIZE_MIN) $size = INPUT_TEXTBOX_SIZE_MIN;
					if ($size > INPUT_TEXTBOX_SIZE_MAX) $size = INPUT_TEXTBOX_SIZE_MAX;
				}
				if (is_array($subItems[$subKey])) {
					foreach ($subItems[$subKey] as $key => $value) {
						echo '<tr><td><input type="text" name="', $subName, '_key_', $index , '" value="', $key, '" size="', INPUT_TEXTARRAY_KEY_SIZE, '" /></td><td>';
						echo '<input type="text" name="', $subName, '_value_', $index , '" value="', $value, '" size="', $size, '" maxlength="', $maxlength, '" />';
						echo '</td></tr>';
						$index++;
					}
				}
				echo '<tr><td><input type="text" name="', $subName, '_key_', $index , '" size="', INPUT_TEXTARRAY_KEY_SIZE, '" value="', $index, '" /></td><td>';
				echo '<input type="text" name="', $subName, '_value_', $index , '" size="', $size, '" maxlength="', $maxlength, '" value="" />';
				echo '</td></tr>';
			} else {
				echo '<tr><td>', $index, '</td><td>';
				if (is_array($regex)) {
					$this->displaySubarray($subName.'_value_'.$index, $index, $subItems[$subKey], $regex);
				} else {
					$maxlength = getRegexLength($regex, INPUT_TEXTARRAY_DEFAULT_SIZE);
					if ($maxlength == 0) {
						$maxlength = '';
						$size = INPUT_TEXTARRAY_DEFAULT_SIZE;
					} else {
						$size = intval($maxlength * 1.3);
						if ($size < INPUT_TEXTBOX_SIZE_MIN) $size = INPUT_TEXTBOX_SIZE_MIN;
						if ($size > INPUT_TEXTBOX_SIZE_MAX) $size = INPUT_TEXTBOX_SIZE_MAX;
					}
					echo '<input type="text" name="', $subName, '_value_', $index , '" value="';
					if (isset($subItems[$subKey][$index])) {
						echo $subItems[$subKey][$index];
					}
					echo '" size="', $size, '" maxlength="', $maxlength, '" />';
				}
				echo '</td></tr>';
			}
		}
		echo '</table>';
	}

}
