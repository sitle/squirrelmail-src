<?php
/*
	This file is part of the Wiki Type Framework (WTF).
	Copyright 2002, Paul James
	See README and COPYING for more information, or see http://wtf.peej.co.uk

	WTF is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	WTF is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WTF; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
wtf.pi.include.php
Include PI Module
*/

$PI['include'] = 'IncludePI';
$PIGROUP['include'] = GODS;

function IncludePI($includeString) {
	global $wtf;
	track('IncludePI');
	static $includeRecurseStop = array();

	if (preg_match('/^(.+)(\(.*\))$/s', $includeString, $thingArray)) {
		$thing = trim($thingArray[1]);
		$thingSplit = explode(':', $thing);
		if (isset($thingSplit[1])) {
			$thing = $thingSplit[1];
			$className = $thingSplit[0];
		} else {
			$className = 'content';
		}
		$paraString = trim($thingArray[2]);

		if (is_numeric($thing)) {
			$thingid = $thing;
		} else {
			$thingid = crc32(strtolower($thing));
		}
		
		if (in_array($className.':'.$thingid, $includeRecurseStop)) {
			$output = '<error>Can not include thing recursively.</error>';
		} else {

			$includeRecurseStop[] = $className.':'.$thingid;
			$obj = &wtf::loadObject($thingid, 0, $className);

			if (is_object($obj)) {

				if ($paraString != '') {
					$paraArray = explode(',', substr($paraString, 1, -1));
					foreach ($paraArray as $para) {
						$attrArray = explode('=', $para);
						if (isset($attrArray[0]) && isset($attrArray[1])) {
							$_GET[$attrArray[0]] = $attrArray[1];
						}
					}
				}

				ob_start();
				echo $obj->getContent();
				$output = ob_get_contents();
				ob_end_clean();

			} else {
				$output = '<error>Could not include thing #'.$thingid.'.</error>';
			}
		}
	} else {
		$output = '<error>Invalid syntax in include, should be &quot;className:thingName(para1=val1, para2=val2, etc.)&quot;.</error>';
	}
	track(); return $output;
}

?>