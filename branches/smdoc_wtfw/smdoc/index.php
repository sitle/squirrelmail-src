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
  if ( defined('SITE_MIRROR') ) {
    sqmMirrorNavMenu($wtf->thing);
  } else {
    sqmNavMenu($wtf->thing);
  }
}

echo '<content>';
$wtf->doOp();
echo '</content>';

if ( $wtf->user->skin == 'sqmail' ) {
  if ( defined('SITE_MIRROR') ) {
    sqmMirrorEditMenu($wtf->thing);
  } else {
    sqmEditMenu($wtf->thing);
  }
} else {
  wikiMenu($wtf->thing);
}

$wtf->display();

databaseClose($conn);
?>
