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
 * Class for creation/storage/manipulation of HTML formatted documents.
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage Foowd
 */

/** Class descriptor */
setClassMeta('foowd_text_html', 'HTML Text Document');

/**
 * HTML text object class.
 *
 * This class defines a HTML text area and methods to view and edit that area.
 *
 * @author Paul James
 * @package smdoc
 * @subpackage text
 */
class foowd_text_html extends foowd_text_plain {

	/**
	 * Evaluate the HTML for PHP processing instructions.
	 *
	 * @var bool
	 */
	var $evalCode = 0;

	/**
	 * Evaluate the HTML for include processing instructions.
	 *
	 * @var bool
	 */
	var $processInclude = 0;

	/**
	 * Serliaisation wakeup method.
	 *
	 * Re-create Foowd meta arrays not stored when object was serialized.
	 */
	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['evalCode'] = '/[01]{1}/';
		$this->foowd_vars_meta['processInclude'] = '/[01]{1}/';
	}

	/**
	 * Process processing instructions.
	 *
	 * @param string str The content string to process.
	 * @return string The processed content.
	 */
	function processPIs($str) {
		$this->foowd->track('foowd_text_html->processPIs');
		static $includeTrack = array();
		$parts = preg_split('/(<\?|\?>)/Us', $str);
		$newStr = '';
		foreach ($parts as $part) {
			if ($this->evalCode && substr($part, 0, 3) == 'php') { // eval PHP code block
				$foowd = &$this->foowd; // place Foowd reference in local scope
				ob_start();
				$newStr .= eval(substr($part, 3));
				$newStr .= ob_get_contents();
				ob_end_clean();
			} elseif ($this->processInclude && substr($part, 0, 7) == 'include') { // include another object
				$include = explode(' ', trim(substr($part, 7)));
				$includeObject = $include[0];
				$foo = 1;
				if (substr($includeObject, 0, 1) == '"') {	
					$includeObject = substr($includeObject, 1);
					while (!isset($index[$foo])) {
						$includeObject .= ' '.$include[$foo];
						if (substr($includeObject, -1, 1) == '"') {
							$includeObject = substr($includeObject, 0, -1);
							break;
						}
						$foo++;
					}
					$foo++;
				}
				if (isset($include[$foo])) {
					$includeMethod = $include[$foo];
				} else {
					$includeMethod = 'raw';
				}
				if (in_array($includeObject, $includeTrack) || $includeObject == $this->title) {
					$newStr .= '<p>Can not nest includes of the same object!</p>';
				} else {
					$includeTrack[] = $includeObject;
					$object = &$this->foowd->getObj(array('objectid' => crc32(strtolower($includeObject))));
					if ($object) {
						ob_start();
						$this->foowd->method($object, $includeMethod); // call object method
						$newStr .= ob_get_contents();
						ob_end_clean();
					}
					array_pop($includeTrack);
				}
			} else { // not a PI so just leave alone
				$newStr .= $part;
			}
		}
		$this->foowd->track(); return $newStr;
	}

	/**
	 * Process text content. Processes processing instructions.
	 *
	 * @param string content The text to process.
	 * @return string The processed content.
	 */
	function processContent($content) {
		if ($this->evalCode || $this->processInclude) {
			return $this->processPIs($content);
		} else {
			return $content;
		}
	}

	/**
	 * Output the object content in a raw format.
	 */
	function method_raw() {
		$this->foowd->track('foowd_text_html->method_raw');
		echo $this->view();
		$this->foowd->track();
	}
	
}

?>
