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
input.cookie.php
Cookie input object
*/

class input_cookie {
	
	var $name; // cookie name
	var $value = NULL; // value of input
	var $regex = NULL; // regex value must match
	var $expire, $path, $domain, $secure;
	
	function input_cookie($name, $regex = NULL, $value = NULL, $expire = NULL, $path = NULL, $domain = NULL, $secure = NULL) {
		$this->name = $name;
		$this->regex = $regex;
		$this->expire = setVarConstOrDefault($expire, 'COOKIE_EXPIRE', 31536000);
		$this->path = setVarConstOrDefault($path, 'COOKIE_PATH', '');
		$this->domain = setVarConstOrDefault($domain, 'COOKIE_DOMAIN', '');
		$this->secure = setVarConstOrDefault($secure, 'COOKIE_SECURE', '');
		if (isset($_COOKIE[$name])) {
			$this->set($_COOKIE[$name]);
		}
		if (!isset($this->value)) {
			$this->value = $value;
		}
	}
	
	function set($value){
		if ($this->regex == NULL || preg_match($this->regex, $value)) {
			if (get_magic_quotes_gpc()) $value = stripslashes($value);
			$this->value = $value;
			return setcookie($this->name, $this->value, time() + $this->expire, $this->path, $this->domain, $this->secure);
		} else {
			return FALSE;
		}
	}

	function delete(){
		return setcookie($this->name, $this->value, time() - 3600, $this->path, $this->domain, $this->secure);
	}

}

?>