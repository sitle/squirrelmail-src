<?php
/*
** etc/admin.inc.php
**
** Jason Bradley Nance <aitrus@tresgeek.net>
**
** This is only here to keep it out of the main config.
** Setup an admin user and password here.
** If they are blank or not set, the script just won't let you in.
** It's either that or set a default, then some goober would leave 
**  the default in there and have their stuff messed with and come 
**  crying on the list.
**
** Make sure this file is only readable by the web user.
*/

$AdminUser = "BigCheese";
$AdminPass = "H4X0rMe";

/* This function is probable somewhere else, but we can clean this up later */
/* Besides, it's a "little" different */
function is_logged_in( ) {
  if ( !session_is_registered( 'admin_is_logged_in' ) ) {
    echo "<html><body bgcolor=\"#ffffff\">\n";
    echo "<br><br>";
    echo "<center>";
    echo "<b>You must be logged in to access this page.</b><br>";
    echo "<a href=\"index.php\">Go to the login page</a>\n";
    echo "</center>";
    echo "</body></html>\n";
    exit( );
  } else {
    return true;
  }
}

?>
