<?php

class class_pref {
	var $_list;
	var $_readonly;

	function class_pref() {
		$this->_list = null;
		$this->_readonly = true;
	}

	function setPref($name, $value) {
		$this->_list["$name"] = $value;
	}

	function getPref($name, $default = null) {
		$value = $default;
		if (isset($this->_list["$name"])) $value = $this->_list["$name"];
		return $value;
	}

	function unsetPref($name) {
		unset($this->_list["$name"]);
	}

	function dump() {
		print_r($this);
		echo '<BR><BR>';
	}
}

?>
