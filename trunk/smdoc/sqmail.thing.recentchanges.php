<?php
/*
 * Modified Nothing Found page for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* print crc32('recentchanges'); */

define('RECENTCHANGESID',-1709045433);
$HARDTHING[RECENTCHANGESID]['func'] = 'recentchanges';
$HARDTHING[RECENTCHANGESID]['title'] = 'Recent Changes';
$HARDTHING[RECENTCHANGESID]['lastmodified'] = '$Date$';

function recentchanges() {
    global $HARDTHING, $HARDCLASS;
    global $wtf, $conn;
$days = 7;
$query = DBSelect($conn, 'tblObject', NULL, array(
    'objectid',
    'title',
    'classid',
    'updatorDatetime'
), array(
    'updatorDatetime > "'.date(DATABASEDATE, time() - ($days * 86400)).'"'
), NULL, array('updatorDatetime DESC'), NULL);
if ($query) {
    $numberOfThings = returnedRows($query);
    $date = FALSE;
    for ($foo = 1; $foo <= $numberOfThings; $foo++) {
        $record = getRecord($query);
        $thisDate = date('j F, Y', dbdate2unixtime($record['updatorDatetime']));
        if ($date != $thisDate) {
            if ($date) {
                echo '</ul>';
            }
            $date = $thisDate;
            echo '<subtitle>', $date, '</subtitle>';
            echo '<ul>';
        }
        $classid = intval($record['classid']);
        if (isset($HARDCLASS[$classid])) {
            echo '<li><a href="', THINGIDURI, $record['objectid'], '&amp;class=', $HARDCLASS[$classid], '">', $record['title'], '</a> ', date('g:i a', dbdate2unixtime($record['updatorDatetime'])), '</li>';
        } else {
            echo '<li><a href="', THINGIDURI.$record['objectid'], '">', $record['title'], '</a> ', date('g:i a', dbdate2unixtime($record['updatorDatetime'])), '</li>';
        }
        if ($foo == $numberOfThings) {
            echo '</ul>';
        }
    }
}


}
?>
