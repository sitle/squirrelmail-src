<?php
/*
 * Revised Header/Navigation Menu for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* display the wiki menu */
function sqmNavMenu(&$thing) {
	global $wtf;
	track('wikiNavMenu');
	
    echo '<pagetitle>';
    echo '<usermenu>';
// user Login/Logout/Register
	if ($wtf->user->objectid == ANONYMOUSUSERID) {
        echo 'Anonymous User ';
		echo '( <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=login">Login</a> ';
		echo '| <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=create">Register</a> )';
	} else {
        echo '<a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=edit">', $wtf->user->title, '</a> ';
		echo '( <a href="', THINGIDURI, $wtf->user->objectid, '&amp;class=user&amp;op=logout">Logout</a> )';
	}    
    if ($wtf->user->workspaceid != 0 ) {
       $workspace = &wtf::loadObject($wtf->user->workspaceid, 0, 'workspace');
       echo '<br/>Current Workspace: ';
       echo '<a href="', THINGIDURI, $workspace->objectid, '&amp;class=workspace">' . $workspace->title . '</a>';
    }
    echo '</usermenu>';        	
// Workspace/PageTitle
    echo '<a href="'.THINGURI.$thing->title.'&amp;class='.$wtf->class.'">'.$thing->title.'</a>';
    if ( $thing->workspaceid != 0 ) {
      if ( $thing->workspaceid != $wtf->user->workspaceid ) {
        $workspace = &wtf::loadObject($wtf->user->workspaceid, 0, 'workspace');
      }
      echo '<workspaceid>';
      echo ' ( ' . $workspace->title . ' ) ';
      echo '</workspaceid>';    
    }
    echo '</pagetitle>';

// Main page sections - Search/Index
    echo '<navmenu>';
	echo '<a href="', FILENAME, '">Home</a> ';
    echo '</navmenu>';
    echo '<searchmenu>';
    echo '<a href="', THINGURI, 'search">Search</a> | ';
    echo '<a href="', THINGURI. 'sqmuseradmin">Users</a> | ';
    echo '<a href="', THINGURI. 'sqmindex">Index</a> ';
    echo '</searchmenu>';
 	
	track();
}

/* display the wiki menu */
function sqmEditMenu(&$thing) {
	global $wtf;
	track('wikiMenu');
	
	echo '<editmenu>';
	
// page
    echo 'This Page: ';
	echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=view">View</a> ';
	echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=edit">Edit</a> ';
	echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=delete">Delete</a> ';
	echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=history">History</a> ';
	if ($wtf->user->inGroup(GODS)) {
		echo '| <a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=admin">Administrate</a> ';
	}
    $updatorDetails = $thing->getUpdator();
	if ($updatorDetails) {
		echo ' (last edited by <a href="'. THINGIDURI, $updatorDetails['homeid'], '&amp;class=home">', $updatorDetails['username'], '</a> on '.dbdate2string($updatorDetails['datetime'], SHORTDATEFORMAT), ') ';
	} else {
		$creatorDetails = $thing->getCreator();
		echo ' (created by <a href="', THINGIDURI.$creatorDetails['homeid'].'&amp;class=home">', $creatorDetails['username'], '</a> on ', dbdate2string($creatorDetails['datetime'], SHORTDATEFORMAT), ') ';
	}
    echo '<br/>';
// site
    echo 'Site: ';
	echo '<a href="', THINGURI, 'wikipage">Create a New Page</a> | ';
	echo '<a href="', THINGURI, 'recent changes">View Recent Changes</a> ';
	
	echo '</editmenu>';
	track();
}

?>
