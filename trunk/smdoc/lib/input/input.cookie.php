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

/**
 * Manage Cookie Input
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage input
 */

/** Define constants for managing cookie behavior */
if (!defined('COOKIE_EXPIRE')) define('COOKIE_EXPIRE', 31536000);
if (!defined('COOKIE_PATH')) define('COOKIE_PATH', '');
if (!defined('COOKIE_DOMAIN')) define('COOKIE_DOMAIN', '');
if (!defined('COOKIE_SECURE')) define('COOKIE_SECURE', '');

/**
 * Input cookie class.
 *
 * This class defines an input cookie, it handles input validation, and setting
 * and retrieving the cookies value.
 *
 * @package smdoc
 * @subpackage input
 * @author Paul James
 */
class input_cookie 
{
	/**
	 * The name of the cookie object.
	 *
	 * @var string
	 */
	var $name;
	
	/**
	 * The value of the cookie.
	 *
	 * @var string
	 */
	var $value = NULL;

	/**
	 * The regular expression used to validate the cookies value.
	 *
	 * @var string
	 */
	var $regex = NULL;

	/**
	 * How long til the cookie expires in seconds.
	 *
	 * @var int
	 */
	var $expire;

	/**
	 * The path the cookie is valid for.
	 *
	 * @var string
	 */
	var $path;

	/**
	 * The domain the cookie is valid for.
	 *
	 * @var string
	 */
	var $domain;

	/**
	 * Whether the cookie should only be sent over secure HTTP.
	 *
	 * @var bool
	 */
	var $secure;

	/**
	 * Constructs a new cookie object.
	 *
	 * @param string name The name of the querystring object.
	 * @param string regex The validation regular expression.
	 * @param string value The initial contents value.
	 * @param int expire How long til the cookie expires in seconds.
	 * @param string path The path the cookie is valid for.
	 * @param string domain The domain the cookie is valid for.
	 * @param bool secure Whether the cookie should only be sent over secure HTTP.
	 */
	function input_cookie($name, $regex = NULL, $value = NULL, $expire = COOKIE_EXPIRE, $path = COOKIE_PATH, $domain = COOKIE_DOMAIN, $secure = COOKIE_SECURE) {
		$this->name = $name;
		$this->regex = $regex;
		$this->expire = $expire;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		if (isset($_COOKIE[$name]) && ($this->regex == NULL || preg_match($this->regex, $_COOKIE[$name]))) {
			if (get_magic_quotes_gpc()) {
				$this->value = stripslashes($_COOKIE[$name]);
			} else {
				$this->value = $_COOKIE[$name];
			}
		}
		if (!isset($this->value)) {
			$this->value = $value;
		}
	}

	/**
	 * Sets the value of the cookie object.
	 *
	 * @param string value The value to set the cookie to.
	 * @return bool TRUE on success.
	 */
	function set($value){
		if ($this->regex == NULL || preg_match($this->regex, $value)) {
			if (get_magic_quotes_gpc()) $value = stripslashes($value);
			$this->value = $value;
			if ($this->expire == 0) {
				$expire = 0;
			} else {
				$expire = time() + $this->expire;
			}
			return setcookie($this->name, $this->value, $expire, $this->path, $this->domain, $this->secure);
		} else {
			return FALSE;
		}
	}

	/**
	 * Delete the cookie from the client.
	 *
	 * @return bool TRUE on success.
	 */
	function delete(){
		return setcookie($this->name, $this->value, time() - 3600, $this->path, $this->domain, $this->secure);
	}

}

?>
