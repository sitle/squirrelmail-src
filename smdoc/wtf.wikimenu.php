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
wtf.wikimenu.php
Wiki Manu
*/

/* display the wiki menu */
function wikiMenu(&$thing) {
	global $wtf;
	track('wikiMenu');
	
	echo '<hr/><menu>';
	
// page
	echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=view">View</a> this page ';
	echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=edit">Edit</a> this page';
	$updatorDetails = $thing->getUpdator();
	if ($updatorDetails) {
		echo ' (last edited by <a href="'. THINGIDURI, $updatorDetails['homeid'], '&amp;class=home">', $updatorDetails['username'], '</a> on '.dbdate2string($updatorDetails['datetime'], SHORTDATEFORMAT), ') ';
	} else {
		$creatorDetails = $thing->getCreator();
		echo ' (created by <a href="', THINGIDURI.$creatorDetails['homeid'].'&amp;class=home">', $creatorDetails['username'], '</a> on ', dbdate2string($creatorDetails['datetime'], SHORTDATEFORMAT), ') ';
	}
	echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=delete">Delete</a> this page ';
	echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=history">History</a> of this page ';
	if ($wtf->user->inGroup(GODS)) {
		echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=admin">Administrate</a> this page ';
	}
	echo '<br/>';

// site
	echo '<a href="', FILENAME, '">Home page</a> | ';
	echo '<a href="', THINGURI, 'search">Find pages</a> by searching | ';
	echo '<a href="', THINGURI, 'wikipage">Create</a> a new page | ';
	echo '<a href="', THINGURI, 'recent changes">Recent changes</a> to pages | ';
	echo '<a href="', THINGURI. 'thing list">List</a> of pages ';
	
// user
	if ($wtf->user->objectid == ANONYMOUSUSERID) {
		echo '| <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=login">Login</a> to WTF ';
		echo '| <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=create">Register</a> an account ';
	} else {
		echo '| <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=logout">Logout</a> of WTF ';
		echo '| <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=edit">Edit</a> your user profile';
	}
	echo '<br/>';
	
	echo '</menu><hr/>';
	track();
}

?>