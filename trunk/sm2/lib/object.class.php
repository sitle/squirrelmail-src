<?php

/**
 * object.class.inc
 *
 * Copyright (c) 2003 Marc Groot Koerkamp 
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * The base of all objects
 *
 * Author: Marc Groot Koerkamp (Sourceforce username: stekkel) 2003
 *
 * $Id$
 */


class object {
	var 	$name,
		$listen = array(),
		$listeners = array(); /* objects that should be notified */

	function object($name='') {
		$this->listeners = array(&$this->nodes);
		/* create root node */
		/* set the sleep notifyer */
		$this->listen['sleep'] = array(&$this, '_sleep');
	}

	function __sleep() {
		$this->notify('sleep','',true);
	}

	function _sleep() {
	}

	function notify($id,$msg, $recursive) {
		if (isset($this->listen[$id])) {
			if (is_callable($this->listen[$id],true,$function)) {
				call_user_func_array($this->listen[$id],&$this,$msg);
				// test
				$function($this,$msg);
			}
		}
		/* notify the rest */
		if ($recursive) {
			foreach ($this->listeners as $listener) {
				if (is_array($listener)) {
					$cnt = count($this->nodes);
					for ($i=0;$i<$cnt;++$i) {
						if (is_object($listener[$i])) {
							$listener[$i]->notify($id,$msg,$recursive);
						}
					}
				} else if (is_object($listener)) {
					$listener->notify($id,$msg,$recursive);
				}
			}
		}
	}
}

?>
