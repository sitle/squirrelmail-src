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
	var $file = NULL; // details of uploaded file
	var $caption; // caption to place next to file upload box
	var $size; // size of file upload box
	var $maxsize; // maxsize of file
	
	function input_file($name, $caption = NULL, $size = NULL, $maxsize = NULL) {
		$this->name = $name;
		$this->maxsize = getVarConstOrDefault($maxsize, 'INPUT_FILE_SIZE_MAX', 30720);
		if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0 && $_FILES[$name]['size'] <= $this->maxsize) {
			$this->file = $_FILES[$name];
		}
		$this->caption = $caption;
		$this->size = getVarConstOrDefault($size, 'INPUT_TEXTBOX_SIZE_MAX', 30);
	}
	
	function isUploaded() {
		if (is_array($this->file) && is_uploaded_file($this->file['tmp_name'])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function saveFile($dir, $filename = FALSE) {
		if (!$filename) {
			$filename = $this->file['name'];
		}
		if ($dir && substr($dir, -1) != '/') {
			$dir .= '/';
		}
		if (is_array($this->file) && move_uploaded_file($this->file['tmp_name'], $dir.$filename)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function getError() {
		if (isset($this->file)) {
			switch ($this->file['error']) {
			case 0:
				return 'The file uploaded with success.';
			case 1:
				return 'The uploaded file exceeds the maximum allowed size ('.ini_get('upload_max_filesize').').';
			case 2:
				return 'The uploaded file exceeds the maximum allowed file size ('.$this->maxsize.' bytes)';
			case 3:
				return 'The uploaded file was only partially uploaded due to your connection to the server being broken.';
			case 4:
				return 'No file was sent for uploading.';
			default:
				return 'An unknown error occured.';
			}
		} else {
			return 'No file was sent for uploading.';
		}
	}

	function display() {
		echo $this->caption, ' <input type="hidden" name="MAX_FILE_SIZE" value="', $this->maxsize, '" /><input name="', $this->name, '" type="file" size="', $this->size, '" />';
	}

}

?>