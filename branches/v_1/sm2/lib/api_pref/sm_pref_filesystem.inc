<?php

	class sm_api_pref {
		var $_username;
		
		function sm_api_pref($username, $password) {
			$this->_username = $username;
		}

		function set($name, $val) {
			$prf = $this->get('*');
			$prf[$name] = $val;
			
			$fp = fopen("$sm_pref_api_data_dir/$username.pref", 'w');
			fwrite ($fp, serialize($prf));
			fclose ($fp);
		}
		
		function get($string='*') {
			global $pref_api_filesystem_data_dir;
			global $default_pref;

			$filename = "$pref_api_filesystem_data_dir/".$this->_username.".pref";
			$fp = fopen($filename, 'a');
			$prf = unserialize(fgets($fp, 65536));
			fclose ($fp);

			if ($string == '*') {
				return $prf;
			} else {
				return $prf[$string];
			}
		}

		function unset_var($name) {
			$prf = $this->get('*');
			unset($prf[$name]);

			$fp = fopen("$sm_pref_api_data_dir/".$this->_username.".pref", 'r');
			fwrite ($fp, serialize($prf));
			fclose ($fp);
		}

		function isset_var($name) {
			$prf = $this->get('*');
			if (isset($prf[$name])) {
				return true;
			} else {
				return false;
			}
		}
	}

?>
