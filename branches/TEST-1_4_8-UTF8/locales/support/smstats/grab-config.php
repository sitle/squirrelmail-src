<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: configuration file for GUI messages statistic program
//
////////////////////////////////////////////////////////////////////////////

// kdelibs is a fake package. Someone may don't like 
// this confusing name. Let's customize it.
$kdefake="squirrelmail";
// the msgfmt program path
$msgfmt="/usr/bin/msgfmt";
// base directory for KDE CVS
$basedir="/home/tomas/smstats-work/work";
// development options
$debug=0;


$prog="grab-guistats";
$adminemail="example@example.com";

// MySQL connection data
$sql_user   = "smstats";
$sql_pass   = "";
$sql_host   = "localhost";
$sql_db     = "smstats";

?>
