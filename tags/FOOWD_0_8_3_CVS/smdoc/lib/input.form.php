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
input.form.php
Form input object
*/

class input_form {
	
	var $name; // form name
	var $location; // location for form to submit to
	var $method; // submit method to use
	var $submit; // value of submit button
	var $reset; // value of reset button
	var $preview; // value of preview button
	var $objects = array(); // array of form objects
	var $class; // css class
	
	function input_form($name, $location = NULL, $method = 'POST', $submit = 'Submit', $reset = 'Reset', $preview = NULL, $class = NULL) {
		$this->name = $name;
		if ($location == NULL) {
			$location = getURI($_GET);
		}
		$this->location = $location;
		$this->method = $method;
		$this->submit = $submit;
		$this->reset = $reset;
		$this->preview = $preview;
		$this->class = $class;
	}
	
	function addObject(&$object){
		if ((is_object($object) && method_exists($object, 'display') && substr(get_class($object), 0, 6) == 'input_') || is_string($object)) {
			$this->objects[] = $object;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function submitted() {
		if (($this->method == 'POST' && isset($_POST[$this->name.'_submit'])) || ($this->method == 'GET' && isset($_GET[$this->name.'_submit']))) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function previewed() {
		if (($this->method == 'POST' && isset($_POST[$this->name.'_preview'])) || ($this->method == 'GET' && isset($_GET[$this->name.'_preview']))) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function display() {
		echo '<form name="', $this->name, '" action="', $this->location, '" method="', $this->method, '" enctype="multipart/form-data" class="', $this->class, '">';
		foreach ($this->objects as $object) {
			if (is_object($object)) {
				$object->display();
				echo '<br />';
			} elseif (is_string($object)) {
				echo $object, '<br />';
			}
		}
		if ($this->submit || $this->reset || $this->preview) {
			echo '<p>';
			if ($this->submit) echo '<input type="submit" name="', $this->name, '_submit" value="', $this->submit, '" class="', $this->class, '_submit" /> ';
			if ($this->reset) echo '<input type="reset" name="reset" value="', $this->reset, '" class="', $this->class, '_reset" /> ';
			if ($this->preview) echo '<input type="submit" name="', $this->name, '_preview" value="', $this->preview, '" class="', $this->class, '_preview" /> ';
			echo '</p>';
		}
		echo '</form>';
	}

	function display_start() {
		echo '<form name="', $this->name, '" action="', $this->location, '" method="', $this->method, '" enctype="multipart/form-data" class="', $this->class, '">';
	}

	function display_end() {
		if ($this->submit || $this->reset || $this->preview) {
			echo '<p>';
			if ($this->submit) echo '<input type="submit" name="', $this->name, '_submit" value="', $this->submit, '" class="', $this->class, '_submit" /> ';
			if ($this->reset) echo '<input type="reset" name="reset" value="', $this->reset, '" class="', $this->class, '_reset" /> ';
			if ($this->preview) echo '<input type="submit" name="', $this->name, '_preview" value="', $this->preview, '" class="', $this->class, '_preview" /> ';
			echo '</p>';
		}
		echo '</form>';
	}

}

?>