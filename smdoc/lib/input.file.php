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
input.file.php
File upload object
*/

class input_file {
	
	var $name; // file upload  name
	var $file; // details of uploaded file
	var $caption; // caption to place next to file upload box
	var $size; // size of file upload box
	var $maxsize; // maxsize of file
	
	function input_file($name, $caption = NULL, $size = NULL, $maxsize = NULL) {
		$this->name = $name;
		if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
			$this->file = $_FILES[$name];
		}
		$this->caption = $caption;
		$this->maxsize = setVarConstOrDefault($maxsize, 'INPUT_FILE_SIZE_MAX', 30720);
		$this->size = setVarConstOrDefault($size, 'INPUT_TEXTBOX_SIZE_MAX', 30);
	}

	function display() {
		echo $this->caption, ' <input type="hidden" name="MAX_FILE_SIZE" value="', $this->maxsize, '" /><input name="', $this->name, '" type="file" size="', $this->size, '" /><br />';
	}

}

?>