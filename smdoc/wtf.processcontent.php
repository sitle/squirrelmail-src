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
wtf.processcontent.php
Content Output Processing
*/

/*** Parse content for PI's ***/

function processContent($content) {
	global $PI, $wtf;

	while (preg_match('/^(.*)<\?([a-zA-Z0-9]+)\b(.*)\?>(.*)$/sU', $content, $matchArray) > 0) {
		$content = $matchArray[1];
		$target = $matchArray[2];
		$data = $matchArray[3];
		if (isset($PI[$target])) {
			$content .= $PI[$target]($data);
		} else {
			$content .= '<error>Unknown processing instruction "'.$target.'".</error>';
		}
		$content .= $matchArray[4];
	}

	return $content;
}

?>