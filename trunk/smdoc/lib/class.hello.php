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
class.hello.php
Hello World example class
*/

/** CLASS DESCRIPTOR **/
if (!defined('META_-1537808423_CLASSNAME')) define('META_-1537808423_CLASSNAME', 'foowd_hello');
if (!defined('META_-1537808423_DESCRIPTION')) define('META_-1537808423_DESCRIPTION', 'Hello World');

/** CLASS DECLARATION **/
class foowd_hello extends foowd_object {

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_hello->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Hello World!</h1>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>