<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: configuration file for GUI messages statistic program
//
////////////////////////////////////////////////////////////////////////////

// kdelibs is a fake package. Someone may don't like 
// this confusing name. Let's customize it.
$kdefake="kdelibs";
// the msgfmt program path
$msgfmt="/usr/bin/msgfmt";
// base directory for KDE CVS
$basedir="/home/www";
// development options
$debug=100;


$prog="grab-guistats";
$adminemail="admin@i18n.kde.org";

// MySQL connection data
$sql_user   = "gui_editor";
$sql_pass   = "";
$sql_host   = "127.0.0.1";
$sql_db     = "guistats";

?>
