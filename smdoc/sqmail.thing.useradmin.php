<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* print crc32('sqmuseradmin'); */

define('USERADMINTHINGID',2048373394);
$HARDTHING[USERADMINTHINGID]['func'] = 'sqmuseradmin';
$HARDTHING[USERADMINTHINGID]['title'] = 'Registered Users';
$HARDTHING[USERADMINTHINGID]['lastmodified'] = filemtime(__FILE__);

$FORMAT = array_merge($FORMAT, array(
    'user_list' => '<table border="0" cellspacing="3" cellpadding="0">' . "\n",
    '/user_list' => "</table>\n",
    'user_name' => '<tr><td>',
    '/user_name' => '</td><td>&nbsp;</td>',
    'user_admin' => '<td align="right" class="small">',
    '/user_admin' => "</td></tr>\n"
));

function sqmuseradmin() {
    global $HARDTHING, $HARDCLASS;
	global $conn, $wtf;
    track('sqmindex');

    $fields = array('DISTINCT objectid','title','workspaceid');
    $orderby = array('title');
    if ($wtf->user->workspaceid == 0) {
        $wherespace = 'workspaceid = 0';
    } else {
        $wherespace = '(workspaceid = 0 OR workspaceid = '.$wtf->user->workspaceid.')';
    }
    $where = array('classid = '.HOMECLASSID, 'AND', $wherespace);

    /* select:     connection, table,       joins, fields,  conditions, groups, orders,   limit) */
    $query = DBSelect(  $conn, OBJECTTABLE, NULL,  $fields, $where,     NULL,   $orderby, NULL);

    $recordNum = getAffectedRows();
    echo '<user_list>';
    if ($recordNum > 0) {
        for ($foo = 1; $foo <= $recordNum; $foo++) {
            $record = getRecord($query);
            echo '<user_name>';
            echo '<a href="',THINGIDURI.$record['objectid'], '&amp;class=', $HARDCLASS[HOMECLASSID],'">', $record['title'], '</a>';
            echo '</user_name>';
            echo '<user_admin>';
            if ( $wtf->user->inGroup(USERADMINGROUP) ) {
                echo ' ( <a href="',THINGIDURI.$record['objectid'], '&amp;class=', $HARDCLASS[USERCLASSID],'&amp;op=admin">admin</a>';
                if ( $wtf->user->inGroup(USERDELETEGROUP) ) {
                    echo ' | <a href="',THINGIDURI.$record['objectid'], '&amp;class=', $HARDCLASS[USERCLASSID],'&amp;op=delete">delete</a>';
                }
                echo ' )';
            }
            echo '</user_admin>';

            /* Append flag for workspace copy of whatever it is */
            if ( $record['objectid'] != $wtf->user->objectid && $record['workspaceid'] != 0 ) {
                echo " &lt;workspace copy&gt;";
            }
        }
    }
    echo '</user_list>';
    track();
}

?>
