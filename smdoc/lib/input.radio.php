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
input.radio.php
Radio button input object
*/

class input_radio {
	
	var $name; // radio name
	var $value; // value of radio
	var $buttons; // array of radio buttons
	var $class; // css class
	
	function input_radio($name, $value = NULL, $buttons = NULL, $class = NULL) {
		$this->name = $name;
		$this->buttons = $buttons;
		if (isset($_POST[$name])) {
			$this->set($_POST[$name]);
		} elseif (isset($_GET[$name])) {
			$this->set($_GET[$name]);
		}
		if (!isset($this->value)) {
			$this->value = $value;
		}
		$this->class = $class;
	}
	
	function set($value) {
		reset($this->buttons);
		if ($value >= key($this->buttons)) {
			end($this->buttons);
			if ($value <= key($this->buttons)) {
				$this->value = $value;
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function display() {
		foreach ($this->buttons as $index => $button) {
			echo '<input name="', $this->name, '" id="', $this->name, '_', $index, '" type="radio" value="', $index, '" ';
			if ($index == $this->value) echo 'checked="checked" ';
			echo 'title="', $button, '" class="', $this->class, '" /> <label for="', $this->name, '_', $index, '">', $button, '</label>';
		}
	}

}

?>