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
// Current workspace for User
    if ($wtf->user->workspaceid != 0 ) {
       $workspace = &wtf::loadObject($wtf->user->workspaceid, 0, 'workspace');
       echo '<br/>Current Workspace: ';
       echo '<a href="', THINGIDURI, $workspace->objectid, '&amp;class=workspace">' . $workspace->title . '</a>';
    }
    echo '</usermenu>';      
  	
// PageTitle/Workspace of page (not shown if Main)
    if ( $thing->classid != HARDTHINGCLASSID ) {
        echo '<a href="'.THINGURI.$thing->title.'&amp;class='.$wtf->class.'">'.$thing->title.'</a>';
    } else {
        // Hardthings require slightly different links
        echo '<a href="'.THINGURI.$wtf->thingtitle.'">'.$thing->title.'</a>';
    }
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
    global $HARDTHING;
	global $wtf;
	track('wikiMenu');
	
	echo '<editmenu>';
    if ( $thing->classid != HARDTHINGCLASSID ) {
        // None of these functions (including History) are available for Hard Things
        if ( $wtf->user->inGroup($thing->viewGroup) ) {
	        echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=view">View</a> | ';
        }
        if ( $wtf->user->inGroup($thing->editGroup) ) {
	        echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=edit">Edit</a> | ';
        }
        if ( $wtf->user->inGroup($thing->deleteGroup) ) {
  	       echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=delete">Delete</a> | ';
        }
        if ($wtf->user->inGroup($thing->adminGroup)) {
            echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=admin">Administrate</a> | ';
        }
	    echo '<a href="', THINGIDURI, $wtf->thingid, '&amp;class=', $wtf->class, '&amp;version=', $wtf->thing->version, '&amp;op=history">History</a> | ';
        echo '<a href="', THINGURI, 'recentchanges">Recent Changes</a>';
        echo '<br/>';

        // page updator information
        echo 'This Page ';
        $updatorDetails = $thing->getUpdator();
	    if ($updatorDetails) {
		    echo ' last edited by <a href="'. THINGIDURI, $updatorDetails['homeid'], '&amp;class=home">', $updatorDetails['username'], '</a> on '.dbdate2string($updatorDetails['datetime'], SHORTDATEFORMAT);
	    } else {
		    $creatorDetails = $thing->getCreator();
		    echo ' created by <a href="', THINGIDURI.$creatorDetails['homeid'].'&amp;class=home">', $creatorDetails['username'], '</a> on ', dbdate2string($creatorDetails['datetime'], SHORTDATEFORMAT);
        }
    } else {
        // Here is the much stripped down footer for Hard Things
	    echo '<a href="', THINGURI, 'recentchanges">Recent Changes</a>';
        echo '<br/>';
        if ( isset($HARDTHING[$thing->objectid]['lastmodified']) ) {
            preg_match("/Date: (\d+)\/(\d+)\/(\d+) (\d+):(\d+):(\d+) /", $HARDTHING[$thing->objectid]['lastmodified'], $matches);
            $last_modified = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
        } else {
            $last_modified = filemtime(__FILE__);
        }
        echo 'This Page last updated on ' . date( "F d Y H:i.", $last_modified);
	}

// close edit menu
	echo '</editmenu>';
	track();
}

?>
