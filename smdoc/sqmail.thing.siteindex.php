<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* print crc32('sqmindex'); */

define('SQMINDEXCLASSID',-2548195);
$HARDTHING[SQMINDEXCLASSID]['func'] = 'sqmindex';
$HARDTHING[SQMINDEXCLASSID]['title'] = 'Site Index';

$FORMAT = array_merge($FORMAT, array(
	'indexhead' => '<h3>',
	'/indexhead' => '</h3>',
	'indexitem' => '',
	'/indexitem' => '<br />'
));

function sqmindex() {
    global $HARDTHING, $HARDCLASS;
	global $conn, $wtf;
    track('sqmindex');

    $fields = array('DISTINCT objectid','title','classid','workspaceid');

    /* first, set up arrays for queries that take workspaceid into account */
    if ($wtf->user->workspaceid == 0) {
        $wherespace = 'workspaceid = 0';
    } else {
        $wherespace = '(workspaceid = 0 OR workspaceid = '.$wtf->user->workspaceid.')';
    }

    /** 
     * Only list workspaces if not Anonymous user. 
     * Anonymous users can not visit/use Workspaces, so don't list them.
     */
    if ( $wtf->user->inGroup(WORKSPACECREATE) || $wtf->user->inGroup(WORKSPACEVIEW) ) {

        $lastClass = FALSE;
        printHeading('Available Workspaces');            

        if ( $wtf->user->inGroup(WORKSPACEVIEW) ) {
            /* select only workspaces */
            $where = array('classid = ' . WORKSPACECLASSID, 'AND',  $wherespace);
            $orderby = array('title');

            /* select:     connection, table,       joins, fields,  conditions, groups, orders,   limit) */
            $query = DBSelect(  $conn, OBJECTTABLE, NULL,  $fields, $where,     NULL,   $orderby, NULL);
            $recordNum = getAffectedRows();
            if ($recordNum > 0) {
                for ($foo = 1; $foo <= $recordNum; $foo++) {
                    $record = getRecord($query);
                    printLineBegin();
                    echo '<a href="', THINGIDURI.$record['objectid'], '">', $record['title'], '</a> (', $HARDCLASS[WORKSPACECLASSID], ")";
                    printLineEnd($record['workspaceid'], $record['objectid']);
                }
            } else {
                printLineBegin();
                echo 'No Workspaces have been created.';
                printLineEnd($record['workspaceid']);
            }     
        }

        if ( $wtf->user->inGroup(WORKSPACECREATE) ) {
            echo '<br />';
            printLineBegin();
            echo '<a href="', THINGURI, 'workspace">Create New Workspace</a>';
            printLineEnd(0);
        } 
    }

    /**
     * Print site content, leave out hardthings,  users, homes,  and workspaces
     */
    printHeading('Index of Site Content');

    /* general index won't include users (not viewable), homes, or workspaces */
    $where = array('classid != ' . USERCLASSID, 'AND',
                   'classid != ' . HOMECLASSID, 'AND',
                   'classid != ' . WORKSPACECLASSID, 'AND',
                   $wherespace);
    $fields = array('DISTINCT objectid','title','classid','workspaceid','sectionid');
    $orderby = array('sectionid', 'classid', 'title');

    /* select:     connection, table,       joins, fields,  conditions, groups, orders,   limit) */
    $query = DBSelect(  $conn, OBJECTTABLE, NULL,  $fields, $where,     NULL,   $orderby, NULL);

    $lastClass = FALSE;
    $recordNum = getAffectedRows();

    if ($recordNum > 0) {
        for ($foo = 1; $foo <= $recordNum; $foo++) {
            $record = getRecord($query);
            $classid = intval($record['classid']);

            printLineBegin();
            if (isset($HARDCLASS[$classid])) {
                echo "<a href=\"", THINGIDURI.$record['objectid'], "&amp;class=", $HARDCLASS[$classid], "\">", $record['title'], "</a> (", $HARDCLASS[$classid], ")";
            } else {
                echo "<a href=\"", THINGIDURI.$record['objectid'], "\">", $record['title'], "</a>";
            }
            printLineEnd($record['workspaceid']);
        }
    }

    if ( $wtf->user->inGroup(CREATORS) ) {
        echo '<br />';
        foreach($HARDTHING as $thingid => $hardThing) {
            if ( $hardThing['func'] != 'workspace' && 
                 $hardThing['func'] != 'search' &&
                 $hardThing['func'] != 'sqmuseradmin' &&
                 $hardThing['func'] != 'sqmindex' ) {
                
                printLineBegin();
	            echo '<a href="', THINGIDURI.$thingid, '">', $hardThing['title'], '</a>';
                printLineEnd(0);
            }
        }
    } 
 
    track();
}

function printHeading($heading) {
    echo '<indexhead>' . $heading . "</indexhead>\n";
}

function printLineBegin($lastClass = FALSE) {
    echo '<indexitem>';
}

function printLineEnd( $workspaceid, $objectid = 1 ) {
    global $wtf;

    /* Append flag for workspace copy if the copy exists in the current (not main) workspace */
    if ( $workspaceid != 0 ) {
        echo ' &lt;workspace copy&gt;';
    }

    /* If the passed in object id matches the workspace of the user, 
     * Indicate it is the current workspace.
     * The objectid parameter is initialized to 1 if not specified to make
     * sure this doesn't match the default workspaceid (0)
     */
    if ( $objectid == $wtf->user->workspaceid ) {
        echo ' &lt;current workspace&gt;';
    }
    echo "</indexitem>\n";
}

?>
