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
wtf.thing.search.php
Search
*/

$HARDTHING[-1259283545]['func'] = 'search';
$HARDTHING[-1259283545]['title'] = 'Search';

// formatting
$FORMAT = array_merge($FORMAT, array(
	'search_form' => '<form method="post" action="'.THINGURI.'search"><p>Use this textbox to search for titles of pages: <input type="text" name="{searchboxfield}" maxlength="{maxlength}" value="',
	'/search_form' => '" /> <input type="submit" name="search" value="Search" /></p></form>',
	'search_results' => '<ul>',
	'/search_results' => '</ul>',
	'search_result' => '<li><a href="'.THINGIDURI.'{thingid}">',
	'/search_result' => '</a></li>'
));

function search() {
	global $HARDTHING;
	global $conn, $wtf;
	track('search');

	$output = '';
	
	$searchString = getValue('searchbox', FALSE);
		
	if ($searchString && strlen($searchString) <= MAXTITLELENGTH) {
		$found = FALSE;
// hard things
		foreach($HARDTHING as $thingid => $hardThing) {
			if (strpos(strtolower($hardThing['title']), strtolower($searchString)) !== FALSE) {
				if (!$found) $output .= '<search_results>';
				$output .= '<search_result thingid="'.$thingid.'">'.$hardThing['title'].'</search_result>';
				$found = TRUE;
			}
		}
// soft things
		if ($query = DBSelect($conn, OBJECTTABLE, NULL, array('DISTINCT objectid', 'title', 'classid'), array('title LIKE "%'.$searchString.'%"'), NULL, array('title'), NULL)) {
			$numberOfRecords = getAffectedRows($conn);
			if ($numberOfRecords > 0) {
				for ($foo = 1; $foo <= $numberOfRecords; $foo++) {
					if (!$found) $output .= '<search_results>';
					$record = getRecord($query);
					$output .= '<search_result thingid="'.$record['objectid'].'">'.$record['title'].'</search_result>';
					$found = TRUE;
				}
			}
		}
		if ($found) {
			$output .= '</search_results>';
			$output .= '<search_form searchboxfield="search_searchbox" maxlength="'.MAXTITLELENGTH.'">'.htmlentities($searchString).'</search_form>';
		} else { // nothing found
			$output .= 'Nothing found.';
			$output .= '<search_form searchboxfield="search_searchbox" maxlength="'.MAXTITLELENGTH.'">'.htmlentities($searchString).'</search_form>';
		}
	} else {
		$output .= '<search_form searchboxfield="search_searchbox" maxlength="'.MAXTITLELENGTH.'"></search_form>';
	}

	track();

	echo $output;
}

?>