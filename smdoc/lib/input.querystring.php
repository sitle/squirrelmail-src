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
input.querystring.php
Querystring input object
*/

class input_querystring {
	
	var $name; // querystring name
	var $value; // value of input
	var $regex; // regex value must match
	
	function input_querystring($name, $regex = NULL, $value = NULL) {
		$this->name = $name;
		$this->regex = $regex;
		if (isset($_GET[$name])) {
			$this->set($_GET[$name]);
		}
		if (!isset($this->value)) {
			$this->value = $value;
		}
	}
	
	function set($value){
		if ($this->regex == NULL || preg_match($this->regex, $value)) {
			if (get_magic_quotes_gpc()) $value = stripslashes($value);
			$this->value = $value;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function urlattach($url = '') {
		if ($url == '') {
			return '?'.urlencode($this->name).'='.urlencode($this->value);
		} else {
			return $url.'&'.urlencode($this->name).'='.urlencode($this->value);
		}
	}

}

?>