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
input.checkgroup.php
Checkbox group input object
*/

class input_checkgroup {
	
	var $name; // checkgroup name
	var $value; // value of checkgroup
	var $checks; // array of checkboxes
	var $class; // css class
	
	function input_checkgroup($name, $value = NULL, $checks = NULL, $class = NULL) {
		$this->name = $name;
		$this->checks = $checks;
		if (isset($_POST)) {
			$val = 0;
			foreach($_POST as $post => $index) {
				if ($post == $this->name.'_'.$index) {
					$val = $val + $index;
				}
			}
			$this->set($val);
		} elseif (isset($_GET)) {
			$val = 0;
			foreach($_GET as $get => $index) {
				if ($get == $this->name.'_'.$index) {
					$val = $val + $index;
				}
			}
			$this->set($val);
		}
		if (!isset($this->value)) {
			$this->set($value);
		}
		$this->class = $class;
	}
	
	function set($value) {
		if ($value <= pow(2, count($this->checks)) - 1) {
			$this->value = $value;
			return TRUE;
		}
		return FALSE;
	}
	
	function isChecked($checkName) {
		$key = array_search($checkName, $this->checks);
		if ($key) {
			$index = pow(2, $key);
			if ($this->value & $index) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function display() {
		$index = 1;
		foreach ($this->checks as $check) {
			echo '<input name="', $this->name, '_', $index, '" id="', $this->name, '_', $index, '" type="checkbox" value="', $index, '" ';
			if ($this->value & $index) echo 'checked="checked" ';
			echo 'title="', $check, '" class="', $this->class, '" /> <label for="', $this->name, '_', $index, '">', $check, '</label>';
			$index = $index * 2;
		}
	}

}

?>