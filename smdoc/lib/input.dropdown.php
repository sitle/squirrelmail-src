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
input.dropdown.php
Dropdown box input object
*/

class input_dropdown {
	
	var $name; // dropdown name
	var $value; // value of dropdown
	var $caption; // caption to place next to dropdown
	var $height; // height of dropdown
	var $items; // items in dropdown
	
	function input_dropdown($name, $value = NULL, $items = NULL, $caption = NULL, $height = 1) {
		$this->name = $name;
		$this->items = $items;
		if (isset($_POST[$name])) {
			$this->set($_POST[$name]);
		} elseif (isset($_GET[$name])) {
			$this->set($_GET[$name]);
		}
		if (!isset($this->value)) {
			$this->value = $value;
		}
		$this->caption = $caption;
		$this->height = $height;
	}
	
	function set($value) {
		reset($this->items);
		if ($value >= key($this->items)) {
			end($this->items);
			if ($value <= key($this->items)) {
				$this->value = $value;
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function display() {
		echo $this->caption, ' <select name="', $this->name, '" size="', $this->height, '">';
		foreach ($this->items as $value => $item) {
			echo '<option value="', $value, '"';
			if ($this->value == $value) echo ' selected="selected"';
			echo '>', $item, '</option>';
		}
		echo '</select><br />';
	}

}
