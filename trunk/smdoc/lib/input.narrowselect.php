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
input.narrowselect.php
Narrow select input object
*/

class input_narrowselect {
	
	var $name; // narrowselect name
	var $value; // value of narrowselect
	var $regex;
	var $caption; // caption to place next to dropdown
	var $height; // height of dropdown
	var $items; // items in dropdown
	var $multiple; // multi select
	var $class; // css class
	var $narrowString; // narrow select string
	var $required;
	var $number; // number of items to shortlist to
	
	function input_narrowselect($name, $value = NULL, $regex = NULL, $items = NULL, $caption = NULL, $number = 5, $height = 1, $multiple = FALSE, $class = NULL, $required = TRUE) {
		$this->name = $name;
		$this->items = $items;
		$this->regex = $regex;
		$this->number = $number;
		if ($multiple) {
			if (isset($_POST[$name]) && is_array($_POST[$name])) {
				$okay = TRUE;
				foreach ($_POST[$name] as $val) {
					if (!isset($this->items[$val])) {
						$okay = FALSE;
						break;
					}
				}
				if ($okay) {
					$this->value = $_POST[$name];
				}
			} elseif (isset($_GET[$name]) && is_array($_GET[$name])) {
				$okay = TRUE;
				foreach ($_GET[$name] as $val) {
					if (!isset($this->items[$val])) {
						$okay = FALSE;
						break;
					}
				}
				if ($okay) {
					$this->value = $_GET[$name];
				}
			}
		} else {
			if (isset($_POST[$name])) {
				$this->set($_POST[$name]);
			} elseif (isset($_GET[$name])) {
				$this->set($_GET[$name]);
			}
		}
		if (!isset($this->value)) {
			if (isset($_POST[$name])) { // we're got a narrow select string
				$this->narrowString = $_POST[$name];
			} else {
				$this->value = $value;
			}
		}
		$this->caption = $caption;
		$this->height = $height;
		$this->multiple = $multiple;
		$this->class = $class;
	}
	
	function set($value) {
		if (isset($this->items[$value])) {
			$this->value = $value;
			return TRUE;
		}
		return FALSE;
	}
	
	function gotValue() {
		if (isset($this->value)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function display() {
	
		if (!isset($this->narrowString)) {
		
			$textbox = new input_textbox($this->name, $this->regex, NULL, $this->caption, NULL, NULL, $this->class, $this->required);
			$textbox->display();
		
		} else {
		
			$groups = array();
			$narrowString = strtolower($this->narrowString);
			foreach ($this->items as $key => $item) {
				$simValue = similar_text($narrowString, strtolower($item));
				$groups[$simValue][$key] = $item;
			}
			krsort($groups);
			$items = array();
			$foo = 0;
			foreach($groups as $group) {
				foreach($group as $key => $item) {
					$items[$key] = $item;
					if ($foo == 0) $value = $key;
					$foo++;
					if ($foo >= $this->number) break 2;
				}
			}
		
			$dropdown = new input_dropdown($this->name, $value, $items, $this->caption, $this->height, $this->multiple, $this->class);
			$dropdown->display();

		}
	}

}
