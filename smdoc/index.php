<?php

/*
 * Modified by SquirrelMail Development Team
 * $Id$
 */

/* Define PATH, DB vars in config.php */
include('site-config.php');

include(PATH.'wtf.config.php');

$conn = databaseOpen(DBHOST, DBUSER, DBPASS, DBNAME); // open database

$wtf = new wtf();

$wtf->loadThing();

if ( $wtf->user->skin == 'sqmail' ) {
  sqmNavMenu($wtf->thing);
}

echo '<content>';
$wtf->doOp();
echo '</content>';

if ( $wtf->user->skin == 'sqmail' ) {
  sqmEditMenu($wtf->thing);
} else {
  wikiMenu($wtf->thing);
}

$wtf->display();

databaseClose($conn);
?>
