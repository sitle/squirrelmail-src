<?php

require_once('class.pref.php');

class class_pref_mysql extends class_pref {
	var $_dbh;
	var $error;
	var $mysql_host;
	var $mysql_user;
	var $mysql_password;
	var $mysql_database;
	var $username;

	function class_pref_mysql($load = false) {
		$this->host= "";
		$this->user = "";
		$this->password = "";
		$this->database = "";
		$this->username = "";

		if ($load) $this->loadPreferences();

		return true;
	}

	function loadPreferences() {
		if ($this->host == "") return false;

 		$dbh = mysql_connect($this->host, $this->user, $this->password);
		mysql_select_db($this->database);

		$result = mysql_query("SELECT prefkey, prefval FROM userprefs WHERE user = '$this->username'");

    		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			$this->setPref($row[0],$row[1]);
    		}

    		mysql_free_result($result);

		mysql_close($dbh);

		return true;
	}

	function savePreferences() {
		if ($this->host == "") return false;

		$dbh = mysql_connect($this->host, $this->user, $this->password);
                mysql_select_db($this->database);

                $result = mysql_query("DELETE FROM userprefs");

		$sql = "INSERT INTO userprefs (user, prefkey, prefval) VALUES ";
                $s = "";
                while (list ($key, $val) = each ($this->_list)) {
                        $sql .= "$s('$this->username','$key','$val')";
                        $s = ",";
                }
		$result = mysql_query($sql);

                mysql_close($dbh);

                return true;
	}
}

?>
