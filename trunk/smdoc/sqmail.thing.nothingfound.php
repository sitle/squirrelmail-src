<?php
/*
 * Modified Nothing Found page for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* print crc32('nothingfound'); */

/* NOTHINGFOUNDID defined in wtf.config.php */
$HARDTHING[NOTHINGFOUNDID]['func'] = 'nothingfound';
$HARDTHING[NOTHINGFOUNDID]['title'] = 'Nothing Found';
$HARDTHING[NOTHINGFOUNDID]['lastmodified'] = '$Date$';

function nothingfound() {
    global $wtf;
    if (isset($wtf->thingtitle)) {
        echo '<p>Sorry, nothing was found for the page "', $wtf->thingtitle, '".</p>';
        if ( $wtf->user->inGroup(CREATORS) ) {
            echo '<p>To create a new page called "', $wtf->thingtitle, '", <a href="', THINGURI, 'wikipage&amp;title=', $wtf->thingtitle, '">click here</a>.</p>';
        }
    } else {
        echo '<p>Sorry, nothing was found for the page #', $wtf->thingid, '.</p>';
    }
}
?>
