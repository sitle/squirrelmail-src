<?php
/* etc/verify.php
**
** Jason Bradley Nance <aitrus@tresgeek.net>
**
** This script verifies that someone trying to access the config stuff 
**  is a valid user.
*/

require_once( 'admin.inc.php' );
header( 'Pragma: no-cache' );
session_start( );
session_unregister( 'admin_is_logged_in' );

/* Check (again to be safe) to see if they setup someone with rights */
if ( !isset( $AdminUser ) || empty( $AdminUser ) )
  die( "No admin user set.  Configuration impossible." );
if ( !isset( $AdminPass ) || empty( $AdminPass ) )
  die( "No admin password set.  Configuration impossible." );

/* Did they fill out the form?  Or are they just trying to browse here? */
if ( !isset( $auser ) || empty( $auser) ) {
  echo "<html>\n";
  echo "<body bgcolor=\"#ffffff\">\n";
  echo "<br><br>";
  echo "<center>";
  echo "<b>You must be logged in to access this page</b><br>";
  echo "<a href=\"index.php\">Go to the login page</a>\n";
  echo "</center>";
  echo "</body></html>\n";
  exit( );
}

/* Finally, does the username and password they supplied match the config? */
if ( ( $auser == $AdminUser ) && ( $apass == $AdminPass ) ) {
  session_register( "admin_is_logged_in" );
  header( "Location: do_config.php" );
}

?>
