<?php

require_once ('class.pref.php');

class class_pref_file extends class_pref {
	var $_filename;

	function class_pref_file($filename, $load = false) {
		$this->_filename = $filename;
		$this->_readonly = false;

		if ($load) $this->loadPreferences();

		return true;
	}

	function loadPreferences() {
                $filename = $this->_filename;
                $fp = @fopen ($filename, "r");
                if (! $fp) return false;
                $i = 0;
                while (! feof($fp)) {
                        $i++;
                        $line = fgets($fp, 128);
                        $line = trim($line);
                        if (strlen($line)) {
                                $words=explode("=",$line);
				$this->setPref($words[0],$words[1]);
                        }
                }
                fclose ($fp);
        }
        
        function savePreferences() {
		if ($this->_readonly) return false;

                $filename = $this->_filename;
                $fp = @fopen($filename, "w");
                reset($this->_list);
                while (list ($key, $val) = each ($this->_list)) {
              		fputs($fp, "$key=$val\n");
                }
                fclose($fp);
        }
}

?>
