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
input.checkbox.php
Checkbox input object
*/

class input_checkbox {
	
	var $name; // checkbox name
	var $checked; // value of checkbox
	var $caption; // caption to place next to checkbox
	
	function input_checkbox($name, $checked = FALSE, $caption = NULL) {
		$this->name = $name;
		if (isset($_POST[$name])) {
			$this->checked = TRUE;
		} elseif (isset($_GET[$name])) {
			$this->checked = TRUE;
		} elseif ($checked && !isset($_POST['submit'])) {
			$this->checked = TRUE;
		} else {
			$this->checked = FALSE;
		}
		$this->caption = $caption;
	}
	
	function set($value) {
		if (is_bool($value)) {
			$this->checked = $value;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function display() {
		echo '<input name="', $this->name, '" id="', $this->name, '" type="checkbox" ';
		if ($this->checked) echo 'checked="checked" ';
		echo ' title="', $this->caption, '" /> <label for="', $this->name, '">', $this->caption, '</label><br />';
	}

}

?>